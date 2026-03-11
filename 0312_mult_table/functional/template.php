<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  <table>  
    <?php foreach ($data as $y => $row): ?>
      <tr>
        <?php foreach ($row as $x => $cell): ?>
          <?php $class = ""; ?>
          <?php if ($x === $y && $x > 0 && $y > 0) $class = ' class="diagonal"'; ?>
          <td<?= $class ?>>
            <?= $cell ?>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach?>
  </table>
</body>
</html>

