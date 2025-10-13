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

# Terminal 3: Queue worker (optional)
php artisan queue:work
```

### 8. Access the Application

- **Application**: http://localhost:8000
- **API Endpoint**: http://localhost:8000/api

## ⚙ Configuration

### Default Credentials (After Seeding)

```
Email: admin@example.com
Password: password
Role: Owner
```

### Two-Factor Authentication Setup

1. Enable 2FA: `POST /api/2fa/enable`
2. Scan QR code with Google Authenticator
3. Confirm with code: `POST /api/2fa/confirm`
4. Save backup codes securely

### Role Configuration

Three predefined roles are available:

| Role | Permissions | Description |
|------|-------------|-------------|
| **Owner** | Full access | Can approve/reject tools, view all activities |
| **Frontend** | Limited access | Access to frontend resources and tools |
| **Backend** | Limited access | Access to backend resources and tools |

## 📊 Database Schema

### Core Tables

#### Users
- Authentication and profile information
- Two-factor authentication settings
- Role assignment

#### Roles
- Role definitions (owner, frontend, backend)
- Display names and descriptions

#### AI Tools
- Tool metadata (name, description, URLs)
- Pricing information (free/paid)
- Status tracking (pending, approved, rejected)
- Creator and approver tracking

#### Categories
- Tool categorization
- Icons and color coding

#### Activities
- Comprehensive audit log
- User actions tracking
- IP and user agent logging

### Relationships

```
User ──< Activities
User ──< AI Tools (creator)
User ──< AI Tools (approver)
Role ──< Users
Role >──< AI Tools (many-to-many)
Category >──< AI Tools (many-to-many)
```

## 🔌 API Documentation

### Authentication

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": {
      "name": "owner",
      "display_name": "Owner"
    }
  }
}
```

#### Verify 2FA
```http
POST /api/verify-2fa
Authorization: Bearer {temp-token}
Content-Type: application/json

{
  "code": "123456"
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

### AI Tools Management

#### List All Tools
```http
GET /api/ai-tools
Authorization: Bearer {token}
```

**Query Parameters:**
- `status`: Filter by status (pending, approved, rejected)
- `category_id`: Filter by category
- `role_id`: Filter by role

#### Get Single Tool
```http
GET /api/ai-tools/{id}
Authorization: Bearer {token}
```

#### Create Tool
```http
POST /api/ai-tools
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "ChatGPT",
  "description": "AI language model",
  "url": "https://chat.openai.com",
  "documentation_url": "https://platform.openai.com/docs",
  "video_url": "https://youtube.com/...",
  "difficulty_level": "beginner",
  "logo_url": "https://...",
  "is_free": false,
  "price": 20.00,
  "category_ids": [1, 2],
  "role_ids": [1, 2]
}
```

#### Update Tool
```http
PUT /api/ai-tools/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Name",
  "description": "Updated description"
}
```

#### Delete Tool
```http
DELETE /api/ai-tools/{id}
Authorization: Bearer {token}
```

### Admin Operations (Owner Only)

#### Get Pending Tools
```http
GET /api/admin/tools/pending
Authorization: Bearer {token}
```

#### Approve Tool
```http
POST /api/admin/tools/{id}/approve
Authorization: Bearer {token}
```

#### Reject Tool
```http
POST /api/admin/tools/{id}/reject
Authorization: Bearer {token}
Content-Type: application/json

{
  "reason": "Does not meet quality standards"
}
```

#### Bulk Approve
```http
POST /api/admin/tools/bulk-approve
Authorization: Bearer {token}
Content-Type: application/json

{
  "tool_ids": [1, 2, 3]
}
```

#### Bulk Reject
```http
POST /api/admin/tools/bulk-reject
Authorization: Bearer {token}
Content-Type: application/json

{
  "tool_ids": [4, 5, 6],
  "reason": "Not relevant to platform"
}
```

### Activity Logs

#### Get All Activities (Owner Only)
```http
GET /api/activities
Authorization: Bearer {token}
```

**Query Parameters:**
- `action`: Filter by action type
- `user_id`: Filter by user
- `start_date`: Start date (Y-m-d)
- `end_date`: End date (Y-m-d)

#### Get Activity Stats (Owner Only)
```http
GET /api/activities/stats
Authorization: Bearer {token}
```

#### Get My Activities
```http
GET /api/my-activities
Authorization: Bearer {token}
```

### Two-Factor Authentication

#### Get 2FA Status
```http
GET /api/2fa/status
Authorization: Bearer {token}
```

#### Enable 2FA
```http
POST /api/2fa/enable
Authorization: Bearer {token}
```

**Response:**
```json
{
  "qr_code": "data:image/png;base64,...",
  "secret": "BASE32SECRET",
  "backup_codes": ["code1", "code2", ...]
}
```

#### Confirm 2FA
```http
POST /api/2fa/confirm
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "123456"
}
```

#### Disable 2FA
```http
POST /api/2fa/disable
Authorization: Bearer {token}
Content-Type: application/json

{
  "password": "user_password"
}
```

#### Regenerate Backup Codes
```http
POST /api/2fa/backup-codes
Authorization: Bearer {token}
```

### Categories & Roles

#### Get All Categories
```http
GET /api/categories
Authorization: Bearer {token}
```

#### Get All Roles
```http
GET /api/roles
Authorization: Bearer {token}
```

## 🔐 Security Features

### Authentication Layers

1. **Sanctum Token Authentication**: All API endpoints require valid bearer token
2. **Two-Factor Authentication**: Optional 2FA with TOTP and backup codes
3. **Password Hashing**: Bcrypt with Laravel's default settings

### Authorization

1. **Role-Based Access Control**:
   - Owner: Full system access
   - Frontend/Backend: Limited role-specific access

2. **Resource Ownership**:
   - Users can only modify their own AI tools
   - Middleware: `resource.owner`

3. **Ability-Based Tokens**:
   - Temporary tokens for 2FA verification
   - Restricted abilities for specific operations

### Activity Logging

All sensitive operations are logged:
- User login/logout
- Tool creation/update/deletion
- Approval/rejection actions
- 2FA enable/disable
- IP address and user agent tracking

### Best Practices

- CSRF protection enabled
- SQL injection prevention via Eloquent ORM
- XSS protection through Laravel escaping
- Encrypted backup codes storage
- Secure password reset tokens

## 💡 Usage Examples

### Creating an AI Tool

```php
use App\Models\AiTool;
use Illuminate\Support\Facades\Auth;

$tool = AiTool::create([
    'name' => 'Midjourney',
    'description' => 'AI image generation tool',
    'url' => 'https://midjourney.com',
    'is_free' => false,
    'price' => 10.00,
    'difficulty_level' => 'intermediate',
    'created_by' => Auth::id(),
    'status' => 'pending'
]);

// Attach categories
$tool->categories()->attach([1, 2]);

// Attach roles
$tool->roles()->attach([1]);
```

### Logging Activities

```php
use App\Services\ActivityLogger;

// Log custom action
ActivityLogger::log(
    action: 'custom_action',
    modelType: AiTool::class,
    modelId: $tool->id,
    description: 'Performed custom action',
    properties: ['key' => 'value']
);

// Log predefined actions
ActivityLogger::logCreated($model);
ActivityLogger::logUpdated($model, $oldValues);
ActivityLogger::logDeleted($model);
ActivityLogger::logApproved($tool);
ActivityLogger::logRejected($tool, 'reason');
```

### Querying with Scopes

```php
// Get approved tools
$approvedTools = AiTool::approved()->get();

// Get pending tools
$pendingTools = AiTool::pending()->get();

// Get rejected tools
$rejectedTools = AiTool::rejected()->get();

// Get tools by status
$tools = AiTool::status('approved')->get();

// Get activities by user
$activities = Activity::byUser($userId)->get();

// Get activities in date range
$activities = Activity::dateRange($startDate, $endDate)->get();
```

### Using Observer for Cache

The `AiToolObserver` automatically clears cache when tools are modified:

```php
use Illuminate\Support\Facades\Cache;

// Cache will be automatically cleared on save/delete
$categories = Cache::remember('categories_with_tools', 3600, function () {
    return Category::with('aiTools')->get();
});
```

## 🤖 AI Development Agents

This project is optimized for development with AI coding assistants. Below are starter prompts and guidelines for working with AI agents like Cursor, GitHub Copilot, or ChatGPT.

### 🎯 Quick Start Prompts

#### For New AI Agent Session

When starting a new AI agent session, use this comprehensive prompt to give context about the project:

```
This is a Laravel 12 AI Tools Platform with the following architecture:

**Tech Stack:**
- Laravel 12.0, PHP 8.2+
- Laravel Sanctum authentication with 2FA
- SQLite database (easily switchable)
- Tailwind CSS 4.0 + Vite 7.0
- Role-based access control (Owner, Frontend, Backend)

**Key Features:**
- AI Tools management with approval workflow (pending/approved/rejected)
- Two-factor authentication with Google Authenticator
- Comprehensive activity logging system
- Category and Role associations (many-to-many)
- Resource ownership permissions
- Bulk operations for admin users

**Project Structure:**
- Models: User, AiTool, Category, Role, Activity
- Controllers: app/Http/Controllers/Api/
- Middleware: CheckOwner, CheckResourceOwner, CheckRole
- Services: ActivityLogger
- Observers: AiToolObserver (cache management)

**Important Conventions:**
- Use Bulgarian language for status labels and user-facing messages
- Follow Laravel best practices and PSR-12 standards
- All API endpoints require authentication except login
- Activity logging is automatic via observers and service class

Please help me with [describe your specific task].
```

#### For Feature Development

```
I need to add a new feature to the AI Tools Platform:

**Feature:** [Describe feature]

**Requirements:**
- Follow existing patterns in app/Http/Controllers/Api/
- Add proper authorization using existing middleware
- Log all actions using ActivityLogger service
- Update API routes in routes/api.php
- Maintain consistency with current codebase style

**Existing Similar Implementation:**
[Point to similar feature in codebase]

Please provide implementation following the project's architecture.
```

#### For Bug Fixes

```
I found a bug in the AI Tools Platform:

**Issue:** [Describe the bug]
**Expected Behavior:** [What should happen]
**Actual Behavior:** [What actually happens]
**Affected Components:** [Models/Controllers/Routes]

**Context:**
- Laravel 12 with Sanctum authentication
- Role-based permissions (Owner, Frontend, Backend)
- Activity logging is enabled

Please analyze and fix while maintaining:
1. Existing security patterns
2. Activity logging
3. Code style consistency
```

#### For API Endpoint Creation

```
Create a new API endpoint for the AI Tools Platform:

**Endpoint:** [METHOD] /api/[route]
**Purpose:** [Describe functionality]
**Authentication:** Required/Not Required
**Authorization:** Owner only / Authenticated / Resource Owner
**Activity Logging:** Yes/No - [action type]

**Request Body Example:**
```json
{
  "field": "value"
}
```

**Response Example:**
```json
{
  "data": {}
}
```

Follow these project patterns:
- Controller location: app/Http/Controllers/Api/
- Use existing middleware: auth:sanctum, owner, resource.owner, role
- Log activity via ActivityLogger service
- Return consistent JSON responses
```

#### For Database Changes

```
I need to modify the database schema:

**Change:** [Describe migration]
**Affected Models:** [List models]

**Current Schema Context:**
- Users (with 2FA fields)
- AI Tools (with approval workflow)
- Categories and Roles (many-to-many with tools)
- Activities (comprehensive logging)

**Requirements:**
- Create migration following Laravel conventions
- Update model $fillable and $casts
- Add relationships if needed
- Update seeders if necessary
- Maintain data integrity

Please provide:
1. Migration file
2. Model updates
3. Seeder updates (if needed)
```

### 🔧 Development Guidelines for AI Agents

#### Code Style & Standards

```
When writing code for this project:

✅ DO:
- Follow PSR-12 coding standards
- Use type hints for parameters and return types
- Add DocBlocks for complex methods
- Use Eloquent relationships over raw queries
- Implement proper error handling
- Log activities for user actions
- Use existing middleware patterns
- Follow RESTful API conventions

❌ DON'T:
- Create duplicate functionality
- Skip authorization checks
- Ignore activity logging
- Use raw SQL queries
- Hardcode values that should be configurable
- Break existing API contracts
```

#### Security Checklist

```
For any security-related changes, ensure:

- [ ] All routes use auth:sanctum middleware
- [ ] Proper authorization checks (owner/resource.owner/role)
- [ ] Input validation on all user data
- [ ] Activity logging for sensitive operations
- [ ] Encrypted sensitive data (e.g., 2FA secrets)
- [ ] CSRF protection enabled
- [ ] SQL injection prevention via Eloquent
- [ ] XSS protection through proper escaping
```

#### Testing Prompts

```
Create tests for [feature/component]:

**Test Type:** Unit / Feature / Integration
**Component:** [Controller/Model/Service]

**Test Coverage Needed:**
- Happy path scenarios
- Error handling
- Authorization checks
- Validation rules
- Edge cases

Use PHPUnit 11.5 conventions and Laravel's testing helpers.
Follow existing test patterns in tests/Feature/ and tests/Unit/.
```

### 📚 Common AI Agent Tasks

#### 1. Adding a New Model

```
Create a new model for [entity name]:

**Fields:**
- [field_name]: [type] - [description]

**Relationships:**
- [relationship_type] with [Model]

**Features Needed:**
- Slug auto-generation (if applicable)
- Activity logging via observer
- Proper validation rules
- Scopes for common queries

Follow patterns from existing models: AiTool, Category, Role
```

#### 2. Creating a Controller

```
Create a CRUD controller for [resource]:

**Base Requirements:**
- Extend Controller
- Use Resource/Collection for responses
- Implement: index, show, store, update, destroy
- Add proper authorization
- Use Form Requests for validation
- Log all modifications via ActivityLogger

**Authorization Rules:**
- [List specific rules]

Reference: app/Http/Controllers/Api/AiToolController.php
```

#### 3. Adding Middleware

```
Create middleware for [purpose]:

**Logic:** [Describe authorization logic]
**Should Check:** [What to validate]
**Error Response:** [What to return on failure]

**Integration:**
- Register in app/Http/Kernel.php
- Add route examples in routes/api.php
- Follow patterns from: CheckOwner, CheckResourceOwner, CheckRole
```

#### 4. Implementing Observer

```
Create observer for [Model]:

**Events to Handle:**
- creating / created
- updating / updated
- deleting / deleted

**Actions:**
- Activity logging
- Cache invalidation
- [Other side effects]

Register in AppServiceProvider::boot()
Reference: app/Observers/AiToolObserver.php
```

### 🚀 Quick Reference Commands

#### Development Workflow

```bash
# Start development environment
composer run dev

# Run migrations and seed
php artisan migrate:fresh --seed

# Clear caches
php artisan optimize:clear

# Run tests
composer run test

# Fix code style
./vendor/bin/pint
```

#### API Testing

```bash
# Login and get token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Use token in subsequent requests
curl -X GET http://localhost:8000/api/ai-tools \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 📖 Architecture Patterns

#### Service Layer Pattern

```php
// Services handle complex business logic
// Example: app/Services/ActivityLogger.php

ActivityLogger::log(
    action: 'custom_action',
    modelType: AiTool::class,
    modelId: $tool->id,
    description: 'Description',
    properties: ['key' => 'value']
);
```

#### Observer Pattern

```php
// Observers handle model lifecycle events
// Auto-registered in AppServiceProvider

protected static function boot()
{
    parent::boot();
    
    static::creating(function ($model) {
        // Logic before creation
    });
}
```

#### Middleware Pattern

```php
// Middleware for authorization and validation
// Registered and used in routes

Route::middleware(['auth:sanctum', 'owner'])->group(function () {
    // Protected routes
});
```

### 🎨 UI/UX Guidelines

When building frontend features:

```
**Design Principles:**
- Use Tailwind CSS utility classes
- Follow mobile-first responsive design
- Implement loading states
- Show proper error messages
- Use status colors consistently:
  - Green: approved/success
  - Yellow: pending/warning  
  - Red: rejected/error
  - Gray: neutral/inactive

**Status Labels (Bulgarian):**
- pending → "Чака одобрение"
- approved → "Одобрен"
- rejected → "Отказан"
```

### 🔍 Debugging Prompts

```
Help me debug this issue in the AI Tools Platform:

**Error Message:** [Paste error]
**Stack Trace:** [Paste trace if available]
**Route/Endpoint:** [Which API endpoint]
**User Role:** [owner/frontend/backend]
**Expected vs Actual:** [Describe]

**Recent Changes:** [What was modified]

Please analyze considering:
- Laravel 12 behavior
- Sanctum authentication flow
- Role-based permissions
- Activity logging side effects
```

### 💡 Best Practices for AI-Assisted Development

1. **Always provide context** - Share relevant model relationships and business logic
2. **Reference existing code** - Point to similar implementations in the codebase
3. **Specify constraints** - Mention authorization rules, validation needs, logging requirements
4. **Request tests** - Ask for test coverage along with feature implementation
5. **Follow patterns** - Maintain consistency with existing architectural decisions
6. **Validate output** - Review generated code for security and performance implications

### 📋 Pre-Development Checklist

Before asking AI agent to implement a feature:

- [ ] Have I explained the business logic clearly?
- [ ] Did I mention which models/controllers are affected?
- [ ] Have I specified authorization requirements?
- [ ] Did I indicate if activity logging is needed?
- [ ] Have I provided example request/response formats?
- [ ] Did I mention any edge cases to handle?
- [ ] Have I referenced similar existing code?

---

**Pro Tip:** Keep this README open in your AI agent's context window for better, more consistent code generation that follows project conventions.

## 🧪 Testing

### Run Tests

```bash
# Run all tests
composer run test

# Or manually
php artisan test

# Run specific test
php artisan test --filter=ExampleTest

# With coverage
php artisan test --coverage
```

### Test Structure

```
tests/
├── Feature/          # Integration tests
│   └── ExampleTest.php
└── Unit/            # Unit tests
    └── ExampleTest.php
```

## 🔧 Development

### Code Style

This project uses Laravel Pint for code formatting:

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

### Queue Workers

The platform uses Laravel queues for async operations:

```bash
# Development
php artisan queue:work

# Production (with supervisor)
php artisan queue:work --tries=3 --timeout=60
```

### Log Viewing

Use Laravel Pail for real-time log viewing:

```bash
php artisan pail
```

### Database Operations

```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Reset database
php artisan migrate:reset
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan optimize
```

## 📁 Project Structure

```
platform-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/          # API Controllers
│   │   └── Middleware/       # Custom Middleware
│   ├── Models/               # Eloquent Models
│   ├── Observers/            # Model Observers
│   ├── Services/             # Business Logic
│   └── Providers/            # Service Providers
├── database/
│   ├── migrations/           # Database Migrations
│   └── seeders/              # Database Seeders
├── routes/
│   ├── api.php              # API Routes
│   └── web.php              # Web Routes
├── tests/                   # Test Suite
└── resources/
    ├── css/                 # Styles
    ├── js/                  # JavaScript
    └── views/               # Blade Templates
```

## 🚀 Deployment

### Production Setup

1. **Set Environment**

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

2. **Optimize Application**

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Build Assets**

```bash
npm run build
```

4. **Set Permissions**

```bash
chmod -R 755 storage bootstrap/cache
```

5. **Configure Web Server**

Point document root to `/public` directory

### Queue Configuration

Use Supervisor for production queue workers:

```ini
[program:platform-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- Development Team - Initial work

## 🙏 Acknowledgments

- Laravel Framework
- Laravel Sanctum for authentication
- Google2FA for two-factor authentication
- Tailwind CSS for styling
- All contributors and supporters

## 📞 Support

For support and questions:
- Open an issue on GitHub
- Contact: support@example.com

---

**Built with ❤️ using Laravel 12**
