<?php
// CSVファイルをテーブルとして表示
function displayCSVAsTable($filename) {
    // ファイルを開く
    $file = fopen($filename, 'r');
    
    if ($file !== false) {
        echo "<table border='1'>";
        
        // ヘッダー行（最初の行）
        $headers = fgetcsv($file);
        echo "<thead><tr>";
        foreach ($headers as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr></thead>";
        
        // データ行
        echo "<tbody>";
        while (($data = fgetcsv($file)) !== false) {
            echo "<tr>";
            foreach ($data as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
        
        fclose($file);
    } else {
        echo "ファイルを開けませんでした。";
    }
}

// 使用例
displayCSVAsTable('data\TOPIX_Small_1\companies.csv');
?>