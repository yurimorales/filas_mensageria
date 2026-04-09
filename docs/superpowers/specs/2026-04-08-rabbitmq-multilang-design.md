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
│   ├── composer.lock
│   └── publisher.php
├── consumers/
│   ├── php/
│   │   ├── Dockerfile
│   │   ├── composer.json
│   │   ├── composer.lock
│   │   └── consumer.php
│   ├── typescript/
│   │   ├── Dockerfile
│   │   ├── package.json
│   │   ├── package-lock.json
│   │   ├── tsconfig.json
│   │   └── src/
│   │       └── consumer.ts
│   └── python/
│       ├── Dockerfile
│       ├── requirements.txt
│       └── consumer.py
├── logs/
├── docker-compose.yml
└── README.md
```

## Installing Dependencies

### Docker (Automatic)

Dependencies are installed automatically during `docker-compose up --build`.

### Local Development (Without Docker)

#### PHP (Publisher e Consumer)

```bash
# Publisher
cd publisher
composer install
php publisher.php "message"

# Consumer PHP
cd consumers/php
composer install
php consumer.php
```

#### TypeScript (Consumer)

```bash
cd consumers/typescript
npm install
npm run build
npm start
```

#### Python (Consumer)

```bash
cd consumers/python
pip install -r requirements.txt
python consumer.py
```

#### Python Publisher (Alternativa)

Se quiser criar um publisher em Python:

```bash
cd publisher
pip install pika
python publisher.py "message"
```

### Dependency Manager by Service

| Service | Manager | Command |
|---------|---------|---------|
| Publisher (PHP) | Composer | `composer install` |
| PHP Consumer | Composer | `composer install` |
| TS Consumer | npm | `npm install` |
| Python Consumer | pip | `pip install -r requirements.txt` |

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

### Start all services (Docker)

```bash
docker-compose up --build
```

Este comando:
1. Constrói as imagens Docker para cada serviço
2. Instala as dependências automaticamente
3. Inicia todos os containers (RabbitMQ + 3 consumers)
4. Os consumers ficam rodando em foreground esperando mensagens

### Rodar Consumers Localmente (Sem Docker)

Os consumers podem ser executados diretamente no terminal (Windows/Linux/macOS):

```bash
# Terminal 1: PHP Consumer
cd consumers/php
composer install
php consumer.php

# Terminal 2: TypeScript Consumer
cd consumers/typescript
npm install && npm run build
npm start

# Terminal 3: Python Consumer
cd consumers/python
pip install -r requirements.txt
python consumer.py
```

### Send messages via publisher

```bash
# Single message
docker-compose run publisher php publisher.php "Hello World"

# Interactive mode (Ctrl+C para sair)
docker-compose run publisher php publisher.php --interactive

# Send N messages
docker-compose run publisher php publisher.php --loop 10
```

**Modo interativo local (sem Docker):**
```bash
cd publisher
php publisher.php --interactive
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
