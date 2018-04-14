<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Пример работы с БД Tecdoc 2018</title>
</head>

<body>

<?php

require_once 'tecdoc.class.php';

// Все марки
$data = Tecdoc::getMakes( 'passenger' );

// Модели Audi
//$data = Tecdoc::getModels( 5, 'passenger' );

// Модификации Audi A4 (8EC, B7)
//$data = Tecdoc::getModifications( 5376, 'passenger' );

// Наименование марки с id=5
//$data = Tecdoc::getMake( 5, 'passenger' );

// Наименование модели с id=5376
//$data = Tecdoc::getModel( 5376, 'passenger' );

// Наименование модификации с id=18371
//$data = Tecdoc::getType( 18371, 'passenger' );

// Дерево всех категорий для AUDI A4 (8EC, B7) 2.0 TFSI
//$data = Tecdoc::getSections( 18371, 'passenger' );

// Дерево тормозной системы для AUDI A4 (8EC, B7) 2.0 TFSI
//$data = Tecdoc::getSections( 18371, 'passenger', 100006 );

echo '<pre>';
print_r($data);
echo '</pre>';

if( SPEED_TEST == 1 ) echo '<hr>Time:  ' . number_format(( microtime(true) - $startTime), 4) . ' Seconds';

?>

</body>
</html>