<?php

header('Content-Type: application/json');

try {

    // if json data is already exists >> skip process
    $today = date("Ymd");
    $fileName = "../data/topix_small_1/topnews/" . "$today.json";
    if (file_exists(($fileName))) 
    {
        $jsonData = json_decode(file_get_contents($fileName), true);
        echo json_encode($jsonData);
        exit();
    }

    // include php file
    include('myClass.php');

    // make instance
    $fetcher = new MyDB();
    $simulator = new MySimulator();

    // get companies    
    $companies = $fetcher->getCompanies();

    // find golenCross for each company
    $results = [];
    foreach ($companies as $company) {
        // get company code & name
        $code = $company['code'];
        $name = $company['name'];
        
        // get data from fetcher
        $dailyData = $fetcher->getStockPrices($params = ['code' => $code, 'expr' => 30]);
        if (empty($dailyData)) {
            continue;
        }
        $maData = $fetcher->getMovingAverages($params = ['code' => $code, 'expr' => 30]);
        $oscData  = $fetcher->getOscillators($params);

        // parse data from fetcher
        $dates = array_column($dailyData, 'date');
        $sma5 = array_map(function($data) {
            return ['date' => $data['date'], 'price' => $data['sma_5']];
            }, $maData);
        $sma25 = array_map(function($data) {
            return ['date' => $data['date'], 'price' => $data['sma_25']];
            }, $maData);
        $sma75 = array_map(function($data) {
            return ['date' => $data['date'], 'price' => $data['sma_75']];
            }, $maData);
        $ema5 = array_map(function($data) {
            return ['date' => $data['date'], 'price' => $data['ema_5']];
            }, $maData);
        $ema25 = array_map(function($data) {
            return ['date' => $data['date'], 'price' => $data['ema_25']];
            }, $maData);
        $ema75 = array_map(function($data) {
            return ['date' => $data['date'], 'price' => $data['ema_75']];
            }, $maData);
        $macd = array_map(function($data) {
            return ['date' => $data['date'], 'price' => $data['macd']];
            }, $oscData);
        $signal = array_map(function($data) {
                return ['date' => $data['date'], 'price' => $data['_signal']];
            }, $oscData);
        $rsi14 = 
            array_map(function($data) {
                return ['date' => $data['date'], 'value' => $data['rsi_14']];
            }, $oscData);

        // find golden Cross
        $smaGoldenCross = $simulator->findGoldenCross($sma5, $sma75);
        $emaGoldenCross = $simulator->findGoldenCross($ema5, $ema75);
        $macdGoldenCross = $simulator->findGoldenCross($macd, $signal);

        // find rsi drop date
        $rsiDrops = [];
        foreach ($rsi14 as $data) {
            if ($data['value'] <= 30) {
                $rsiDrops[] = ['date' => $data['date']];
            }
        }

        // filtering event, that is occured before 14days
        $today = new DateTime();
        $xDayBefore = $today->modify('-14 day');
        $smaGoldenCross = array_filter($smaGoldenCross, function($data) use ($xDayBefore) {
            return new Datetime($data['date']) >= $xDayBefore;
        });
        $emaGoldenCross = array_filter($smaGoldenCross, function($data) use ($xDayBefore) {
            return new Datetime($data['date']) >= $xDayBefore;
        });
        $macdGoldenCross = array_filter($smaGoldenCross, function($data) use ($xDayBefore) {
            return new Datetime($data['date']) >= $xDayBefore;
        });
        $rsiDrops = array_filter($rsiDrops, function($data) use ($xDayBefore) {
            return new Datetime($data['date']) >= $xDayBefore;
        });

        // check if events are exists or not
        $tmpArray = [$smaGoldenCross, $emaGoldenCross, $macdGoldenCross, $rsiDrops];
        $filteredArray = array_filter($tmpArray);
        if (count($filteredArray) === 0) {
            continue;
        }
    
        // merge events
        $events = [];
        foreach ([[$smaGoldenCross, 'GoldenCross(SMA)'], [$emaGoldenCross, 'GoldenCross(EMA)'],
                [$macdGoldenCross, 'GodlenCross(MACD)'], [$rsiDrops, 'RSIDrop']] as $data) {
            if (count($data[0]) == 0) {
                continue;
            }
            $eventName = $data[1];
            foreach ($data[0] as $value) {
                $eventDate = $value['date'];
                $events[] = "$eventDate $eventName";
            }
        }

        // get latest event date
        $tmpArray = [];
        foreach ([$smaGoldenCross, $emaGoldenCross, $macdGoldenCross, $rsiDrops] as $data) {
            if (count($data) != 0) {
                $tmpArray[] = end($data)['date'];
            }
        }
        $latestEventDate = max($tmpArray);

        // get latest close value
        $latestClose = end($dailyData)['close'];

        // make resutl and save it
        $result = [
            'code' => $code, 
            'name' => $name, 
            'events' => $events, 
            'latest_event_date' => $latestEventDate,
            'latest_close' => $latestClose
        ];
        $results[] = $result;
    }

    // save results
    file_put_contents($fileName, json_encode(($results)));

    // return rsults
    echo json_encode($results);
    
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>
