# 🚀 Deployment Guide — Deploy ke Production

Panduan untuk deploy Tripmo ke production server.

---

## 📋 Pre-Deployment Checklist

- [ ] Semua tests passing: `php artisan test`
- [ ] Code sudah di-review
- [ ] Database backup ready
- [ ] Environment variables sudah dikonfigurasi
- [ ] SSL certificate ready
- [ ] Domain sudah pointing ke server

---

## 🔧 Server Requirements

| Requirement | Version | Note |
|---|---|---|
| PHP | 8.0+ | Laravel 11 minimum |
| MySQL | 5.7+ | Aiven MySQL support |
| Node.js | 16+ | For build assets |
| Composer | Latest | Dependency manager |
| Git | Latest | For deployment |

---

## 📝 Step-by-Step Deployment

### 1️⃣ Prepare Server

```bash
# SSH ke server
ssh user@production-server.com

# Clone repository
git clone <REPO_URL> /var/www/tripmo
cd /var/www/tripmo

# Set proper permissions
sudo chown -R www-data:www-data /var/www/tripmo
sudo chmod -R 755 /var/www/tripmo
sudo chmod -R 775 /var/www/tripmo/storage
sudo chmod -R 775 /var/www/tripmo/bootstrap/cache
```

### 2️⃣ Install Dependencies

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Frontend dependencies
npm install
npm run build
```

### 3️⃣ Configure Environment

```bash
# Copy dan edit .env untuk production
cp .env.example .env

# Edit dengan production values
nano .env

# Penting:
# - APP_ENV=production
# - APP_DEBUG=false
# - APP_KEY=<generate baru>
# - DB_HOST, DB_USERNAME, DB_PASSWORD (Aiven)
```

Generate production APP_KEY:

```bash
php artisan key:generate
```

### 4️⃣ Setup Database

```bash
# Run migrations
php artisan migrate --force

# Seed initial data (jika perlu)
php artisan db:seed --class=DatabaseSeeder
```

### 5️⃣ Setup Storage & Cache

```bash
# Create storage symlink
php artisan storage:link

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:cache
php artisan view:cache
```

### 6️⃣ Configure Web Server

**Nginx config** (`/etc/nginx/sites-available/tripmo`):

```nginx
server {
    listen 80;
    listen [::]:80;
    
    server_name your-domain.com www.your-domain.com;
    root /var/www/tripmo/public;
    
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/tripmo /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7️⃣ Setup SSL (HTTPS)

Gunakan Certbot untuk Let's Encrypt:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### 8️⃣ Setup Queue Worker (jika diperlukan)

```bash
# Buat systemd service untuk queue worker
sudo nano /etc/systemd/system/tripmo-queue.service

[Unit]
Description=Tripmo Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/tripmo
ExecStart=/usr/bin/php artisan queue:work --timeout=90

[Install]
WantedBy=multi-user.target

# Enable & start
sudo systemctl enable tripmo-queue
sudo systemctl start tripmo-queue
```

### 9️⃣ Setup Supervisor (untuk queue)

```bash
# Install supervisor
sudo apt install supervisor

# Create config
sudo nano /etc/supervisor/conf.d/tripmo.conf

[program:tripmo-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/tripmo/artisan queue:work sqs --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/tripmo/storage/logs/queue-worker.log

# Restart supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tripmo-queue-worker:*
```

### 🔟 Verify Deployment

```bash
# Test application
curl https://your-domain.com

# Check logs
tail -f /var/www/tripmo/storage/logs/laravel.log

# Test database
php artisan db:show
```

---

## 📊 Environment Variables untuk Production

```env
APP_NAME=Tripmo
APP_ENV=production
APP_KEY=<GENERATE_VIA_php_artisan_key:generate>
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Aiven
DB_CONNECTION=mysql
DB_HOST=mysql-3958c3e0-nabilarosdika97-3270.c.aivencloud.com
DB_PORT=23819
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=your_aiven_password

# Session
SESSION_DRIVER=database
SESSION_DOMAIN=.your-domain.com
SESSION_SECURE_COOKIES=true

# Cache
CACHE_STORE=database

# Mail (opsional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=<your-username>
MAIL_PASSWORD=<your-password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
```

---

## 🔄 Continuous Deployment (CI/CD)

### GitHub Actions Example

`.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /var/www/tripmo
            git pull origin main
            composer install --no-dev
            npm install && npm run build
            php artisan migrate --force
            php artisan cache:clear
```

---

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false` ✅
- [ ] `.env` file permissions correct (644)
- [ ] `storage/` writable by web server
- [ ] SSL certificate installed & enabled
- [ ] Database password strong (20+ chars)
- [ ] Regular backups scheduled
- [ ] Error logs not publicly accessible
- [ ] CORS configured properly

---

## 📈 Performance Optimization

```bash
# Cache routes
php artisan route:cache

# Cache configuration
php artisan config:cache

# Optimize autoloader
composer dump-autoload --optimize

# Cache views
php artisan view:cache

# Check performance
php artisan debug:routes
php artisan optimize
```

---

## 🆘 Rollback

Jika ada masalah saat deployment:

```bash
cd /var/www/tripmo

# Lihat commit terbaru
git log --oneline -5

# Kembali ke commit sebelumnya
git reset --hard <commit-hash>

# Atau gunakan:
git revert <commit-hash>

# Run migrations jika ada rollback
php artisan migrate:rollback
```

---

## 📞 Monitoring

Monitor production dengan:

```bash
# Real-time logs
tail -f /var/www/tripmo/storage/logs/laravel.log

# Database status
php artisan db:show

# Queue status
php artisan queue:failed
php artisan queue:retry all

# Application health
curl https://your-domain.com/health
```

---

## 🎯 Post-Deployment

1. Test semua fitur di production
2. Monitor error logs
3. Check database performance
4. Setup automated backups
5. Configure monitoring alerts
6. Document any custom setup

---

**Deployment complete!** 🚀
