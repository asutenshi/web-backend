<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Multiply table</title>
  <style>
    body {
      font-family: sans-serif;
    }
    table {
      border-collapse: collapse;
    }
    td {
      padding: 2px 3px;
      border: 1px solid red;
      text-align: right;
      width: 30px;
    }
    table tr:first-child td,
    table tr td:first-child {
      background-color: orange;
      font-weight: bold;
    }
    table tr:first-child td:first-child {
      background-color: red;
    }
    td.diagonal {
      background-color: yellow;
    }
  </style>
    
</head>    
<body>
  <?php
  echo '<table>';
  
  for ($i = 0; $i <= 10; $i++) {
    echo '<tr>';
    for ($j = 0; $j <= 10; $j++) {
      $class = '';
      if ($i === $j && $i > 0 && $j > 0) $class = ' class="diagonal"';
      
      echo "<td$class>";
      
      if ($i === 0 && $j === 0) {
        echo '';
      } else if ($i === 0 || $j === 0) {
        echo $i + $j;
      } else {
        echo $i * $j;
      }
      echo '</td>';
    }
    echo '</tr>';
  }
  echo '</table>';
  ?>   
</body>
</html>