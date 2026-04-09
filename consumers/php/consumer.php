<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$port = getenv('RABBITMQ_PORT') ?: 5672;
$user = getenv('RABBITMQ_USER') ?: 'guest';
$password = getenv('RABBITMQ_PASSWORD') ?: 'guest';
$queue = getenv('RABBITMQ_QUEUE') ?: 'messages';
$logFile = '/logs/php.log';

function startConsumer() {
    global $host, $port, $user, $password, $queue, $logFile;
    
    while (true) {
        try {
            echo "[PHP Consumer] Connecting to {$host}:{$port}...\n";
            
            $connection = new AMQPStreamConnection($host, $port, $user, $password);
            $channel = $connection->channel();
            
            $channel->queue_declare($queue, false, true, false, false);
            
            echo "[PHP Consumer] Waiting for messages on queue: {$queue}\n";
            
            $callback = function (AMQPMessage $msg) use ($logFile, $channel) {
                $message = $msg->getBody();
                $timestamp = date('Y-m-d H:i:s');
                $logEntry = "[{$timestamp}] PHP Consumer received: {$message}" . PHP_EOL;
                
                echo $logEntry;
                file_put_contents($logFile, $logEntry, FILE_APPEND);
                
                $msg->ack();
            };
            
            $channel->basic_qos(null, 1, null);
            $channel->basic_consume($queue, '', false, false, false, false, $callback);
            
            while ($channel->is_consuming()) {
                $channel->wait();
            }
            
            $channel->close();
            $connection->close();
            
        } catch (Exception $e) {
            echo "[PHP Consumer] Error: " . $e->getMessage() . "\n";
            echo "[PHP Consumer] Reconnecting in 5 seconds...\n";
            sleep(5);
        }
    }
}

startConsumer();
