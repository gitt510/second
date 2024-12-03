<?php

$dsn = 'mysql:dbname=test;host=localhost';
$user = 'user1';
$password = 'password1';

try{
    $dbh = new PDO($dsn, $user, $password);

    $sql = 'select * from mytable';
    foreach ($dbh->query($sql) as $row) {
        print($row['col1'].',');
        print($row['col2']);
        print('<br>');
    }
}catch (PDOException $e){
    print('Error:'.$e->getMessage());
    die();
}

$dbh = null;
?>