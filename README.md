# Hay API Gateway

Professional API Gateway platform with user management, billing, and AI model proxy.

## Features

- User Authentication (Register, Login, 2FA)
- Billing System (PAYG + Plans, VietQR Payment)
- API Key Management
- AI Gateway/Proxy (OpenAI compatible)
- Analytics Dashboard
- Gift Code System
- Daily Check-in Rewards
- Achievement System
- Support Ticket System
- Notification Center
- Referral System
- Campaign System (Registration Campaigns with Bonus)
- Multi-language (VI/EN)
- Dark/Light Theme
- PWA Support

## Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx with mod_rewrite
- cURL extension
- JSON extension
- PDO MySQL extension

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/hotroanishop-bit/Hay.git
cd Hay
```

### 2. Configure Database

Create MySQL database:

```sql
CREATE DATABASE hay_gateway CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import migrations:

```bash
mysql -u root -p hay_gateway < database/migrations/all_features_combined.sql
mysql -u root -p hay_gateway < database/migrations/new_features_v2.sql
mysql -u root -p hay_gateway < database/migrations/new_features_v3.sql
mysql -u root -p hay_gateway < database/migrations/new_features_v4.sql
mysql -u root -p hay_gateway < database/migrations/campaigns.sql
```

### 3. Configure Environment

Copy and edit config file:

```bash
cp config/config.example.php config/config.php
```

Edit `config/config.php`:

```php
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'hay_gateway',
        'user' => 'your_db_user',
        'pass' => 'your_db_password'
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

### 4. Configure Web Server

**Apache (.htaccess already included):**

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/hay/public
    
    <Directory /var/www/hay/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/hay/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Set Permissions

```bash
chmod -R 755 public/
chmod -R 777 storage/  # If exists
```

### 6. Create Admin User

Register normally and update:

```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

Or insert directly:

```sql
INSERT INTO users (name, email, password, is_admin, is_active, created_at) 
VALUES ('Admin', 'admin@example.com', '$2y$10$...hashed_password...', 1, 1, NOW());
```

## Directory Structure

```
Hay/
├── app/
│   ├── Controllers/     # Request handlers
│   ├── Models/          # Database models
│   ├── Services/        # Business logic
│   └── Middleware/      # Request middleware
├── config/              # Configuration files
├── database/
│   └── migrations/      # SQL migration files
├── lang/                # Translation files (vi.php, en.php)
├── public/              # Web root
│   ├── index.php        # Entry point
│   ├── css/
│   ├── js/
│   └── assets/
├── routes/
│   └── web.php          # Route definitions
├── views/
│   ├── layouts/         # Layout templates
│   ├── pages/           # Page views
│   └── partials/        # Reusable components
└── README.md
```

## API Usage

### Authentication

```bash
curl -X POST https://yourdomain.com/v1/chat/completions \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "gpt-4",
    "messages": [{"role": "user", "content": "Hello!"}]
  }'
```

### Available Models

- gpt-4
- gpt-3.5-turbo
- claude-3
- (Configure in admin panel)

## Campaign System

Admin can create registration campaigns with bonus tokens:

1. Go to Admin Panel > Campaigns
2. Create new campaign with:
   - Name and description
   - URL slug (e.g., `tet-2025`)
   - Bonus tokens/credits
   - Max registrations (optional)
   - Start/end dates (optional)
3. Share the campaign URL: `/c/your-slug` or `/register?campaign=your-slug`
4. Users who register through this URL will receive bonus tokens

## Cron Jobs

Add to crontab:

```bash
# Process scheduled tasks every minute
* * * * * php /var/www/hay/cron/scheduler.php

# Clean expired sessions daily
0 0 * * * php /var/www/hay/cron/cleanup.php
```

## Running Locally

For development, use PHP built-in server:

```bash
cd public
php -S localhost:8000
```

Then visit: http://localhost:8000

## License

MIT License

## Support

- Create an issue on GitHub
- Email: support@yourdomain.com
