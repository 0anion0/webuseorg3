<?php

/* 
 * (с) 2015 Грибов Павел
 * http://грибовы.рф * 
 * Если исходный код найден в сети - значит лицензия GPL v.3 * 
 * В противном случае - код собственность ГК Яртелесервис, Мультистрим, Телесервис, Телесервис плюс * 
 */



include_once ("../../../config.php");                    // загружаем первоначальные настройки

// загружаем классы

include_once("../../../class/sql.php");               // загружаем классы работы с БД
include_once("../../../class/config.php");		// загружаем классы настроек
include_once("../../../class/users.php");		// загружаем классы работы с пользователями
include_once("../../../class/employees.php");		// загружаем классы работы с профилем пользователя


// загружаем все что нужно для работы движка

include_once("../../../inc/connect.php");		// соеденяемся с БД, получаем $mysql_base_id
include_once("../../../inc/config.php");              // подгружаем настройки из БД, получаем заполненый класс $cfg
include_once("../../../inc/functions.php");		// загружаем функции
include_once("../../../inc/login.php");		// загружаем функции

$astra_id=$_GET['astra_id'];

$sql="select * from astra_servers where id='$astra_id'";
$result = $sqlcn->ExecuteSQL( $sql ) or die("Не могу выбрать список серверов astra_servers!".mysqli_error($sqlcn->idsqlconnection));
while($row = mysqli_fetch_array($result)) {
  $path=$row["path"];   
};
$image = imagecreatefromjpeg("$path/$astra_id/pic/bk1.jpg");

header('Content-Type: image/jpeg');
imagejpeg($image);