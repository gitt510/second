<?php
class MyDB {
    private const HOST = 'localhost';
    private const DBNAME = 'topix_small_1';
    private const USERNAME = 'user1';
    private const PASSWORD = 'password1';
    private ?PDO $connection = null;
    
    function getPdo(): PDO {
        if ($this->connection === null) {
            $this->connection = new PDO(
                'mysql:dbname=' . self::DBNAME . ';host=' . self::HOST . ';charset=utf8',
                self::USERNAME,
                self::PASSWORD
            );
        }
        return $this->connection;
    }

    function getCompanies() {
        $pdo = $this->getPdo();
        $query = "SELECT code, name FROM companies";
        $stmt = $pdo->query($query);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $items;
    }

    function getStockPrices($params) {
        try {
            $pdo = $this->getPdo();
            $code = $params['code'];
            $expr = $params['expr'] ?? 1095;
            // $query = "SELECT date, open, high, low, close, vol FROM stock_prices "
                     // ."WHERE code = $code "
            $query = "SELECT date, open, high, low, close, vol FROM stock_prices_$code "
                    ."WHERE date BETWEEN DATE_SUB(now(), INTERVAL $expr DAY) AND now()";
            $stmt = $pdo->query($query);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $items;
        } catch (PDOException $e) {
            // print_r($e->getMessage());
            return null;
        }
        
    }

    function getMovingAverages($params) {
        try {
            $pdo = $this->getPdo();
            $code = $params['code'];
            $expr = $params['expr'] ?? 1095;
            // $query = "SELECT date, sma_5, sma_25, sma_75 FROM simple_moving_averages "
            //         ."WHERE code = $code "
            $query = "SELECT * FROM moving_averages_$code "
                    ."WHERE date BETWEEN DATE_SUB(now(), INTERVAL $expr DAY) AND now()";
            $stmt = $pdo->query($query);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $items;
        } catch (PDOException $e) {
            // print_r($e->getMessage());
            return null;
        }
    }

    function getOscillators($params) {
        try {
            $pdo = $this->getPdo();
            $code = $params['code'];
            $expr = $params['expr'] ?? 1095;
            $query = "SELECT * FROM oscillators_$code "
                    ."WHERE date BETWEEN DATE_SUB(now(), INTERVAL $expr DAY) AND now()";
            $stmt = $pdo->query($query);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $items;
        } catch (PDOException $e) {
            // print_r($e->getMessage());
            return null;
        }
    }

    function getDiviedends($params) {
        $pdo = $this->getPdo();
        $code = $params['code'];
        $query = "SELECT record_date FROM diviedends WHERE code = $code";
        $stmt = $pdo->query($query);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $items;
    }

    function calcMovingAverage($array, $period) {
        // define variable
        $movingAverages = [];
        $count = count($array);

        // calc moving average
        foreach ($array as $idx => $data) {
            # insert null value
            // if ($idx < $period - 1 || $idx > $count - $period)
            if ($idx < $period -1) {
                $movingAverages[] = [
                    'date' => $data['date'],
                    'price'=> null
                ];
            } else {
                $sum = 0;
                for ($j = 0; $j < $period; $j++) {
                    $sum += $array[$idx - $j]['close'];
                }
                $movingAverages[] = [
                    'date' => $data['date'],
                    'price' => $sum / $period
                ];
            }
        }
    return $movingAverages;
    }


    function closeConnection(): void {
        $this->connection = null;
    }
}

class MySimulator {
    function findGoldenCross($shortTermData, $longTermData) {
        // define variavle for loop
        $isRising = false;
        $isRecordable = false;
        $STPriceBUF = null;
        $dataCount = count($shortTermData);

        // main loop
        $goldenCross = [];
        for ($idx = 0; $idx < $dataCount; $idx++) {
            // get values from array
            $date = $shortTermData[$idx]['date'];
            $STPrice = $shortTermData[$idx]['price'];
            $LTPrice = $longTermData[$idx]['price'];

            // if there are null value, record null value
            if (is_null($STPrice)) {
                continue;
            }

            // check if the STPrice is rising or not
            // if (is_null($STPriceBUF) && !is_null($STPrice))
            if (is_null($STPriceBUF)) {
                $STPriceBUF = $STPrice;
            } else {
                $isRising = $STPrice >= $STPriceBUF;
                $STPriceBUF = $STPrice;
            }

            // manage recordable statement
            if (!$isRecordable && $STPrice < $LTPrice) {
                if ($STPrice < $LTPrice) {
                    $isRecordable = true;
                }
            }

            // if the STPrice is above the LTPrice, record it as golden cross
            if ($isRising && $isRecordable) {
                if ($STPrice >= $LTPrice) {
                    $goldenCross[] = [
                        'date' => $date,
                        'price' => $LTPrice
                    ];
                    $isRecordable = false;
                    continue;
                }
            }

        }
        return $goldenCross;
    }

    function tryCrossReturn($dailyData, $goldenCross, $ratio) {
        // // find the date of golden cross
        $dates = array_column($dailyData, 'date');
        $opens = array_column($dailyData, 'open');
        $highs = array_column($dailyData, 'high');
        $lows = array_column($dailyData, 'low');

        // let's start simulation
        $results = [];
        foreach ($goldenCross as $goldenData) {
            // parse date
            $eventDate = $goldenData['date'];
            $eventPrice = $goldenData['price'];

            // you buy stock at open price just after golden cross
            $startIdx = array_search($eventDate, $dates);
            if ($startIdx < count($dailyData)) {
                $buyDate = $dates[$startIdx];
                $buyPrice = $opens[$startIdx];
            }

            // set limit and stop price
            $limitPrice = $buyPrice * (1 + $ratio);
            $stopPrice = $buyPrice * (1 - $ratio);

            // 
            $idx = $startIdx;
            while ($idx < count($dailyData)) {
                # get dailyData
                $date  = $dates[$idx];
                $high  = $highs[$idx];
                $low   = $lows[$idx];

                # 
                if ($high >= $limitPrice) {
                    $result = 'win';
                    $saveFlag = true;
                }
                elseif ($low <= $stopPrice) {
                    $result = 'lose';
                    $saveFlag = true;
                }
                else {
                    $saveFlag = false;
                }
                
                #
                if ($saveFlag) {
                    $results[] = [
                        'event_date' => $eventDate,
                        'event_price' => $eventPrice,
                        'buy_date' => $buyDate,
                        'buy_price' => $buyPrice,
                        'sell_date'=> $date,
                        'result' => $result,
                        'price' => intval($buyPrice * $ratio * 100) 
                    ];
                    break;
                } else {
                    $idx += 1;
                }
            }
            if ($idx >= count($dailyData)) {
                $results[] = [
                    'event_date' => $eventDate,
                    'event_price' => $eventPrice,
                    'buy_date' => $buyDate ?? null,
                    'buy_price' => $buyPrice ?? null,
                    'sell_date'=> null,
                    'result' => 'unresolved',
                    'price' => null
                ];
            }
        }

        // return result
        return $results;

    }

    function tryDropReturn($dailyData, $eventData, $ratio) {
        // 
        $dates = array_column($dailyData, 'date');
        $opens = array_column($dailyData, 'open');
        $highs = array_column($dailyData, 'high');
        $lows = array_column($dailyData, 'low');

        //
        $results = [];
        foreach($eventData as $data) {
            // rsiが30以下か確認
            $eventDate = $data['date'];
            $value = $data['value'];
            if ($value > 30) {
                continue;
            }

            # 30以上なら翌日のopen価格で株を購入
            $startIdx = array_search($eventDate, $dates);
            if ($startIdx < count($dailyData)) {
                $buyDate = $dates[$startIdx];
                $buyPrice = $opens[$startIdx];
            }

            // set limit and stop price
            $limitPrice = $buyPrice * (1 + $ratio);
            $stopPrice = $buyPrice * (1 - $ratio);

            // 取引が成立するまでループ
            $idx = $startIdx;
            while ($idx < count($dailyData)) {
                # get dailyData
                $date  = $dates[$idx];
                $high  = $highs[$idx];
                $low   = $lows[$idx];

                # 
                if ($high >= $limitPrice) {
                    $result = 'win';
                    $saveFlag = true;
                }
                elseif ($low <= $stopPrice) {
                    $result = 'lose';
                    $saveFlag = true;
                }
                else {
                    $saveFlag = false;
                }
                
                #
                if ($saveFlag) {
                    $results[] = [
                        'event_date' => $eventDate,
                        'buy_date' => $buyDate,
                        'buy_price' => $buyPrice,
                        'sell_date'=> $date,
                        'result' => $result,
                        'price' => intval($buyPrice * $ratio * 100)
                    ];
                    break;
                } else {
                    $idx += 1;
                }
            }

            if ($idx >= count($dailyData)) {
                $results[] = [
                    'event_date' => $eventDate,
                    'value' => $value,
                    'buy_date' => $buyDate ?? null,
                    'buy_price' => $buyPrice ?? null,
                    'sell_date'=> null,
                    'result' => 'unresolved',
                    'price' => null
                ];
            }
        }

        //
        return $results;
    }


}

?>