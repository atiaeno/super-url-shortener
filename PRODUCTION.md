# Production Deployment Guide

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- VPS with at least 2GB RAM
- Domain name (optional)

### One-Command Deployment

```bash
# Clone the repository
git clone <your-repo-url>
cd url-shortener

# Copy environment file
cp .env.example .env

# Edit .env with your production settings
nano .env

# Deploy!
chmod +x deploy.sh
./deploy.sh
```

## 🔧 Security Enhancements Implemented

### 1. Authentication & Authorization
- ✅ Mass assignment vulnerability fixed (role removed from User fillable)
- ✅ Secure role management with validation
- ✅ API authentication strengthened (Bearer tokens only)
- ✅ Rate limiting integrated with database settings

### 2. Input Validation & Sanitization
- ✅ URL validation service with security checks
- ✅ Malicious pattern detection
- ✅ Private IP blocking
- ✅ XSS prevention

### 3. Performance Optimizations
- ✅ Database indexes for common queries
- ✅ URL hash for duplicate detection
- ✅ Redis caching and queue system
- ✅ Query optimization

## 📊 Monitoring & Health Checks

### Health Endpoint
```
GET /api/v1/health
```

Response:
```json
{
  "status": "ok",
  "timestamp": "2026-05-11T20:00:00Z",
  "version": "1.0.0",
  "environment": "production",
  "checks": {
    "database": "ok",
    "cache": "ok",
    "queue": "ok"
  }
}
```

### Rate Limiting
- Configurable via admin panel
- API: `api_rate_limit_per_hour` (default: 100)
- Tokens: `api_token_rate_limit_per_hour` (default: 10)

## 🐳 Docker Configuration

### Services
- **app**: PHP-FPM + Nginx
- **mysql**: MySQL 8.0
- **redis**: Redis 7
- **queue-worker**: Laravel queue processor

### Volumes
- `mysql_data`: Persistent MySQL data
- `redis_data`: Persistent Redis data

### Environment Variables
Key production settings in `.env`:
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

## 🗄️ Database Setup

### Migrations
The deployment script automatically runs:
- `2026_05_11_195500_add_destination_url_hash_to_links_table`
- `2026_05_11_200000_add_performance_indexes_to_links_table`

### Indexes Added
- `(user_id, is_active, created_at)` - User link listings
- `(short_code, is_active)` - Fast redirects
- `(destination_url_hash)` - Duplicate detection
- Plus many more performance indexes

## 🔒 Security Headers

The Nginx configuration includes:
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

## 📈 Performance Features

### Caching Strategy
- **Redis**: Sessions, cache, queues
- **Application Cache**: Redirect lookups (24h TTL)
- **Static Files**: 1-year cache with immutable headers

### Queue System
- Background job processing
- OG tag fetching
- IP anonymization
- Old click cleanup

## 🛠️ Management Commands

```bash
# Check service status
docker-compose ps

# View logs
docker-compose logs -f app

# Run migrations
docker-compose exec app php artisan migrate

# Clear cache
docker-compose exec app php artisan cache:clear

# Queue worker status
docker-compose logs -f queue-worker
```

## 🔄 Maintenance

### Backup Database
```bash
docker-compose exec mysql mysqldump -u root -p shortlink_prod > backup.sql
```

### Update Application
```bash
git pull
docker-compose build
docker-compose up -d
```

### Scale Workers
```bash
# Scale queue workers
docker-compose up -d --scale queue-worker=3
```

## 📝 Post-Deployment Checklist

- [ ] Configure domain DNS
- [ ] Set up SSL certificate (Let's Encrypt)
- [ ] Configure backup strategy
- [ ] Set up monitoring alerts
- [ ] Review rate limiting settings
- [ ] Test all API endpoints
- [ ] Verify health checks
- [ ] Check error logs

## 🚨 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check MySQL container: `docker-compose logs mysql`
   - Verify credentials in `.env`

2. **Cache Errors**
   - Check Redis container: `docker-compose logs redis`
   - Verify Redis connection settings

3. **Queue Not Processing**
   - Check queue worker: `docker-compose logs queue-worker`
   - Restart worker: `docker-compose restart queue-worker`

4. **High Memory Usage**
   - Scale down workers: `docker-compose up -d --scale queue-worker=1`
   - Check MySQL configuration

## 📞 Support

For issues:
1. Check logs: `docker-compose logs`
2. Verify health: `curl http://localhost:8000/api/v1/health`
3. Review configuration: Check `.env` settings

---

## 🎯 Production Ready Features

✅ **Security**: Authentication, validation, rate limiting  
✅ **Performance**: Database indexes, caching, queues  
✅ **Monitoring**: Health checks, logging  
✅ **Deployment**: Docker, automation scripts  
✅ **Scalability**: Redis, queue workers, load balancing ready  

 