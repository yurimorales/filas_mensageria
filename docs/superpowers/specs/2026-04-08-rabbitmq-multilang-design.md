# RabbitMQ Multi-Language Consumers Demo

## Overview

Learning/demo project demonstrating pub/sub messaging pattern with RabbitMQ and multi-language consumers (PHP, TypeScript, Python).

## Architecture

```
                    ┌─────────────────┐
                    │    RabbitMQ     │
                    │  (fanout exch)  │
                    └────────┬────────┘
                             │
           ┌─────────────────┼─────────────────┐
           │                 │                 │
           ▼                 ▼                 ▼
    ┌────────────┐   ┌────────────┐   ┌────────────┐
    │ PHP        │   │ TypeScript │   │ Python     │
    │ Consumer   │   │ Consumer   │   │ Consumer   │
    └────────────┘   └────────────┘   └────────────┘
           │                 │                 │
           └─────────────────┼─────────────────┘
                             ▼
                      ┌───────────┐
                      │   Logs    │
                      │  (files)  │
                      └───────────┘
```

## Services

| Service | Language | Image | Purpose |
|---------|----------|-------|---------|
| rabbitmq | - | rabbitmq:3-management | Message broker with web UI |
| publisher | PHP | custom | CLI tool to send messages |
| php-consumer | PHP 8.2 | custom | Consumes and logs messages |
| ts-consumer | TypeScript/Node 20 | custom | Consumes and logs messages |
| python-consumer | Python 3.12 | custom | Consumes and logs messages |

## Directory Structure

```
filas_mensageria/
├── rabbitmq/
│   └── Dockerfile
├── publisher/
│   ├── Dockerfile
│   ├── composer.json
│   └── publisher.php
├── consumers/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── consumer.php
│   ├── typescript/
│   │   ├── Dockerfile
│   │   ├── package.json
│   │   ├── tsconfig.json
│   │   └── src/
│   │       └── consumer.ts
│   └── python/
│       ├── Dockerfile
│       └── consumer.py
├── logs/
├── docker-compose.yml
└── README.md
```

## Message Flow

1. **Publisher** sends messages to `messages_exchange` (fanout type)
2. **Consumers** (all 3) receive every message from the queue
3. Each consumer writes to its own log file in `/logs`:
   - `/logs/php.log`
   - `/logs/ts.log`
   - `/logs/python.log`

## Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| RABBITMQ_HOST | rabbitmq | RabbitMQ server hostname |
| RABBITMQ_PORT | 5672 | RabbitMQ AMQP port |
| RABBITMQ_USER | guest | RabbitMQ username |
| RABBITMQ_PASSWORD | guest | RabbitMQ password |
| RABBITMQ_QUEUE | messages | Queue name |
| RABBITMQ_EXCHANGE | messages_exchange | Fanout exchange name |

## Usage

### Start all services

```bash
docker-compose up --build
```

### Send messages via publisher

```bash
# Single message
docker-compose run publisher php publisher.php "Hello World"

# Interactive mode
docker-compose run publisher php publisher.php --interactive

# Send N messages
docker-compose run publisher php publisher.php --loop 10
```

### View logs

```bash
# Follow all logs
tail -f logs/*.log

# View specific log
cat logs/php.log
```

### RabbitMQ Management UI

Access at: http://localhost:15672

- Username: `guest`
- Password: `guest`

## Cross-Platform Compatibility

- Dockerfiles use Alpine/slim base images
- Works on Windows, macOS, and Linux
- Consumers run as long-running CLI processes
- Logs written to mounted volume for host access

## Log Format

Each consumer writes in the format:

```
[YYYY-MM-DD HH:MM:SS] <Consumer> Consumer received: <message>
```

Example:
```
[2026-04-08 14:30:15] PHP Consumer received: Hello from publisher
[2026-04-08 14:30:15] TS Consumer received: Hello from publisher
[2026-04-08 14:30:15] Python Consumer received: Hello from publisher
```
