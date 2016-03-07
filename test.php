<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 07.03.16
 * Time: 16:35
 */

require 'vendor/autoload.php';

$client = new MongoDB\Client();
$collection = $client->selectCollection('pd', 'titles');

var_dump($collection->findOne());
?>