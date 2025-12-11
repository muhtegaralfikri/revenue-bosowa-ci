# Panduan Deploy CodeIgniter 4 di aaPanel

## Kelebihan PHP vs NestJS untuk Server Kecil

| Aspek | PHP (CodeIgniter) | NestJS |
|-------|-------------------|--------|
| Build Process | ❌ Tidak perlu build | ✅ Perlu `npm run build` (RAM intensive) |
| Memory Usage | ~50-100MB | ~200-500MB |
| Deploy | Upload langsung | Build dulu, baru upload |
| Cold Start | Instant | 5-30 detik |

**PHP tidak perlu build** - langsung upload dan jalan!

---

## Langkah 1: Persiapan Server aaPanel

### 1.1 Install Software Stack

Di aaPanel, pastikan sudah install:

1. **Nginx** (versi terbaru)
2. **PHP 8.1+** dengan extensions:
   - intl
   - mbstring
   - curl
   - json
   - mysqli
   - openssl
   - zip
3. **MariaDB 10.x** atau **MySQL 5.7+**

### 1.2 Install PHP Extensions

```
aaPanel > App Store > PHP 8.1 > Install Extensions
```

Centang dan install:
- ✅ intl
- ✅ mbstring  
- ✅ curl
- ✅ json
- ✅ mysqli
- ✅ openssl
- ✅ zip
- ✅ fileinfo

### 1.3 PHP Configuration

```
aaPanel > App Store > PHP 8.1 > Settings > Configuration
```

Edit `php.ini`:
```ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
date.timezone = Asia/Makassar
```

---

## Langkah 2: Buat Database

### 2.1 Buat Database di aaPanel

```
aaPanel > Databases > Add Database
```

- **Database Name**: `revenue_bosowa`
- **Username**: `revenue_user`
- **Password**: (generate strong password)
- **Access**: Local (localhost)

**Simpan credentials ini!**

---

## Langkah 3: Upload Project

### 3.1 Opsi A: Upload via File Manager (Recommended)

1. **Di komputer lokal**, compress project:
   ```
   Klik kanan folder revenue-bosowa-ci > Compress to ZIP
   ```
   
   **PENTING**: Hapus folder ini sebelum compress:
   - `vendor/` (akan di-install ulang di server)
   - `.git/` (tidak perlu)
   - `writable/cache/*`
   - `writable/logs/*`
   - `writable/session/*`

2. **Di aaPanel**:
   ```
   aaPanel > Files > /www/wwwroot/
   ```
   
3. **Upload** file ZIP

4. **Extract** ke folder `/www/wwwroot/revenue.yourdomain.com/`

### 3.2 Opsi B: Upload via Git (Advanced)

```bash
cd /www/wwwroot/
git clone https://github.com/username/revenue-bosowa-ci.git revenue.yourdomain.com
```

---

## Langkah 4: Install Dependencies

### 4.1 SSH ke Server

```
aaPanel > Terminal
```

atau SSH langsung:
```bash
ssh root@your-server-ip
```

### 4.2 Install Composer Dependencies

```bash
cd /www/wwwroot/revenue.yourdomain.com

# Install composer jika belum ada
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install dependencies (tanpa dev dependencies untuk production)
composer install --no-dev --optimize-autoloader
```

**Jika memory error**, jalankan:
```bash
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader
```

---

## Langkah 5: Konfigurasi Environment

### 5.1 Copy dan Edit .env

```bash
cd /www/wwwroot/revenue.yourdomain.com

# Copy template
cp env.example .env

# Edit dengan nano atau vi
nano .env
```

### 5.2 Isi .env untuk Production

```env
#--------------------------------------------------------------------
# ENVIRONMENT - PENTING!
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://revenue.yourdomain.com/'
app.indexPage = ''
app.appTimezone = 'Asia/Makassar'

#--------------------------------------------------------------------
# DATABASE - Sesuaikan dengan yang dibuat di Langkah 2
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = revenue_bosowa
database.default.username = revenue_user
database.default.password = PASSWORD_DARI_LANGKAH_2
database.default.DBDriver = MySQLi
database.default.DBPrefix = ci_
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

#--------------------------------------------------------------------
# ENCRYPTION - Generate key baru!
#--------------------------------------------------------------------
# Jalankan: php spark key:generate
encryption.key = 

#--------------------------------------------------------------------
# GOOGLE SHEETS
#--------------------------------------------------------------------
google.sheets.enabled = true
google.sheets.credentials_file = '/www/wwwroot/revenue.yourdomain.com/google-credentials.json'
google.spreadsheet.id = '1Xf9tR8HodZOYhXWkNr41NDEMfCtBfCMi'
google.sheets.sync_interval = 60

#--------------------------------------------------------------------
# CRON - Generate key baru!
#--------------------------------------------------------------------
# Jalankan: openssl rand -hex 32
CRON_SECRET_KEY = GENERATE_KEY_BARU
```

### 5.3 Generate Encryption Key

```bash
php spark key:generate
```

### 5.4 Generate Cron Secret Key

```bash
openssl rand -hex 32
```

Copy output dan paste ke `CRON_SECRET_KEY` di `.env`

---

## Langkah 6: Upload Google Credentials

### 6.1 Upload File

Upload `google-credentials.json` ke:
```
/www/wwwroot/revenue.yourdomain.com/google-credentials.json
```

### 6.2 Set Permission (PENTING untuk keamanan!)

```bash
chmod 600 /www/wwwroot/revenue.yourdomain.com/google-credentials.json
chown www:www /www/wwwroot/revenue.yourdomain.com/google-credentials.json
```

---

## Langkah 7: Set Folder Permissions

```bash
cd /www/wwwroot/revenue.yourdomain.com

# Set ownership
chown -R www:www .

# Set folder permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions  
find . -type f -exec chmod 644 {} \;

# Writable folder harus writable
chmod -R 775 writable/
chmod -R 775 public/assets/
```

---

## Langkah 8: Jalankan Migration

```bash
cd /www/wwwroot/revenue.yourdomain.com

php spark migrate
```

Output yang diharapkan:
```
Running all new migrations...
...
Done migrations.
```

### 8.1 Jalankan Seeder (Opsional - untuk data awal)

```bash
php spark db:seed CompanySeeder
php spark db:seed UserSeeder
```

**PENTING**: Setelah seeder, segera ganti password admin!

---

## Langkah 9: Buat Website di aaPanel

### 9.1 Add Website

```
aaPanel > Websites > Add Site
```

- **Domain**: `revenue.yourdomain.com`
- **Root Directory**: `/www/wwwroot/revenue.yourdomain.com/public`
- **PHP Version**: PHP 8.1
- **Database**: None (sudah dibuat manual)

**PENTING**: Root directory harus ke folder `/public`!

### 9.2 Konfigurasi Nginx

```
aaPanel > Websites > revenue.yourdomain.com > Settings > URL Rewrite
```

Pilih: **codeigniter4** atau paste manual:

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass unix:/tmp/php-cgi-81.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_intercept_errors on;
}

# Block access to sensitive files
location ~ /\. {
    deny all;
}

location ~ /(app|system|writable|tests|vendor)/ {
    deny all;
}
```

### 9.3 Setup SSL (HTTPS)

```
aaPanel > Websites > revenue.yourdomain.com > SSL
```

- Pilih **Let's Encrypt**
- Centang domain
- Klik **Apply**
- Enable **Force HTTPS**

---

## Langkah 10: Setup Cron Job

### 10.1 Tambah Cron di aaPanel

```
aaPanel > Cron Jobs > Add Cron Job
```

**Opsi A: Via URL (Recommended)**
- **Type**: Access URL
- **Name**: Revenue Sync
- **Cycle**: Every 5 minutes
- **URL**: `https://revenue.yourdomain.com/sync/cron?key=YOUR_CRON_SECRET_KEY`

**Opsi B: Via Shell**
- **Type**: Shell Script
- **Name**: Revenue Sync
- **Cycle**: Every 5 minutes
- **Script**:
```bash
cd /www/wwwroot/revenue.yourdomain.com && /usr/bin/php spark sync:sheets >> /www/wwwroot/revenue.yourdomain.com/writable/logs/cron.log 2>&1
```

---

## Langkah 11: Test Aplikasi

### 11.1 Akses Website

Buka browser: `https://revenue.yourdomain.com`

### 11.2 Login Default

- **Email**: `admin@bosowa.co.id`
- **Password**: `password123`

### 11.3 SEGERA Ganti Password!

1. Login ke aplikasi
2. Buat user baru dengan password kuat (8+ karakter, huruf besar/kecil, angka)
3. Hapus atau nonaktifkan user default

---

## Langkah 12: Security Checklist

### 12.1 Pastikan Sudah Dilakukan

- [ ] `CI_ENVIRONMENT = production` di .env
- [ ] Password database kuat
- [ ] Encryption key sudah di-generate
- [ ] Cron secret key sudah di-generate
- [ ] SSL/HTTPS aktif
- [ ] Password admin sudah diganti
- [ ] google-credentials.json permission 600
- [ ] Folder sensitive tidak bisa diakses publik

### 12.2 Test Security

```bash
# Harus return 403 Forbidden
curl https://revenue.yourdomain.com/app/
curl https://revenue.yourdomain.com/.env
curl https://revenue.yourdomain.com/writable/
```

---

## Troubleshooting

### Error: 500 Internal Server Error

```bash
# Cek log error
tail -f /www/wwwroot/revenue.yourdomain.com/writable/logs/log-*.log
tail -f /www/wwwlogs/revenue.yourdomain.com.error.log
```

### Error: Permission Denied

```bash
chown -R www:www /www/wwwroot/revenue.yourdomain.com
chmod -R 775 /www/wwwroot/revenue.yourdomain.com/writable
```

### Error: Class Not Found

```bash
composer dump-autoload
```

### Error: Database Connection

- Cek credentials di `.env`
- Pastikan database sudah dibuat
- Pastikan user punya akses ke database

### Error: Blank Page

```bash
# Enable error display temporary untuk debug
# Edit .env:
CI_ENVIRONMENT = development

# Setelah fix, kembalikan ke:
CI_ENVIRONMENT = production
```

### Error: Google Sheets Sync Failed

- Cek path `google.sheets.credentials_file` di `.env`
- Cek permission file: `ls -la google-credentials.json`
- Cek log: `tail -f writable/logs/log-*.log`

---

## Maintenance

### Backup Database

```
aaPanel > Databases > revenue_bosowa > Backup
```

Atau via cron:
```bash
mysqldump -u revenue_user -p revenue_bosowa > /backup/revenue_$(date +%Y%m%d).sql
```

### Update Aplikasi

```bash
cd /www/wwwroot/revenue.yourdomain.com

# Pull changes (jika pakai git)
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run new migrations
php spark migrate

# Clear cache
rm -rf writable/cache/*
```

### Monitor Logs

```bash
# Application logs
tail -f /www/wwwroot/revenue.yourdomain.com/writable/logs/log-*.log

# Nginx access log
tail -f /www/wwwlogs/revenue.yourdomain.com.log

# Nginx error log
tail -f /www/wwwlogs/revenue.yourdomain.com.error.log
```

---

## Estimasi Waktu Deploy

| Langkah | Waktu |
|---------|-------|
| Install PHP Extensions | 2-3 menit |
| Upload Project | 2-5 menit |
| Install Composer | 1-2 menit |
| Konfigurasi | 5-10 menit |
| Setup Website | 2-3 menit |
| Setup SSL | 1-2 menit |
| Testing | 5 menit |
| **Total** | **~20-30 menit** |

---

## Kontak Support

Jika ada masalah saat deploy, cek:
1. Log error di `writable/logs/`
2. Log Nginx di `/www/wwwlogs/`
3. PHP error log di aaPanel

**Good luck dengan deployment! 🚀**
