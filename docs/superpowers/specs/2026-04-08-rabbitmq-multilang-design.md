# RabbitMQ Multi-Language Consumers Demo

## Visão Geral

Projeto de aprendizado/demonstração que implementa o padrão de mensageria pub/sub com RabbitMQ e consumers em múltiplas linguagens (PHP, TypeScript, Python).

## Arquitetura

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

## Serviços

| Serviço | Linguagem | Imagem | Propósito |
|---------|-----------|--------|-----------|
| rabbitmq | - | rabbitmq:3-management | Broker de mensagens com UI web |
| publisher | PHP | custom | CLI para enviar mensagens |
| php-consumer | PHP 8.2 | custom | Consome e loga mensagens |
| ts-consumer | TypeScript/Node 20 | custom | Consome e loga mensagens |
| python-consumer | Python 3.12 | custom | Consome e loga mensagens |

## Estrutura de Diretórios

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

## Instalação de Dependências

### Docker (Automático)

As dependências são instaladas automaticamente durante `docker-compose up --build`.

### Desenvolvimento Local (Sem Docker)

#### PHP (Publisher e Consumer)

```bash
# Publisher
cd publisher
composer install
php publisher.php "mensagem"

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

Para criar um publisher em Python:

```bash
cd publisher
pip install pika
python publisher.py "mensagem"
```

### Gerenciador de Dependências por Serviço

| Serviço | Gerenciador | Comando |
|---------|-------------|---------|
| Publisher (PHP) | Composer | `composer install` |
| PHP Consumer | Composer | `composer install` |
| TS Consumer | npm | `npm install` |
| Python Consumer | pip | `pip install -r requirements.txt` |

## Fluxo de Mensagens

1. **Publisher** envia mensagens para `messages_exchange` (tipo fanout)
2. **Consumers** (todos os 3) recebem todas as mensagens da fila
3. Cada consumer escreve em seu próprio arquivo de log em `/logs`:
   - `/logs/php.log`
   - `/logs/ts.log`
   - `/logs/python.log`

## Configuração

### Variáveis de Ambiente

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| RABBITMQ_HOST | rabbitmq | Hostname do servidor RabbitMQ |
| RABBITMQ_PORT | 5672 | Porta AMQP do RabbitMQ |
| RABBITMQ_USER | guest | Usuário do RabbitMQ |
| RABBITMQ_PASSWORD | guest | Senha do RabbitMQ |
| RABBITMQ_QUEUE | messages | Nome da fila |
| RABBITMQ_EXCHANGE | messages_exchange | Nome do exchange fanout |

## Como Usar

### Iniciar todos os serviços (Docker)

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

### Enviar mensagens via Publisher

```bash
# Mensagem única
docker-compose run publisher php publisher.php "Olá Mundo"

# Modo interativo (Ctrl+C para sair)
docker-compose run publisher php publisher.php --interactive

# Enviar N mensagens
docker-compose run publisher php publisher.php --loop 10
```

**Modo interativo local (sem Docker):**
```bash
cd publisher
php publisher.php --interactive
```

### Visualizar Logs

```bash
# Seguir todos os logs em tempo real
tail -f logs/*.log

# Ver log específico
cat logs/php.log
cat logs/ts.log
cat logs/python.log
```

### Interface de Gerenciamento do RabbitMQ

Acesse em: http://localhost:15672

- Usuário: `guest`
- Senha: `guest`

## Compatibilidade Cross-Platform

- Dockerfiles usam imagens Alpine/slim
- Funciona no Windows, macOS e Linux
- Consumers rodam como processos CLI de longa duração
- Logs gravados em volume montado para acesso do host

## Formato dos Logs

Cada consumer escreve no formato:

```
[YYYY-MM-DD HH:MM:SS] <Consumer> Consumer received: <mensagem>
```

Exemplo:
```
[2026-04-08 14:30:15] PHP Consumer received: Hello from publisher
[2026-04-08 14:30:15] TS Consumer received: Hello from publisher
[2026-04-08 14:30:15] Python Consumer received: Hello from publisher
```
