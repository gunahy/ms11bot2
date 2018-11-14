<?php

require_once("../vendor/autoload.php");

use Viber\Bot;
use Viber\Api\Sender;
use Viber\Api\Event;

$config = require('./config.php');
$apiKey = $config['apiKey'];

// reply name
$botSender = new Sender([
    'name' => 'Мостострой 11',
    'avatar' => '',
]);


$bot = null;


if (strpos($_SERVER['REQUEST_URI'],"sendMessage")>0) {
 $clid = (isset($_REQUEST['clid']) && !empty($_REQUEST['clid'])) ? $_REQUEST['clid'] : '';
 $mess = (isset($_REQUEST['message']) && !empty($_REQUEST['message'])) ? $_REQUEST['message'] : '';


//$clid = 'jaJRRsNkMzELjqhRCoNdtA==';
//$clid = '8PjzaY55vUfDYhfuiOInLh7dkFykVa6N';
//$mess = $_GET['message'];
//$mess="hi frombot";
//   header('Content-Type: application/json');    
if ($clid!='' && $mess!='') 
    {
          $bot = new Bot(['token' => $apiKey]);
          $bot->getClient()->sendMessage(
              (new \Viber\Api\Message\Text())
              ->setSender($botSender)
              ->setReceiver($clid)
              ->setMinApiVersion(3)
              ->setText($mess)
          );
          $bot->run(new Event([]));
          header ("HTTP/1.1 200 OK!");
       //echo '{"code" : 200, "comment" : "OK!"}';
       echo '{"clid" : "'.$clid.'", "message" : "'.$mess.'"}';
    }else{
        header ("HTTP/1.1 400 NOT DATA FOUND!");
       echo '{"code" : 400, "comment" : "NOT DATA FOUND!"}';

    }
}


if (strpos($_SERVER['REQUEST_URI'],"getMessages")>0) {

    header('Content-Type: application/json');

   if (file_exists('results.json'))
   {
    $filename = filemtime("./results.json").'.json';
    rename('results.json',$filename);

    $inp = file_get_contents($filename);
    $inp = '{"filename":"'.$filename.'","data":['.substr($inp,0,-2).']}';
    echo $inp;
   }else{
    echo '[]';
  }
}
