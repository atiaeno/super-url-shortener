<!-- © Atia Hegazy — atiaeno.com -->

<p align="center">
  <h1 align="center">Super URL Shortener</h1>
  <p align="center">A powerful URL shortener with affiliate program, QR codes, and admin panel , STATICS , ADM PANEL</p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js" alt="Vue 3">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=flat-square&logo=tailwind-css" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License MIT">
</p>

---

## Features

### Core Features
- **URL Shortening** - Create short, shareable links instantly
- **Guest Shortening** - Shorten URLs without registration (rate-limited)
- **Custom Short Codes** - Personalized memorable links
- **QR Code Generation** - Download QR codes in SVG or PNG format
- **Bulk Shortening** - Shorten multiple URLs at once with CSV export

### User Features
- **User Dashboard** - Manage all your links with analytics
- **OAuth Authentication** - Sign in with Google or GitHub
- **Link Analytics** - Track clicks, referrers, and geographic data
- **Link Management** - Edit, delete, and organize your links

### Affiliate Program
- **Tiered Commission System** - Earn based on performance levels
- **Country-Specific Rates** - Different payouts per geographic region
- **Payout Requests** - Request withdrawals when reaching thresholds
- **Real-time Stats** - Monitor clicks and earnings

### Admin Panel
- **User Management** - Manage users, roles, and bans
- **Link Moderation** - Review reported links with audit logs
- **Affiliate Tiers** - Configure commission structures
- **Payout Management** - Approve, reject, and process withdrawals
- **Ad Management** - Configure interstitial advertisements
- **System Settings** - Backup, import/export, and cache management

### Technical Features
- **Redis Caching** - High-performance caching and session storage
- **Queue System** - Background job processing for analytics
- **Dynamic Rate Limiting** - Configurable protection against abuse
- **Input Validation** - URL sanitization and security checks
- **Database Indexes** - Optimized for high-performance queries
- **Health Monitoring** - System health checks and metrics
- **SEO Ready** - Sitemap and robots.txt generation
- **Responsive Design** - Works on all devices

### Security Features
- **API Authentication** - Bearer token authentication only
- **Mass Assignment Protection** - Secure model attributes
- **URL Validation** - Prevents malicious URLs
- **Safe Browsing** - Optional security scanning
- **GDPR Compliance** - Data protection features

### Production Ready
- **Docker Deployment** - Complete containerized setup
- **Performance Optimized** - Database indexes and caching
- **Monitoring Ready** - Health checks and logging
- **Scalable Architecture** - Redis queues and load balancing ready

---

## Requirements

- PHP 8.2 or higher
- MySQL 8.0+ or MariaDB 10.6+
- Redis 6.0+
- Node.js 18+ and NPM
- Composer 2.x

---

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/shortlink.git
cd shortlink
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Environment Variables

Edit `.env` and set your database and Redis credentials:

```env
APP_NAME="ShortLink"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shortlink
DB_USERNAME=root
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Optional: OAuth Providers
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
```

### 6. Create Database

```bash
mysql -u root -p -e "CREATE DATABASE shortlink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 7. Run Migrations and Seeders

```bash
php artisan migrate
php artisan db:seed
```

### 8. Build Assets

```bash
npm run build
```

### 9. Start the Application

Development mode (with hot reload):
```bash
composer run dev
```

Or manually start services:
```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Queue worker
php artisan queue:listen

# Terminal 3 - Vite dev server
npm run dev
```

---

## Quick Setup (One Command)

If you have all requirements installed, you can use the setup script:

```bash
composer run setup
```

This will:
- Install PHP dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run migrations
- Install NPM packages
- Build assets

---

## Docker Deployment (Production)

For production deployment, use the included Docker setup:

### Quick Deploy
```bash
# One-command deployment
chmod +x deploy.sh
./deploy.sh
```

### Manual Docker Setup
```bash
# Build and start containers
docker-compose up -d

# Run migrations and seed production data
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Production\\DatabaseSeeder --force

# Optimize for production
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### Docker Services
- **app** - PHP-FPM with Laravel application
- **nginx** - Web server with SSL configuration
- **mysql** - MySQL database
- **redis** - Redis caching and queues
- **supervisor** - Process management

### Environment Variables for Production
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## Docker Setup (Development)

If you prefer Docker for development, you can use Laravel Sail:

```bash
composer require laravel/sail --dev
php artisan sail:install
./vendor/bin/sail up
```

---

## Default Admin Account

After production seeding, an admin account is created:

- **Email:** `admin@yourdomain.com` (based on your APP_URL)
- **Password:** `change-this-password-in-production`

> ⚠️ **IMPORTANT:** Change the default password immediately after first login!

### Production Seeder
The production seeder creates minimal essential data:
- Single admin account
- Essential application settings
- No test/demo data

For development with sample data, use:
```bash
php artisan db:seed --class=DatabaseSeeder
```

---

## API Documentation

### Authentication
The API uses Bearer token authentication for security:

```bash
# Correct - Bearer token in header
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/links

# Incorrect - Query parameter not supported for security
curl https://yourdomain.com/api/v1/links?api_key=YOUR_TOKEN
# Returns 401 Unauthorized
```

### Rate Limits
- **API Endpoints**: 1000 requests/hour (configurable)
- **Token Management**: 100 requests/hour (configurable)
- **Guest Shortening**: 10 requests/hour (configurable)

### Health Check
Monitor system health with:
```bash
curl https://yourdomain.com/api/v1/health
```

Response includes database, cache, and queue status.

---

## Testing

```bash
composer run test
```

**Test Coverage**: 156 tests passing ✅

---

## Security Checklist

Before deploying to production:

- [ ] Change default admin password
- [ ] Set strong APP_KEY in production
- [ ] Configure HTTPS/SSL certificates
- [ ] Set APP_DEBUG=false in production
- [ ] Configure firewall rules
- [ ] Set up monitoring and logging
- [ ] Review rate limiting settings
- [ ] Enable backup strategy

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://atiaeno.com">Atia Hegazy</a></sub>
</p>
