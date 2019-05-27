<?php
header('Content-type:application/json;charset=utf-8');
//JSONP - делаем JSONP объект
echo $_GET['callback'] . json_encode($data, JSON_UNESCAPED_UNICODE);
