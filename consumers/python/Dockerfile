FROM python:3.12-slim

WORKDIR /app

RUN pip install pika

COPY consumer.py .

CMD ["python", "consumer.py"]
