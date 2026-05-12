<!-- © Atia Hegazy — atiaeno.com -->

<div align="center">

<img src="screenshots/cover.jpeg" alt="ShortLink PRO" width="100%" style="max-height: 400px; object-fit: cover;">

<h1>ShortLink PRO</h1>
<h3>Enterprise URL Shortener SaaS Platform</h3>
<p>High-performance link management with affiliate monetization, real-time analytics, and distributed caching</p>

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&style=for-the-badge)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue.js-3.x-4FC08D?logo=vue.js&style=for-the-badge)](https://vuejs.org)
[![PHP 8.2](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&style=for-the-badge)](https://php.net)
[![Redis](https://img.shields.io/badge/Redis-6.0+-DC382D?logo=redis&style=for-the-badge)](https://redis.io)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&style=for-the-badge)](https://mysql.com)
[![Tests](https://img.shields.io/badge/Tests-156%2B%20Passing-success?style=for-the-badge)](./tests)

</div>

---

## 📋 Table of Contents

- [Platform Overview](#-platform-overview)
- [Technical Architecture](#-technical-architecture)
- [Performance & Scalability](#-performance--scalability)
- [User Interface](#-user-interface)
- [Admin Panel](#-admin-panel)
- [API & Documentation](#-api--documentation)
- [Testing Strategy](#-testing-strategy)
- [Installation](#-installation)
- [Docker Deployment](#-docker-deployment)

---

## 🎯 Platform Overview

<div align="center">
<img src="screenshots/homepage.png" alt="Platform Homepage" width="100%" height="500" style="object-fit: cover; object-position: top;">
<br><br>
<p><strong>Production-ready URL shortening platform built for scale</strong></p>
</div>

### Core Capabilities

| Feature | Implementation | Scale |
|---------|---------------|-------|
| **URL Shortening** | Custom aliases, bulk CSV import (500 links/batch) | 10K+ links/minute |
| **Real-time Analytics** | Click tracking, geolocation, device analytics | <30s latency |
| **Affiliate System** | Tiered commissions, country-specific rates | Multi-currency |
| **SEO Indexing** | Google Indexing API, IndexNow protocol | Instant submission |
| **Multi-Domain** | Branded short domains per user | Unlimited domains |
| **Caching Layer** | Redis-backed with domain-scoped keys | Sub-millisecond |

---

## 🏗️ Technical Architecture

<div align="center">
<img src="screenshots/shortLink_digram.png" alt="System Architecture" width="85%">
</div>

### High-Availability Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              Request Flow                                        │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│   Client Request                                                                 │
│        │                                                                         │
│        ▼                                                                         │
│   ┌─────────────┐    Cache Hit?    ┌─────────────┐                             │
│   │   Nginx     │ ───────YES──────►│   Redis     │                             │
│   │   (LB)      │                  │   (Cache)   │                             │
│   └──────┬──────┘                  └─────────────┘                             │
│          │ NO                                                                   │
│          ▼                                                                       │
│   ┌─────────────┐    ┌─────────────┐    ┌─────────────┐                       │
│   │  Laravel    │───►│   MySQL     │───►│   Redis     │                       │
│   │  PHP-FPM    │    │  (Primary)  │    │  (Cache)    │                       │
│   └──────┬──────┘    └─────────────┘    └─────────────┘                       │
│          │                                                                       │
│          ▼                                                                       │
│   ┌─────────────┐    ┌─────────────┐                                            │
│   │  Queue      │───►│  Workers    │                                            │
│   │  (Redis)    │    │  (OG/SEO)   │                                            │
│   └─────────────┘    └─────────────┘                                            │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Technology Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| **Frontend** | Vue 3 + Inertia.js | SPA with server-side routing |
| **Backend** | Laravel 12 + PHP 8.2 | API + Queue processing |
| **Database** | MySQL 8.0 | Primary data store |
| **Cache** | Redis 6.0+ | Distributed caching + sessions |
| **Queue** | Redis + Supervisor | Background job processing |
| **Web Server** | Nginx | Reverse proxy + static assets |
| **Container** | Docker + Compose | Production orchestration |

---

## ⚡ Performance & Scalability

### Distributed Caching Strategy

```php
// Domain-scoped cache keys prevent collisions
$cacheKey = "{$domain}:{$shortCode}";

// Cache hit: sub-millisecond response
$cachedUrl = Redis::get($cacheKey);

// Cache miss: database lookup + cache warm
$url = Link::where('short_code', $shortCode)
    ->where('domain_id', $domainId)
    ->first();
    
Redis::setex($cacheKey, 86400, $url); // 24h TTL
```

| Metric | Without Cache | With Redis | Improvement |
|--------|--------------|------------|-------------|
| Redirect Latency | 45ms | 2ms | **22x faster** |
| Database Queries | 1 per request | 0 (hit) / 1 (miss) | **95% reduction** |
| Throughput | 500 req/s | 15,000 req/s | **30x higher** |

### Database Optimization

```sql
-- Composite indexes for frequent queries
CREATE INDEX idx_links_domain_code ON links(domain_id, short_code);
CREATE INDEX idx_clicks_link_date ON clicks(link_id, created_at);
CREATE INDEX idx_analytics_daily ON link_analytics_daily(link_id, date);

-- Daily aggregation table prevents N+1 on analytics
INSERT INTO link_analytics_daily (link_id, date, total_clicks, by_device, by_country)
SELECT link_id, DATE(created_at), COUNT(*), 
       JSON_OBJECT_AGG(device, COUNT(*)),
       JSON_OBJECT_AGG(country, COUNT(*))
FROM clicks 
GROUP BY link_id, DATE(created_at);
```

### Queue Processing

| Job Type | Queue | Workers | Priority |
|----------|-------|---------|----------|
| **OG Tag Fetching** | `default` | 3 workers | Normal |
| **SEO Indexing** | `indexing` | 2 workers | Low |
| **IP Anonymization** | `gdpr` | 1 worker | High |
| **Payout Processing** | `payments` | 2 workers | Critical |

---

## 🖥️ User Interface

### Dashboard & Link Management

<table>
<tr>
<td width="33%" valign="top">
<img src="screenshots/Dashboard.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Dashboard</b><br>
<small>Overview with click stats, recent links, quick actions</small>
</td>
<td width="33%" valign="top">
<img src="screenshots/Create-Link.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Create Link</b><br>
<small>Custom aliases, password protection, expiration dates</small>
</td>
<td width="33%" valign="top">
<img src="screenshots/My-Links.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Link Management</b><br>
<small>Search, filter, bulk operations, export to CSV</small>
</td>
</tr>
</table>

### Analytics & Reporting

<table>
<tr>
<td width="50%" valign="top">
<img src="screenshots/Analytics.png" width="100%" height="300" style="object-fit: cover;">
<br><b>Global Analytics</b><br>
<small>Time-series charts, geographic distribution, device breakdown, referrer tracking</small>
</td>
<td width="50%" valign="top">
<img src="screenshots/AnalyticsPerLink.png" width="100%" height="300" style="object-fit: cover;">
<br><b>Per-Link Analytics</b><br>
<small>Individual link performance, click timeline, top referrers, browser/OS stats</small>
</td>
</tr>
</table>

### Affiliate Monetization

<table>
<tr>
<td width="50%" valign="top">
<img src="screenshots/Affiliate-Program.png" width="100%" height="300" style="object-fit: cover;">
<br><b>Affiliate Program Landing</b><br>
<small>Tier structure, commission rates, earnings calculator</small>
</td>
<td width="50%" valign="top">
<img src="screenshots/Affiliate-Dashboard.png" width="100%" height="300" style="object-fit: cover;">
<br><b>Affiliate Dashboard</b><br>
<small>Real-time earnings, visit tracking, payout requests</small>
</td>
</tr>
</table>

<p align="center">
<img src="screenshots/Affalites_digram.png" alt="Affiliate Commission Flow" width="80%">
<br>
<em>Tier-based commission system with country-specific rate overrides</em>
</p>

---

## 🎛️ Admin Panel

### Administration Dashboard

<table>
<tr>
<td width="33%" valign="top">
<img src="screenshots/Admin-Dashboard.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Admin Overview</b><br>
<small>System metrics, user stats, revenue tracking</small>
</td>
<td width="33%" valign="top">
<img src="screenshots/Admin-All-Links-.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Link Administration</b><br>
<small>Global link management, moderation, bulk actions</small>
</td>
<td width="33%" valign="top">
<img src="screenshots/Admin-Link.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Link Details</b><br>
<small>Edit links, view full analytics, manage settings</small>
</td>
</tr>
</table>

### Monetization Management

<table>
<tr>
<td width="33%" valign="top">
<img src="screenshots/Admin-Affiliate-Tiers.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Affiliate Tiers</b><br>
<small>Configure commission rates, thresholds, multipliers</small>
</td>
<td width="33%" valign="top">
<img src="screenshots/Admin-Payout-Management-.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Payout Management</b><br>
<small>Review requests, process payments, audit trail</small>
</td>
<td width="33%" valign="top">
<img src="screenshots/Admin-Advertising-Management.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Ad Management</b><br>
<small>Create ads, manage placements, track performance</small>
</td>
</tr>
</table>

### System Operations

<table>
<tr>
<td width="33%" valign="top">
<img src="screenshots/Admin-Report-Queue.png" width="100%" height="280" style="object-fit: cover;">
<br><b>Report Queue</b><br>
<small>User reports, abuse handling, moderation workflow</small>
</td>
<td width="33%" valign="top">
<img src="screenshots/Admin-SEO-Indexer.png" width="100%" height="280" style="object-fit: cover;">
<br><b>SEO Indexer</b><br>
<small>Google Indexing API, IndexNow, sitemap management</small>
</td>
<td width="33%" valign="top">
<img src="screenshots/Admin-Settings.png" width="100%" height="280" style="object-fit: cover;">
<br><b>System Settings</b><br>
<small>Platform configuration, rate limits, feature toggles</small>
</td>
</tr>
</table>

---

## 🔌 API & Documentation

<div align="center">
<img src="screenshots/API-Documentation.png" alt="API Documentation" width="85%" height="400" style="object-fit: cover;">
<br><br>
</div>

### REST API Endpoints

| Method | Endpoint | Auth | Rate Limit | Description |
|--------|----------|------|------------|-------------|
| `POST` | `/api/v1/auth/register` | No | 10/min | User registration |
| `POST` | `/api/v1/auth/login` | No | 10/min | Bearer token authentication |
| `GET` | `/api/v1/links` | Bearer | 1000/hr | List user links (paginated) |
| `POST` | `/api/v1/links` | Bearer | 100/hr | Create single or bulk links |
| `GET` | `/api/v1/links/{id}` | Bearer | 1000/hr | Link details with analytics |
| `PATCH` | `/api/v1/links/{id}` | Bearer | 100/hr | Update link configuration |
| `GET` | `/api/v1/analytics` | Bearer | 500/hr | Global analytics summary |
| `GET` | `/api/v1/links/{id}/analytics` | Bearer | 500/hr | Per-link detailed analytics |
| `POST` | `/api/v1/webhooks` | Bearer | 50/hr | Configure webhook endpoints |

### Authentication Example

```bash
# Obtain Bearer token
curl -X POST https://api.shortlink.pro/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"secret"}'

# Create link with token
curl -X POST https://api.shortlink.pro/api/v1/links \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/product",
    "alias": "summer-sale",
    "domain_id": 1,
    "campaign_tag": "newsletter-june"
  }'
```

---

## 🧪 Testing Strategy

### Test Coverage: 156+ Tests | 85%+ Code Coverage

```
┌────────────────────────────────────────────────────────────────┐
│                    Test Architecture                            │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  Unit Tests (45)           Feature Tests (98)                 │
│  ├── Models (12)           ├── API Endpoints (35)            │
│  ├── Services (18)         ├── Controllers (28)               │
│  ├── Policies (8)          ├── Authentication (20)            │
│  └── Helpers (7)           └── Integration (15)             │
│                                                                │
│  Integration Tests (13)                                      │
│  ├── Database Transactions                                   │
│  ├── Redis Cache Operations                                  │
│  ├── Queue Job Processing                                     │
│  └── Third-party APIs (Stripe, OAuth)                       │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

### Test Execution

```bash
# Run full test suite
composer run test

# Run with coverage report
php artisan test --coverage --min=85

# Run specific test categories
php artisan test --filter=Unit
php artisan test --filter=Feature\Api
php artisan test --filter=Integration

# Parallel testing for speed
php artisan test --parallel --recreate-databases
```

### Critical Test Scenarios

| Component | Test Scenario | Assertion |
|-----------|--------------|-----------|
| **Redirect** | Cache hit flow | Response < 5ms, no DB query |
| **Redirect** | Cache miss flow | DB query executes, cache warmed |
| **Analytics** | Click tracking | IP anonymized, geo data stored |
| **Affiliate** | Commission calculation | Tier rate × country rate = correct |
| **Queue** | OG tag fetching | Job queued, tag extracted, stored |
| **API** | Rate limiting | 429 returned after threshold |
| **Auth** | OAuth flow | Provider callback creates/updates user |

---

## 💻 Installation

### Prerequisites

| Requirement | Version | Verification |
|-------------|---------|--------------|
| PHP | 8.2+ | `php -v` |
| MySQL | 8.0+ | `mysql --version` |
| Redis | 6.0+ | `redis-cli ping` |
| Node.js | 18+ | `node -v` |
| Composer | 2.x | `composer --version` |

### Local Setup

```bash
# Clone repository
git clone https://github.com/atiahegazy/url-shortener.git
cd url-shortener

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Environment configuration
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_DATABASE=shortlink_pro
DB_USERNAME=root
DB_PASSWORD=your_secure_password

# Run migrations with seeders
php artisan migrate --seed

# Build assets
npm run build

# Start services
php artisan serve
php artisan queue:work --queue=default,indexing,payments
```

---

## 🐳 Docker Deployment

### Production-Ready Stack

```yaml
# docker-compose.yml services:
# - app: PHP-FPM + Laravel (replicas: 3)
# - nginx: Load balancer + SSL termination
# - mysql: Primary database (volume mounted)
# - redis: Cache + Queue + Sessions
# - supervisor: Queue workers (multi-queue)
```

```bash
# Production deployment
docker-compose up -d --scale app=3
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan optimize

# Verify services
docker-compose ps
docker-compose logs -f app
```

### Scaling Configuration

| Service | Replicas | Resources | Scaling Trigger |
|---------|----------|-----------|-----------------|
| `app` | 3-10 | 512MB RAM / 0.5 CPU | CPU > 70% |
| `nginx` | 2 | 256MB RAM / 0.25 CPU | Connections > 1000 |
| `mysql` | 1 (master) | 2GB RAM / 1 CPU | Query time > 100ms |
| `redis` | 1 | 512MB RAM | Memory usage > 80% |
| `supervisor` | 1 | 1GB RAM / 0.5 CPU | Queue depth > 1000 |

---

## 📊 Performance Benchmarks

| Scenario | RPS | Latency (p95) | Error Rate |
|----------|-----|---------------|------------|
| **Cache Hit Redirect** | 15,000 | 2ms | 0.01% |
| **Cache Miss Redirect** | 2,000 | 45ms | 0.05% |
| **Link Creation** | 500 | 120ms | 0.1% |
| **Analytics Query** | 1,000 | 80ms | 0.02% |
| **API Bulk Create** | 100 | 800ms | 0.1% |

*Benchmarked on: 4 vCPU / 8GB RAM / SSD storage*

---

## 🔐 Security Implementation

| Layer | Implementation |
|-------|----------------|
| **Authentication** | Laravel Sanctum (stateful + token-based), OAuth 2.0 providers |
| **Authorization** | Policy-based access control, role middleware |
| **Input Validation** | Form Request classes, custom validators for URLs |
| **Rate Limiting** | Per-route configuration: IP-based + user-based tiers |
| **Data Protection** | IP anonymization (GDPR), AES-256 encryption for sensitive data |
| **Audit Logging** | ActivityLog model tracks all mutations with IP + user agent |

---

## 📁 Project Structure

```
url-shortener/
├── app/
│   ├── Console/Commands/       # Artisan commands (cleanup, reports)
│   ├── Http/
│   │   ├── Controllers/        # API + Web controllers
│   │   ├── Middleware/         # Rate limiting, auth, security
│   │   └── Requests/           # Form request validation
│   ├── Jobs/                   # Queueable jobs (OG fetch, SEO, GDPR)
│   ├── Models/                 # Eloquent + relations + scopes
│   ├── Policies/               # Authorization policies
│   ├── Services/               # Business logic encapsulation
│   └── Providers/              # Service providers
├── config/                     # App configuration
├── database/
│   ├── factories/              # Model factories for testing
│   ├── migrations/             # Schema versions (50+ tables)
│   └── seeders/                # Test + production seeders
├── docker/                     # Container configs
├── docs/                       # API docs (HTML + Markdown)
├── public/                     # Web root
├── resources/
│   ├── js/                     # Vue 3 + Inertia SPA
│   │   ├── Components/         # Reusable UI components
│   │   ├── Composables/        # Shared logic (useAuth, useLinks)
│   │   ├── Layouts/            # Page layouts
│   │   └── Pages/              # Route components
│   ├── css/                    # Tailwind + custom styles
│   └── views/                  # Blade entry point
├── routes/
│   ├── api.php                 # REST API routes (v1)
│   ├── web.php                 # Web routes
│   └── console.php           # Artisan commands
├── tests/
│   ├── Feature/                # End-to-end API tests
│   ├── Unit/                   # Isolated component tests
│   └── TestCase.php            # Base test class
└── screenshots/                # Documentation images
```

---

<div align="center">

<img src="screenshots/cta.png" alt="Get Started" width="100%">

**[View Live Demo](https://shortlink.pro)** • **[API Docs](https://docs.shortlink.pro)** • **[Report Issue](https://github.com/atiahegazy/url-shortener/issues)**

---

Built with precision by [Atia Hegazy](https://atiaeno.com)

*Senior Full-Stack Engineer | Laravel & Vue.js Specialist*

</div>
