<?php
function generateTable($x = 10, $y = 10) {
  $data = [];
  
  for ($i = 0; $i <= $y; $i++) {
  $row = [];
    for ($j = 0; $j <= $x; $j++) {
      if ($i === 0 && $j === 0) {
        $row[] = '';
      } else if ($i === 0 || $j === 0) {
        $row[] = $i + $j;
      } else {
        $row[] = $i * $j;
      }
    }
  $data[] = $row;
  }
  return $data;
}
?>