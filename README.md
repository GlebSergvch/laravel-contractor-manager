# Contractor Project

Стек: **Laravel backend + Postgres + Nginx**.  
Все сервисы разворачиваются через Docker Compose.

## Предварительные требования

- Docker ≥ 24.x
- Docker Compose (или встроенный `docker compose`)
- Make (опционально, но удобно)

---

## Установка и запуск проекта локально

1. **Клонируем репозиторий**
```bash
git clone <репозиторий>
cd <директория проекта>
```

2. **Создаём файл окружения**

Копировать файл
```bash
cp .env.example .env
```
прописать свои токены DADATA_TOKEN, DADATA_SECRET

3. **Сборка и поднятие контейнеров**

из корня проекта выполнить:
```bash
make setup
```