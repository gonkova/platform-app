# 🚀 AI Tools Platform

A comprehensive Laravel-based platform for managing and cataloging AI tools with advanced features including role-based access control, two-factor authentication, approval workflows, and activity logging.

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Database Schema](#-database-schema)
- [API Documentation](#-api-documentation)
- [Security Features](#-security-features)
- [Usage Examples](#-usage-examples)
- [Development](#-development)
- [AI Development Agents](#-ai-development-agents)
- [Testing](#-testing)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

### Core Functionality
- **AI Tools Management**: Create, read, update, and delete AI tools with comprehensive metadata
- **Approval Workflow**: Three-state approval system (pending, approved, rejected)
- **Category System**: Organize tools by categories with many-to-many relationships
- **Role-Based Tools**: Associate tools with specific developer roles

### Security & Authentication
- **JWT Authentication**: Secure API authentication using Laravel Sanctum
- **Two-Factor Authentication (2FA)**: Google Authenticator support with backup codes
- **Role-Based Access Control (RBAC)**: Three-tier permission system (Owner, Frontend, Backend)
- **Resource Ownership**: Users can only modify their own resources

### Activity & Monitoring
- **Comprehensive Activity Logging**: Track all user actions and system events
- **User Activity Dashboard**: View personal and system-wide activity logs
- **IP & User Agent Tracking**: Security audit trail for all activities

### Advanced Features
- **Automatic Slug Generation**: SEO-friendly URLs for all resources
- **Cache Management**: Observer pattern for intelligent cache invalidation
- **Bulk Operations**: Batch approve/reject multiple tools
- **Status Tracking**: Visual status indicators with colors and labels

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 12.0
- **PHP Version**: 8.2+
- **Database**: SQLite (easily switchable to MySQL/PostgreSQL)
- **Authentication**: Laravel Sanctum
- **2FA**: pragmarx/google2fa-laravel

### Frontend
- **Build Tool**: Vite 7.0
- **CSS Framework**: Tailwind CSS 4.0
- **HTTP Client**: Axios

### Development Tools
- **Code Quality**: Laravel Pint (PHP CS Fixer)
- **Testing**: PHPUnit 11.5
- **CLI**: Laravel Tinker, Artisan
- **Local Development**: Laravel Sail (Docker)
- **Log Viewer**: Laravel Pail

## 💻 System Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM or Yarn
- SQLite/MySQL/PostgreSQL
- Git

## 📦 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd platform-app
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=sqlite
# For SQLite (default)
DB_DATABASE=/absolute/path/to/database/database.sqlite

# OR for MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=platform_app
# DB_USERNAME=root
# DB_PASSWORD=
```

### 6. Run Migrations & Seeders

```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed initial data (roles, categories, sample tools)
php artisan db:seed
```

### 7. Start Development Server

#### Option 1: Using Composer Script (Recommended)
```bash
composer run dev
```
This runs:
- Laravel development server (port 8000)
- Queue worker
- Log viewer (Pail)
- Vite dev server

#### Option 2: Manual Start
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
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




