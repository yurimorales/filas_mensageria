# RabbitMQ Multi-Language Consumers Demo

Projeto de demonstração de mensageria com RabbitMQ, usando o padrão pub/sub onde um publisher envia mensagens e 3 consumers em linguagens diferentes (PHP, TypeScript e Python) recebem todas as mensagens simultaneamente.

## Como Funciona

```
                    ┌─────────────────┐
                    │    RabbitMQ     │
                    │  (fanout exch)  │
                    └────────┬────────┘
                             │
            ┌────────────────┼────────────────┐
            ▼                ▼                ▼
     ┌────────────┐  ┌────────────┐  ┌────────────┐
     │ PHP        │  │ TypeScript │  │ Python     │
     │ Consumer   │  │ Consumer   │  │ Consumer   │
     └────────────┘  └────────────┘  └────────────┘
```

1. O **Publisher** envia mensagens para o RabbitMQ
2. O **RabbitMQ** distribui as mensagens para todos os consumers (padrão fanout)
3. Cada **Consumer** processa a mensagem e salva em seu próprio log

## Pré-requisitos

- Docker e Docker Compose instalados
- Ou: PHP 8.2+, Node.js 20+, Python 3.12+ com Composer/npm/pip

## Como Rodar

### 1. Iniciar todos os serviços

```bash
docker-compose up --build
```

Isso vai:
- Construir as imagens Docker de cada serviço
- Iniciar o RabbitMQ (porta 5672 para AMQP, 15672 para UI web)
- Iniciar os 3 consumers rodando em background

### 2. Abrir a interface do RabbitMQ

Acesse: http://localhost:15672

- Usuário: `guest`
- Senha: `guest`

### 3. Enviar mensagens

```bash
# Mensagem única
docker-compose run publisher php publisher.php "Minha primeira mensagem"

# Modo interativo (digite mensagens manualmente)
docker-compose run publisher php publisher.php --interactive

# Enviar 10 mensagens automaticamente
docker-compose run publisher php publisher.php --loop 10
```

### 4. Ver os logs dos consumers

```bash
# Ver todos os logs
cat logs/php.log
cat logs/ts.log
cat logs/python.log

# Ou seguir em tempo real
tail -f logs/*.log
```

## Rodar Sem Docker (Desenvolvimento Local)

Se preferir rodar os consumers diretamente no terminal:

```bash
# Terminal 1: PHP Consumer
cd consumers/php
composer install
php consumer.php

# Terminal 2: TypeScript Consumer
cd consumers/typescript
npm install
npm run build
npm start

# Terminal 3: Python Consumer
cd consumers/python
pip install -r requirements.txt
python consumer.py
```

**Nota:** O RabbitMQ precisa estar rodando (via Docker ou instalação local).

## Estrutura do Projeto

```
filas_mensageria/
├── rabbitmq/           # Configuração do RabbitMQ
├── publisher/          # CLI PHP para enviar mensagens
├── consumers/           # 3 consumers em linguagens diferentes
│   ├── php/            # Consumer em PHP
│   ├── typescript/     # Consumer em TypeScript
│   └── python/         # Consumer em Python
├── logs/               # Logs dos consumers (criado automaticamente)
└── docker-compose.yml  # Orquestração de todos os serviços
```

## Formato dos Logs

```
[2026-04-08 14:30:15] PHP Consumer received: Minha primeira mensagem
[2026-04-08 14:30:15] TS Consumer received: Minha primeira mensagem
[2026-04-08 14:30:15] Python Consumer received: Minha primeira mensagem
```

## Parar a Aplicação

```bash
docker-compose down
```

Para remover também os dados do RabbitMQ:
```bash
docker-compose down -v
```
