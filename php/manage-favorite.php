<?php

header('Content-Type: application/json');

try {
    // include php file
    include('myClass.php');

    // make instance
    $fetcher = new MyDB();

    // get parameter from http get request
    $params = [
        'action' => $_GET['action'],
        'code' => $_GET['code'] ?? null,
        'name' => $_GET['name'] ?? null,
        'value' => $_GET['value'] ?? null,
    ];

    if ($params['action'] == 'get') {
        $ret = $fetcher->getFavorites($params);
        echo json_encode($ret);
    } else if ($params['action'] == 'update') {
        $ret = $fetcher->updateFavorites($params);
        echo json_encode($ret);
    }

    // // return result as json
    // echo json_encode($ret);
    
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>