<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$port = getenv('RABBITMQ_PORT') ?: 5672;
$user = getenv('RABBITMQ_USER') ?: 'guest';
$password = getenv('RABBITMQ_PASSWORD') ?: 'guest';
$queue = getenv('RABBITMQ_QUEUE') ?: 'messages';
$exchange = getenv('RABBITMQ_EXCHANGE') ?: 'messages_exchange';

$connection = new AMQPStreamConnection($host, $port, $user, $password);
$channel = $connection->channel();

$channel->exchange_declare($exchange, 'fanout', false, true, false);
$channel->queue_declare($queue, false, true, false, false);
$channel->queue_bind($queue, $exchange);

echo "RabbitMQ Publisher CLI\n";
echo "=======================\n";
echo "Connected to {$host}:{$port}\n";
echo "Queue: {$queue}\n";
echo "Exchange: {$exchange}\n\n";

if ($argc < 2) {
    echo "Usage: php publisher.php <message>\n";
    echo "   or: php publisher.php --interactive (send messages interactively)\n";
    echo "   or: php publisher.php --loop <count> (send N messages)\n";
    exit(1);
}

$command = $argv[1];

if ($command === '--interactive') {
    echo "Enter messages (Ctrl+C to exit):\n";
    while (true) {
        echo "> ";
        $line = trim(fgets(STDIN));
        if ($line === '') continue;
        
        $msg = new AMQPMessage($line, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $channel->basic_publish($msg, $exchange);
        echo "  Sent: {$line}\n";
    }
} elseif ($command === '--loop') {
    $count = isset($argv[2]) ? (int)$argv[2] : 10;
    echo "Sending {$count} messages...\n";
    
    for ($i = 1; $i <= $count; $i++) {
        $message = "Message #{$i} from Publisher";
        $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $channel->basic_publish($msg, $exchange);
        echo "Sent #{$i}: {$message}\n";
        usleep(500000);
    }
    echo "Done!\n";
} else {
    $message = implode(' ', array_slice($argv, 1));
    $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
    $channel->basic_publish($msg, $exchange);
    echo "Sent: {$message}\n";
}

$channel->close();
$connection->close();
