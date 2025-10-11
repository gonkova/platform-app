
# AI Tools Dashboard

## Инструкции за инсталация

1. Клонирайте репото:
```bash
git clone <your-repo-url>
cd <repo-folder>
```
2. Инсталирайте зависимостите:
```bash
composer install
npm install
```
3. Настройте `.env` файл според `example.env`:
```bash
cp .env.example .env
php artisan key:generate
```
4. Миграции и seed:
```bash
php artisan migrate --seed
```

## Стартиране (без Docker)

```bash
php artisan serve
npm run dev
```
Отворете `http://127.0.0.1:8000`

## Добавяне на тулове

- Влезте като администратор
- Отидете в админ панела > Добавяне на нов тул
- Попълнете име, описание, категория и статус

## Ролева система и права

- Owner: пълен достъп, одобрение/отказ на тулове
- Admin: управление на тулове, категории
- User: достъп само до approved тулове

Middleware защита: `role:owner|admin` за админ рутове

## AI агенти

- В системата могат да се стартират AI агенти за автоматизация
- Всеки агент получава начални промтове за стартиране:
  - Пример: `Summarize all new AI tools into categories`
  - Пример: `Check for new suggested tools and mark as approved if valid`

## Начални промтове за агент за разработка

- “Прегледай кода и предложи оптимизации”
- “Генерирай audit log за последните 24 часа”
- “Създай кеширана версия на категориите с броя на туловете”




