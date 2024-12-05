<?php
// entry point?
header('Content-Type: application/json');

try {
    // include php file
    include('myClass.php');

    // make instance
    $fetcher = new MyDB();
    $simulator = new MySimulator();

    // get parameter from http get request
    $params = [
        'ticker_code' => $_GET['ticker_code']
    ];

    // get stock prices and calculate moving average
    $dailyData = $fetcher->getStockPrices($params);
    $smaData = $fetcher->calcMovingAverage($dailyData, 5);
    $lmaData = $fetcher->calcMovingAverage($dailyData, 75);

    // fing golden cross
    $goldenCross = $simulator->findGoldenCross($smaData, $lmaData);

    // make return value
    $ret = $dailyData;
    for ($idx = 0; $idx < count($dailyData); $idx++) {
        $ret[$idx]['sma'] = $smaData[$idx]['price'];
        $ret[$idx]['lma'] = $lmaData[$idx]['price'];
        $ret[$idx]['gcross'] = $goldenCross[$idx]['price'];
    }

    // return result as json
    echo json_encode($ret);
    
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>