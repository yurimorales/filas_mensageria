import * as amqp from 'amqplib';
import * as fs from 'fs';

const host = process.env.RABBITMQ_HOST || 'rabbitmq';
const port = process.env.RABBITMQ_PORT || 5672;
const user = process.env.RABBITMQ_USER || 'guest';
const password = process.env.RABBITMQ_PASSWORD || 'guest';
const queue = process.env.RABBITMQ_QUEUE || 'messages';
const logFile = '/logs/ts.log';

async function start() {
  try {
    const connection = await amqp.connect(`amqp://${user}:${password}@${host}:${port}`);
    const channel = await connection.createChannel();

    await channel.assertQueue(queue, { durable: true });

    console.log(`[TS Consumer] Waiting for messages on queue: ${queue}`);

    channel.consume(queue, (msg) => {
      if (msg) {
        try {
          const message = msg.content.toString();
          const timestamp = new Date().toISOString();
          const logEntry = `[${timestamp}] TS Consumer received: ${message}\n`;
          
          console.log(logEntry);
          fs.appendFileSync(logFile, logEntry);
          
          channel.ack(msg);
        } catch (err) {
          console.error('Error processing message:', err);
          channel.nack(msg, false, true);
        }
      }
    });

    connection.on('error', (err) => {
      console.error('Connection error:', err);
    });

    connection.on('close', () => {
      console.log('Connection closed, reconnecting...');
      setTimeout(start, 5000);
    });
  } catch (err) {
    console.error('Failed to start:', err);
    setTimeout(start, 5000);
  }
}

start();
