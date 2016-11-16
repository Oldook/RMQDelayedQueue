<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();
$channel->queue_declare('myQ', false, true, false, false);
$channel->queue_bind('myQ', 'amq.direct', 'myQ');

$delayChannel = $connection->channel();

$delayChannel->queue_declare('myQ_delay', false, true, false, false, false, array(
    'x-message-ttl' => ['I', 5000],
    'x-dead-letter-exchange' => ['S', 'amq.direct'],
    'x-dead-letter-routing-key' => ['S', 'myQ']
));

$data = json_encode(array("id" => 267));

$msg = new AMQPMessage($data);

$channel->basic_publish($msg, '', 'myQ_delay');


echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();

?>