<?php
require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;
$curl = new Curl();
$username = "LazyKarlson";
$url = "https://d3.ru/api/users/".$username."/posts/";
$curl->get($url);

if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
	 echo "Постов: ".$curl->response->item_count."Страниц:".$curl->response->page_count;
}
