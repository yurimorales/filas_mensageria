import os
import sys
import pika
from datetime import datetime
import time

host = os.getenv('RABBITMQ_HOST', 'rabbitmq')
port = int(os.getenv('RABBITMQ_PORT', 5672))
user = os.getenv('RABBITMQ_USER', 'guest')
password = os.getenv('RABBITMQ_PASSWORD', 'guest')
queue = os.getenv('RABBITMQ_QUEUE', 'messages')
log_file = '/logs/python.log'

def callback(ch, method, properties, body):
    message = body.decode()
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    log_entry = f"[{timestamp}] Python Consumer received: {message}\n"
    
    print(log_entry)
    
    with open(log_file, 'a') as f:
        f.write(log_entry)
    
    ch.basic_ack(delivery_tag=method.delivery_tag)

def start_consumer():
    while True:
        try:
            credentials = pika.PlainCredentials(user, password)
            parameters = pika.ConnectionParameters(
                host=host, 
                port=port, 
                credentials=credentials,
                connection_attempts=3,
                retry_delay=5
            )
            
            connection = pika.BlockingConnection(parameters)
            channel = connection.channel()
            
            channel.queue_declare(queue=queue, durable=True)
            
            print(f"[Python Consumer] Waiting for messages on queue: {queue}")
            
            channel.basic_qos(prefetch_count=1)
            channel.basic_consume(queue=queue, on_message_callback=callback)
            
            channel.start_consuming()
            
        except pika.exceptions.AMQPConnectionError as err:
            print(f"Connection error: {err}, retrying in 5 seconds...")
            time.sleep(5)
        except KeyboardInterrupt:
            print("Shutting down...")
            sys.exit(0)
        except Exception as err:
            print(f"Error: {err}, retrying in 5 seconds...")
            time.sleep(5)

if __name__ == '__main__':
    start_consumer()
