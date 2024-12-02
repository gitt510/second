<?php
if(isset($_POST["fruits"])) {
  // セレクトボックスで選択された値を受け取る
  $fruit = $_POST["fruits"];

  // 受け取った値を画面に出力
  echo $fruit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <metacharset = "utf-8">
  <title>セレクトボックスの値をPHPで受け取る</title>
</head>

<body>
  <h1>セレクトボックスの値を送信</h1>
  <form action="index.php" method = "POST">
    <select name= "fruits">
      <option value = "りんご">りんご</option>
      <option value = "れもん">れもん</option>
      <option value = "メロン">メロン</option>
    </select>
    <input type="submit"name="submit"value="送信"/>
  </form>
</body>
</html>