# Changelog

All notable changes to the Super URL Shortener project will be documented in this file.

## [2.0.0] - 2026-05-11

### 🚀 Production Ready Release
Major security, performance, and deployment improvements for production readiness.

### 🔒 Security Enhancements
- **Fixed Mass Assignment Vulnerability** - Removed `role` from User model fillable attributes
- **Strengthened API Authentication** - Removed insecure query parameter authentication, enforced Bearer tokens only
- **Input Validation Service** - Added comprehensive URL validation and sanitization
- **Secure Model Attributes** - Added proper role management methods to User model

### ⚡ Performance Optimizations
- **Database Indexes** - Added performance indexes on links, clicks, and users tables
- **URL Hash Column** - Added `destination_url_hash` for duplicate detection and performance
- **Redis Configuration** - Optimized for production cache, sessions, and queues
- **Query Optimization** - Enhanced database queries for better performance

### 🛡️ Rate Limiting Improvements
- **Dynamic Rate Limiting** - Configurable limits via admin panel
- **Enhanced Middleware** - Custom middleware for database-driven rate limits
- **Production Defaults** - Higher limits for production environment

### 🐳 Docker Deployment
- **Complete Docker Setup** - Production-ready Docker and Docker Compose configuration
- **Automated Deployment** - One-command deployment script (`deploy.sh`)
- **Service Configuration** - Nginx, PHP-FPM, MySQL, Redis, Supervisor
- **SSL Ready** - HTTPS configuration included

### 📊 Monitoring & Health Checks
- **Health Endpoint** - `/api/v1/health` with system status checks
- **Database Monitoring** - Connection and performance metrics
- **Cache Monitoring** - Redis connectivity and performance
- **Queue Monitoring** - Background job processing status

### 🗄️ Database Improvements
- **Production Seeders** - Minimal essential data for production
- **Migration Compatibility** - SQLite-compatible for testing, MySQL/PostgreSQL for production
- **Schema Optimization** - Added proper indexes and constraints

### 🧪 Testing & Quality
- **156 Tests Passing** - Comprehensive test coverage
- **SQLite Compatibility** - Fixed SHA2 function for testing database
- **Security Tests** - Added tests for authentication improvements
- **Integration Tests** - Enhanced API and redirect functionality tests

### 📚 Documentation Updates
- **Production Guide** - Complete deployment and configuration documentation
- **API Documentation** - Authentication and security best practices
- **Security Checklist** - Pre-deployment security verification
- **Docker Guide** - Containerized deployment instructions

### 🔧 Configuration Changes
- **Environment Variables** - Production-optimized `.env.example`
- **Cache Settings** - Production cache TTL values
- **Rate Limit Settings** - Configurable via database settings
- **Security Settings** - Disabled features by default for production

### 📦 New Files Added
- `docker-compose.yml` - Production Docker setup
- `Dockerfile` - Application container
- `deploy.sh` - Automated deployment script
- `docker/` - Configuration files
- `database/seeders/Production/` - Production seeders
- `app/Services/UrlValidationService.php` - URL validation
- `app/Http/Middleware/DynamicRateLimit.php` - Rate limiting

### ⚠️ Breaking Changes
- **API Authentication** - Query parameter authentication removed (security improvement)
- **Default Seeder** - Production seeder now used by default for deployments
- **Rate Limits** - New dynamic rate limiting system
- **Database Schema** - New `destination_url_hash` column added

### 🔄 Migration Notes
- Run migrations: `php artisan migrate`
- Use production seeders: `php artisan db:seed --class=Database\\Seeders\\Production\\DatabaseSeeder`
- Update API clients to use Bearer token authentication only

---

## [1.0.0] - Previous

### Initial Features
- Basic URL shortening functionality
- User authentication and registration
- Admin panel
- Affiliate program
- QR code generation
- Link analytics

---

## Security Advisory

### Version 2.0.0 Security Improvements
- **Critical**: Mass assignment vulnerability fixed
- **Critical**: API authentication hardened
- **High**: Input validation added
- **Medium**: Rate limiting enhanced

### Recommended Actions
1. Update to version 2.0.0 immediately
2. Change all admin passwords
3. Review API authentication methods
4. Update rate limiting configurations

---

## Support

For questions about these changes or deployment assistance:
- Check the [PRODUCTION.md](PRODUCTION.md) guide
- Review the updated [README.md](README.md)
- Run tests: `composer run test`

---

*For detailed deployment instructions, see [PRODUCTION.md](PRODUCTION.md)*
