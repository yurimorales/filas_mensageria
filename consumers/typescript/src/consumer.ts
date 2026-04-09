import * as amqp from 'amqplib';

const host = process.env.RABBITMQ_HOST || 'rabbitmq';
const port = process.env.RABBITMQ_PORT || 5672;
const user = process.env.RABBITMQ_USER || 'guest';
const password = process.env.RABBITMQ_PASSWORD || 'guest';
const queue = process.env.RABBITMQ_QUEUE || 'messages';
const logFile = '/logs/ts.log';

async function start() {
  const connection = await amqp.connect(`amqp://${user}:${password}@${host}:${port}`);
  const channel = await connection.createChannel();

  await channel.assertQueue(queue, { durable: true });

  console.log(`[TS Consumer] Waiting for messages on queue: ${queue}`);

  channel.consume(queue, (msg) => {
    if (msg) {
      const message = msg.content.toString();
      const timestamp = new Date().toISOString();
      const logEntry = `[${timestamp}] TS Consumer received: ${message}\n`;
      
      console.log(logEntry);
      
      const fs = require('fs');
      fs.appendFileSync(logFile, logEntry);
      
      channel.ack(msg);
    }
  });
}

start().catch(console.error);
