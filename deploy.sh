#!/bin/bash
# © Atia Hegazy — atiaeno.com

# Production Deployment Script for URL Shortener

set -e

echo "🚀 Starting URL Shortener Production Deployment..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Check if .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from template..."
    cp .env.example .env
    echo "⚠️  Please edit .env file with your production settings before running this script again."
    exit 1
fi

# Create necessary directories
echo "📁 Creating storage directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Build and start containers
echo "🐳 Building Docker containers..."
docker-compose build

echo "🔄 Starting services..."
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 30

# Run database migrations
echo "🗄️ Running database migrations..."
docker-compose exec app php artisan migrate --force

# Seed database with production data
echo "🌱 Seeding database with production data..."
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Production\\DatabaseSeeder --force

# Clear and cache configurations
echo "💾 Optimizing application..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan view:cache

# Create storage link
echo "🔗 Creating storage link..."
docker-compose exec app php artisan storage:link

# Optimize composer
echo "📦 Optimizing composer..."
docker-compose exec app composer dump-autoload --optimize

# Check application health
echo "🏥 Checking application health..."
sleep 10

if curl -f http://localhost:8000/api/health > /dev/null 2>&1; then
    echo "✅ Application is healthy and running!"
else
    echo "❌ Application health check failed. Please check logs:"
    docker-compose logs app
    exit 1
fi

echo "🎉 Deployment completed successfully!"
echo "🌐 Your URL Shortener is now running at: http://localhost:8000"
echo "📊 Check status with: docker-compose ps"
echo "📋 View logs with: docker-compose logs -f"
