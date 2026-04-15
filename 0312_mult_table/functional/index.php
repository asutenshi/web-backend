<?php
require_once 'functions.php';

$width = (int)($_GET['width'] ?? 10);
$height = (int)($_GET['height'] ?? 10);

$width = min(max($width, 1), 100);
$height = min(max($height, 1), 100);

$data = generateTable($width, $height);

if (PHP_SAPI === 'cli') {
  print_r($data);
} else {
  include_once 'template.php';
}
?>