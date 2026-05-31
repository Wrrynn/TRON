# 🔧 Setup Guide — Panduan Lengkap untuk Developer

Panduan ini membantu developer baru setup Tripmo dengan cepat tanpa perlu banyak konfigurasi manual.

---

## ⚡ Quick Start (5 Menit)

```bash
# 1. Clone & masuk folder
git clone <repo-url>
cd Tripmo

# 2. Install dependencies
composer install && npm install

# 3. Copy .env (sudah terisi Aiven config)
cp .env.example .env

# 4. Setup storage
php artisan storage:link

# 5. Run migrations
php artisan migrate --force

# 6. Start server
php artisan serve
```

Akses: **http://localhost:8000**

Login: `aya@gmail.com`

---

## 📋 Detailed Setup

### Prerequisite
- [Git](https://git-scm.com)
- [PHP 8.0+](https://www.php.net)
- [Composer](https://getcomposer.org)
- [Node.js 16+](https://nodejs.org)
- Internet connection (untuk Aiven DB)

### Step-by-Step

#### 1️⃣ Clone Repository
```bash
git clone <REPO_URL>
cd Tripmo
```

#### 2️⃣ Install PHP Dependencies
```bash
composer install
```

Tunggu sampai selesai. Ini download ~200MB Laravel framework & packages.

#### 3️⃣ Install Frontend Dependencies
```bash
npm install
```

Download ~500MB untuk Tailwind, Vite, dan dependencies lainnya.

#### 4️⃣ Setup Environment File

`.env` berisi konfigurasi aplikasi. Copy template:

```bash
cp .env.example .env
```

✅ File `.env` sudah terisi dengan:
- **App config** (APP_NAME, APP_KEY, dll)
- **Database Aiven** (HOST, PORT, CREDENTIALS)
- **Session & Cache** (database-based)

**Tidak perlu edit apa-apa!** Semua sudah dikonfigurasi.

Jika ada error, lihat file `.env` dengan editor:
```bash
# Windows
notepad .env

# Linux/Mac
nano .env
```

#### 5️⃣ Setup Storage Symlink

Aplikasi menyimpan foto di `storage/app/public/`, tapi diakses via `public/storage/`.

Buat symlink otomatis:
```bash
php artisan storage:link
```

Output: `The [public/storage] link has been connected to [storage/app/public].`

#### 6️⃣ Run Database Migrations

Migrations membuat tabel di database Aiven:

```bash
php artisan migrate --force
```

Output:
```
Running migrations.
✓ create_users_table ... DONE
✓ create_cache_table ... DONE
✓ create_jobs_table ... DONE
✓ create_postingan_table ... DONE
✓ add_profile_columns_to_users_table ... DONE
```

#### 7️⃣ Clear Config Cache

```bash
php artisan config:clear
```

#### 8️⃣ Start Development Server

```bash
php artisan serve
```

Output:
```
Starting Laravel development server: http://127.0.0.1:8000
```

Akses: http://localhost:8000

#### 9️⃣ (Optional) Watch Frontend Changes

Di terminal baru:
```bash
npm run dev
```

Ini auto-recompile Tailwind & JS saat ada perubahan.

---

## 🔐 Login Credentials

Default user sudah ada di database:

```
Email: aya@gmail.com
Password: (ada di database, tanya tim jika lupa)
```

---

## ✅ Verify Setup

Cek apakah semua berjalan:

```bash
# 1. Check database connection
php artisan db:show

# Output:
# ┌──────────────────────────────────────┐
# │ Driver   mysql                       │
# │ Host     mysql-3958c3e0-...         │
# │ Database defaultdb                  │
# │ Tables   12                          │
# └──────────────────────────────────────┘

# 2. Check table existence
php artisan tinker
>>> DB::table('users')->count();
# Output: 1

# 3. Test file storage
php artisan storage:link
```

---

## 📂 Project Structure

```
Tripmo/
├── app/                  # Application code
│   ├── Http/
│   │   └── Controllers/  # Request handlers
│   ├── Models/           # Database models (User, Postingan, etc)
├── database/
│   └── migrations/       # Database schema changes
├── public/
│   ├── css/              # Compiled styles
│   ├── js/               # Compiled scripts
│   └── storage/          # Symlink to storage/app/public
├── resources/
│   ├── views/            # Blade templates (HTML)
│   ├── css/              # Tailwind source
│   └── js/               # Alpine.js, etc
├── routes/               # URL routes
├── storage/
│   ├── app/public/       # User uploaded files (photos)
│   └── logs/             # Application logs
├── .env                  # Environment config (⚠️ NEVER commit)
├── .env.example          # Template (safe to commit)
├── README.md             # Main documentation
└── composer.json         # PHP dependencies
```

---

## 🔄 Common Workflows

### Adding a New Feature

```bash
# 1. Create migration for new table/column
php artisan make:migration create_table_name

# 2. Edit migration file in database/migrations/
# 3. Run migration
php artisan migrate

# 4. Create model if needed
php artisan make:model ModelName

# 5. Create controller
php artisan make:controller ControllerName
```

### Pulling Latest Code

```bash
git pull origin main

# Jika ada migration baru
php artisan migrate --force

# Jika ada dependencies baru
composer install
npm install

# Clear cache
php artisan config:clear
```

### Debugging Issues

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Or use Tinker untuk quick testing
php artisan tinker
>>> DB::table('users')->get();
>>> User::all();
```

---

## ⚠️ Important Notes

### 🔒 Secrets & Credentials
- **NEVER commit `.env`** — Contains database password
- `.env` is in `.gitignore` by default ✅
- Each developer gets own copy from `.env.example`

### 🌐 Database
- **Aiven is cloud-hosted** → accessible from anywhere with internet
- **SSL encrypted** → safe for production
- **Automatic backups** → Aiven handles it
- **No local setup needed** → just copy `.env`

### 📱 Uploads
- Photos stored in `storage/app/public/`
- Accessible via `/storage/` in browser
- Symlink required: `php artisan storage:link`

### 🔧 Environment Variables
- `APP_ENV=local` for development
- `APP_ENV=production` untuk deploy
- `APP_DEBUG=true` shows errors (NEVER on production!)
- `DB_PASSWORD` contains Aiven credentials (keep secret!)

---

## 🆘 Common Issues & Solutions

### "SQLSTATE[HY000]: No such file or directory"
```bash
# .env file missing
cp .env.example .env
php artisan config:clear
```

### "No connection could be made to Aiven"
```bash
# Check internet connection
# Check .env database credentials
# Verify Aiven host is not blocked by firewall
php artisan db:show
```

### "No such file or directory" when uploading photos
```bash
# Storage symlink missing
php artisan storage:link
```

### "Migration not found"
```bash
# Migration file doesn't exist
php artisan migrate:status  # Check status
# Verify file exists in database/migrations/
```

### "Composer dependencies conflict"
```bash
composer update
# or clear cache
composer clear-cache
```

---

## 🚀 Ready to Code?

1. ✅ Setup complete
2. ✅ Database connected
3. ✅ Server running
4. 👉 **Start developing!**

Happy coding! 💻✨

---

**Need help?** Ask team lead atau check troubleshooting section di README.md
