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

    function getStockPrices($params = []) {
        $pdo = $this->getPdo();
        $code = $params['ticker_code'];
        $query = "SELECT date, close FROM stock_prices WHERE ticker_code = $code";
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
            if ($idx < $period - 1 || $idx > $count - $period) {
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
    function findGoldenCross($smaData, $lmaData) {
        $goldenCross = [];
        $isRising = false;
        $isRecordable = true;
        $smaBuf = null;
        for ($idx = 0; $idx < count($smaData); $idx++) {
            // get values from array
            $date = $smaData[$idx]['date'];
            $sma = $smaData[$idx]['price'];
            $lma = $lmaData[$idx]['price'];

            // if there are null value, record null value
            if (is_null($sma) || is_null($lma)) {
                $goldenCross[] = [
                    'date' => $date,
                    'price' => null,
                ];
                continue;
            }

            // check if the sma is rising or not
            if (is_null($smaBuf) && !is_null($sma)) {
                $smaBuf = $sma;
            } else {
                $isRising = $sma >= $smaBuf;
                $smaBuf = $sma;
            }

            // manage recordable statement
            if ($isRecordable === false) {
                if ($sma < $lma) {
                    $isRecordable = true;
                }
            }

            // if the sma is above the lma, record it as golden cross
            if ($isRising && $isRecordable) {
                if ($sma >= $lma) {
                    $goldenCross[] = [
                        'date' => $date,
                        'price' => $lma
                    ];
                    $isRecordable = false;
                    continue;
                }
            } 

            // othrewise, record null value
            $goldenCross[] = [
                'date' => $date,
                'price' => null,
            ];

        }
        return $goldenCross;
    }
}

?>