<?php

// include php file
include('MyClass.php');

// make instance
$fetcher = new MyDB();
$simulator = new MySimulator();

// get parameter from http get request
$params = [
    'ticker_code' => '8008',
];
print_r($params);

// get stock prices and calculate moving average
$dailyData = $fetcher->getStockPrices($params);
$smaData = $fetcher->calcMovingAverage($dailyData, 5);
$lmaData = $fetcher->calcMovingAverage($dailyData, 75);

// fing golden cross
$goldenCross = $simulator->findGoldenCross($smaData, $lmaData);
for ($idx = 0; $idx < count($goldenCross); $idx++) {
    if (!is_null($goldenCross[$idx]['price'])) {
        print_r($goldenCross[$idx]);
    }
}

print_r(count($dailyData));
print_r(count($smaData));
print_r(count($goldenCross));
// make return value
$ret = $dailyData;
for ($idx = 0; $idx < count($dailyData); $idx++) {
    $ret[$idx]['sma'] = $smaData[$idx]['price'];
    $ret[$idx]['lma'] = $lmaData[$idx]['price'];
    $ret[$idx]['g-cross'] = $goldenCross[$idx]['price'];
}

?>