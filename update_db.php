<?php
// データベース接続設定
$host = 'localhost';
$dbname = 'your_database';
$username = 'your_username';
$password = 'your_password';

try {
   // PDO接続
   $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   // CSVファイル読み込み
   $filename = 'data.csv';
   $file = fopen($filename, 'r');

   // 最初の行（ヘッダー）を読み込む
   $headers = fgetcsv($file);

   // テーブル作成（ヘッダーに基づく）
   $createTableQuery = "CREATE TABLE IF NOT EXISTS csv_data (";
   foreach ($headers as $header) {
       $createTableQuery .= "`" . str_replace(' ', '_', $header) . "` VARCHAR(255), ";
   }
   $createTableQuery = rtrim($createTableQuery, ', ') . ")";
   $pdo->exec($createTableQuery);

   // プリペアドステートメント作成
   $placeholders = implode(',', array_fill(0, count($headers), '?'));
   $insertQuery = "INSERT INTO csv_data (" . 
       implode(',', array_map(function($h) { return "`" . str_replace(' ', '_', $h) . "`"; }, $headers)) . 
       ") VALUES ($placeholders)";
   $stmt = $pdo->prepare($insertQuery);

   // データ挿入
   while (($data = fgetcsv($file)) !== false) {
       $stmt->execute($data);
   }

   fclose($file);
   echo "CSVデータが正常にインポートされました。";

} catch(PDOException $e) {
   echo "エラー: " . $e->getMessage();
}
?>