# Environment Configuration

## Инструкции за настройка

Уверете се, че във вашия `.env` файл има следните променливи:

```env
# Application
APP_NAME="Platform Project"
APP_URL=http://127.0.0.1:8000

# Frontend URL (за CORS)
FRONTEND_URL=http://localhost:3000

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# Other settings...
```

### За Development:
```env
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:3000
```

### За Production:
```env
APP_URL=https://your-backend-domain.com
FRONTEND_URL=https://your-frontend-domain.com
```

## Важни забележки:
- `FRONTEND_URL` се използва в CORS конфигурацията
- След промяна на environment променливи, изчистете config cache:
  ```bash
  php artisan config:clear
  php artisan cache:clear
  ```
- Не commit-вайте `.env` файла в Git (само `.env.example`)

