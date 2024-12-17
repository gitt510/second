<?php

header('Content-Type: application/json');

try {
    // include php file
    include('myClass.php');

    // make instance
    $fetcher = new MyDB();
    $simulator = new MySimulator();

    // get parameter from http get request
    $params = [
        'code' => $_GET['code'],
        'expr' => $_GET['expr'],
    ];

    // get data from fetcher
    $dailyData = $fetcher->getStockPrices($params);
    $maData = $fetcher->getMovingAverages($params);
    $oscData  = $fetcher->getOscillators($params);
    $diviedends = $fetcher->getDiviedends($params);

    # parse data from fetcher
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
    
    

    // find golden cross
    $smaGoldenCross = $simulator->findGoldenCross($sma5, $sma75);
    $emaGoldenCross = $simulator->findGoldenCross($ema5, $ema75);
    $macdGoldenCross = $simulator->findGoldenCross($macd, $signal);

    // simulate cross return
    $smaCrossReturns = $simulator->tryCrossReturn($dailyData, $smaGoldenCross, 0.03);
    $emaCrossReturns = $simulator->tryCrossReturn($dailyData, $emaGoldenCross, 0.03);
    $macdCrossReturns = $simulator->tryCrossReturn($dailyData, $macdGoldenCross, 0.03);
    $rsiDropReturns = $simulator->tryDropReturn($dailyData, $rsi14, 0.03);

    // make return value
    $ret = [
        'stock_prices' => $dailyData,
        'moving_averages' => $maData,
        'oscillators'=> $oscData,
        'diviedends' => $diviedends,
        'sma_cross_returns' => $smaCrossReturns,
        'ema_cross_returns' => $emaCrossReturns,
        'macd_cross_returns' => $macdCrossReturns,
        'rsi_drop_returns' => $rsiDropReturns,
    ];

    // return result as json
    echo json_encode($ret);
    
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>