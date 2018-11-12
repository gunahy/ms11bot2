<?php

/**
 * Before you run this example:
 * 1. install monolog/monolog: composer require monolog/monolog
 * 2. copy config.php.dist to config.php: cp config.php.dist config.php
 *
 * @author Novikov Bogdan <hcbogdan@gmail.com>
 */

require_once("../vendor/autoload.php");

use Viber\Bot;
use Viber\Api\Sender;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$config = require('./config.php');
$apiKey = $config['apiKey'];
$debug = $config['debug'];

// reply name
$botSender = new Sender([
    'name' => '1CCRM',
    'avatar' => 'https://rarus.ru/favicon.ico',
]);

// log bot interaction
$log = new Logger('bot');
if ($debug) {
    $log->pushHandler(new StreamHandler('./bot.log'));
} else {
    $log->pushHandler(new \Monolog\Handler\NullHandler());
}

$bot = null;

try {
    // create bot instance
    $bot = new Bot(['token' => $apiKey]);
//    if ($bot) {
//        $log->info('Actual sign: '. $bot->getSignHeaderValue());
//        $log->info('Actual body: '. $bot->getInputBody());
//    }
    $bot
    ->onConversation(function ($event) use ($bot, $botSender, $log) {
        $log->info('onConversation '. var_export($event, true));
        // this event fires if user open chat, you can return "welcome message"
        // to user, but you can't send more messages!
        return (new \Viber\Api\Message\Text())
            ->setSender($botSender)
            ->setText("Can i help you?");
    })
    ->onText('|start|si', function ($event) use ($bot, $botSender, $log) {
        $log->info('onText start '. var_export($event, true));
        $bot->getClient()->sendMessage(
            (new \Viber\Api\Message\Text())
            ->setSender($botSender)
            ->setReceiver($event->getSender()->getId())
            ->setMinApiVersion(3)
            ->setText("We need your phone number")
            ->setKeyboard(
            (new \Viber\Api\Keyboard())
                ->setButtons([
                    (new \Viber\Api\Keyboard\Button())
                            ->setActionType('share-phone')
                            ->setActionBody('reply')
                            ->setText('Отправить номер телефона')
                        ])
            )
        );
    })
    ->onText('|info|si', function ($event) use ($bot, $botSender, $log) {
        $log->info('onText info'. var_export($event, true));
        $bot->getClient()->sendMessage(
            (new \Viber\Api\Message\Text())
            ->setSender($botSender)
            ->setReceiver($event->getSender()->getId())
            ->setText('ID: '.$event->getSender()->getId()."\n".' Name: '.$event->getSender()->getName())
        );
    })
    ->onText('|.*|s', function ($event) use ($bot, $botSender, $log) {

        $out = [];
        $out['clid']= $event->getSender()->getId();
        $out['name']= $event->getSender()->getName();
        $out['message']= $event->getMessage()->getText();
        $jsf = fopen('./results.json', 'a');
        fwrite($jsf, json_encode($out).",\n");
        fclose($jsf);

        $log->info('onText '. var_export($event, true));
        $log->info('onText.getMessage '. var_export($event->getMessage(), true));


        // .* - match any symbols
        /*$bot->getClient()->sendMessage(
            (new \Viber\Api\Message\Text())
            ->setSender($botSender)
            ->setReceiver($event->getSender()->getId())
            ->setText("Привет ".$event->getSender()->getName())
        );*/
    })
    ->on(function ($event) {
        return true; // match all
    }, function ($event) use ($log) {
        $log->info('Other event: '. var_export($event, true));
        $out = $event;
        $jsf = fopen('./event.json', 'a');
        fwrite($jsf, json_encode($out).",\n");
        fclose($jsf);
    })
    ->run();
} catch (Exception $e) {
    $log->warning('Exception: ',array($e->getMessage()));
    if ($bot) {
        $log->warning('Actual sign: '. $bot->getSignHeaderValue());
        $log->warning('Actual body: '. $bot->getInputBody());
    }
}
