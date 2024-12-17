<?php

header('Content-Type: application/json');

try {
    // include php file
    include('myClass.php');

    // make instance
    $fetcher = new MyDB();

    // get companies
    $companies = $fetcher->getCompanies();
    $ret = [
        'companies' => $companies
    ];

    // return result as json
    echo json_encode($ret);
    
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>