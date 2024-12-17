<?php

// define variable database
$host = 'localhost';
$dbname = 'topix_small_1';
$username = 'user1';
$password = 'password1';

## define function
function updateTable($fname, $tablename) {
    // connect database
    global $host, $dbname, $username, $password;
    $pdo = new PDO("mysql:dbname=$dbname;host=$host;charset=utf8", $username, $password);

    // open file
    $file = fopen($fname, 'r');

    // make insert query
    $headers = fgetcsv($file);
    $columns = '('.implode(', ', $headers).')';
    $placeholders = '('.implode(', ', array_fill(0, count($headers), '?')).')';
    $insertQuery = "INSERT IGNORE INTO $tablename $columns VALUES $placeholders";

    // set statement and insert record
    $stmt = $pdo->prepare($insertQuery);
    $pdo->beginTransaction();
    try {
        while (($data = fgetcsv($file)) !== false) {
            $stmt->execute($data);
        }
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        print_r($e->getMessage());
    }

    // close file
    fclose($file);
}

function deleteAllRecords($tablename) {
    // connect database
    global $host, $dbname, $username, $password;
    $pdo = new PDO("mysql:dbname=$dbname;host=$host;charset=utf8", $username, $password);

    // truncate table
    $stmt = $pdo->prepare("TRUNCATE TABLE $tablename");
    $stmt->execute();
}

function main() {
    updateTable('data\topix_small_1\companies.csv', 'companies');
    updateTable('data\topix_small_1\stock_prices.csv', 'stock_prices');
    updateTable('data\topix_small_1\diviedends.csv', 'diviedends');
    updateTable('data\topix_small_1\yutais.csv', 'yutais');
}

main();
?>