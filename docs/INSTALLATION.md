# Huong Dan Cai Dat Hay API Gateway

## Yeu Cau He Thong

### Server
- Ubuntu 20.04+ / CentOS 7+ / Debian 10+
- RAM: Toi thieu 1GB (khuyen nghi 2GB+)
- Disk: Toi thieu 10GB

### Software
- PHP 8.0 hoac cao hon
- MySQL 5.7+ hoac MariaDB 10.3+
- Apache 2.4+ hoac Nginx 1.18+
- Composer (khong bat buoc)

### PHP Extensions
- pdo_mysql
- curl
- json
- mbstring
- openssl
- fileinfo

## Cai Dat Tung Buoc

### Buoc 1: Cai dat LAMP/LEMP Stack

#### Ubuntu/Debian

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-curl php8.1-json php8.1-mbstring php8.1-xml php8.1-zip

# Install MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Install Nginx
sudo apt install nginx
```

#### CentOS/RHEL

```bash
# Install EPEL and Remi
sudo yum install epel-release
sudo yum install https://rpms.remirepo.net/enterprise/remi-release-7.rpm
sudo yum install yum-utils
sudo yum-config-manager --enable remi-php81

# Install PHP
sudo yum install php php-fpm php-mysqlnd php-curl php-json php-mbstring php-xml php-zip

# Install MySQL
sudo yum install mysql-server
sudo systemctl start mysqld
```

### Buoc 2: Clone Project

```bash
cd /var/www
sudo git clone https://github.com/hotroanishop-bit/Hay.git
cd Hay
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
```

### Buoc 3: Tao Database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE hay_gateway CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hay_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON hay_gateway.* TO 'hay_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Buoc 4: Import Database

```bash
cd /var/www/Hay

# Import all migrations
mysql -u hay_user -p hay_gateway < database/migrations/all_features_combined.sql
mysql -u hay_user -p hay_gateway < database/migrations/new_features_v2.sql
mysql -u hay_user -p hay_gateway < database/migrations/new_features_v3.sql
mysql -u hay_user -p hay_gateway < database/migrations/new_features_v4.sql
mysql -u hay_user -p hay_gateway < database/migrations/campaigns.sql
```

### Buoc 5: Cau hinh

```bash
cp config/config.example.php config/config.php
nano config/config.php
```

Sua cac thong so:
- Database connection
- App URL
- Mail settings
- Telegram bot (neu co)

```php
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'hay_gateway',
        'user' => 'hay_user',
        'pass' => 'your_secure_password'
    ],
    'app' => [
        'url' => 'https://yourdomain.com',
        'name' => 'Hay API Gateway',
        'debug' => false
    ],
    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'user' => 'your@email.com',
        'pass' => 'your_app_password'
    ],
    'telegram' => [
        'bot_token' => 'your_bot_token',
        'admin_chat_id' => 'your_chat_id'
    ]
];
```

### Buoc 6: Cau hinh Web Server

#### Nginx

```bash
sudo nano /etc/nginx/sites-available/hay
```

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/Hay/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/hay /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Apache

Da co .htaccess, chi can enable mod_rewrite:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Buoc 7: SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### Buoc 8: Tao Admin User

```bash
mysql -u hay_user -p hay_gateway
```

```sql
-- Dang ky user binh thuong truoc, sau do:
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

Hoac insert truc tiep:

```sql
-- Mat khau: Admin@123 (da hash)
INSERT INTO users (name, email, password, is_admin, is_active, created_at) 
VALUES ('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NOW());
```

### Buoc 9: Cau hinh Cron Jobs

```bash
crontab -e
```

Them:

```cron
* * * * * php /var/www/Hay/cron/scheduler.php >> /var/log/hay-cron.log 2>&1
0 0 * * * php /var/www/Hay/cron/cleanup.php >> /var/log/hay-cleanup.log 2>&1
```

## Kiem Tra

1. Truy cap: https://yourdomain.com
2. Dang ky tai khoan
3. Dang nhap Admin: /admin
4. Tao API key va test

## Troubleshooting

### Loi 500 Internal Server Error

```bash
sudo tail -f /var/log/nginx/error.log
# hoac
sudo tail -f /var/log/apache2/error.log
```

### Loi Permission

```bash
sudo chown -R www-data:www-data /var/www/Hay
sudo chmod -R 755 /var/www/Hay
```

### Loi Database Connection

- Kiem tra thong tin trong config/config.php
- Kiem tra MySQL dang chay: `sudo systemctl status mysql`

### Loi PHP Extensions

```bash
# Kiem tra extensions da cai
php -m

# Cai them neu thieu
sudo apt install php8.1-curl php8.1-mysql php8.1-mbstring php8.1-xml
```

### Loi Upload File

```bash
# Tang gioi han upload trong php.ini
sudo nano /etc/php/8.1/fpm/php.ini
```

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

```bash
sudo systemctl restart php8.1-fpm
```

## Cai Dat voi Docker

Neu ban muon su dung Docker:

```bash
# Tao Dockerfile
cat > Dockerfile << 'EOF'
FROM php:8.1-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html
WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
EOF

# Build va chay
docker build -t hay-api .
docker run -d -p 9000:9000 hay-api
```

## Cap Nhat

Khi co version moi:

```bash
cd /var/www/Hay
git pull origin main

# Import migrations moi (neu co)
mysql -u hay_user -p hay_gateway < database/migrations/new_migration.sql

# Xoa cache (neu co)
php artisan cache:clear  # neu dung Laravel-style cache
```

## Lien He Ho Tro

- GitHub Issues: https://github.com/hotroanishop-bit/Hay/issues
- Email: support@yourdomain.com
