# 🕌 EN NUR - MEMBERSHIP SYSTEM

A comprehensive Laravel-based membership management system with integrated payment processing for EN NUR organization.

## ✨ Features

### 🔐 **Authentication & Authorization**
- Multi-role system (Super Admin, Admin, User)
- Secure user registration and login
- Email verification
- Password reset functionality

### 💳 **Payment Processing**
- **Stripe** integration (Credit/Debit cards)
- **PayPal** integration
- **TWINT** Swiss mobile payment
- **Bank Transfer** with automated instructions
- Real-time payment status tracking
- Automated receipt generation (PDF)

### 👥 **User Management**
- Complete user profile management
- Admin dashboard for user oversight
- Bulk operations support
- User role management

### 📊 **Admin Dashboard**
- Payment history and analytics
- User management interface
- System backup functionality
- Log management
- Bulk notification system

### 📄 **PDF Export System**
- Professional payment receipts
- Payment history exports
- Admin reporting tools
- Branded PDF templates

### 🔄 **Membership Renewals**
- Automated renewal notifications
- Membership status tracking
- Renewal history management

## 🚀 **Quick Start**

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js & NPM

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/en-nur-membership.git
cd en-nur-membership
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database setup**
```bash
php artisan migrate
php artisan db:seed --class=DatabaseSeeder
```

5. **Start the application**
```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## ⚙️ **Production Deployment**

### Environment Configuration

**Critical:** Update `.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your_production_db_host
DB_DATABASE=en_nur_membership_prod
DB_USERNAME=your_production_db_user
DB_PASSWORD=your_strong_production_password

# Payment Gateways (LIVE KEYS)
STRIPE_KEY=pk_live_YOUR_REAL_STRIPE_KEY
STRIPE_SECRET=sk_live_YOUR_REAL_STRIPE_SECRET
PAYPAL_CLIENT_ID=YOUR_REAL_PAYPAL_CLIENT_ID
PAYPAL_CLIENT_SECRET=YOUR_REAL_PAYPAL_CLIENT_SECRET

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### Production Commands

```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Create admin user
php artisan db:seed --class=ProductionSeeder
```

### Required Updates Before Deployment

1. **Contact Information** - Update placeholder data in:
   - `app/Http/Controllers/PaymentController.php` (lines 1333, 1377)
   - `resources/views/payments/bank-instructions.blade.php` (line 109)
   - `resources/views/admin/payment-receipt-*.blade.php`

2. **Payment Gateway Setup**
   - Configure Stripe webhooks
   - Set up PayPal live credentials
   - Update bank transfer details

3. **Security Hardening**
   - Enable HTTPS/SSL
   - Configure firewall
   - Set up database backups
   - Configure log rotation

## 🔒 **Security Features**

- CSRF protection on all forms
- SQL injection prevention
- XSS protection
- Secure password hashing
- Rate limiting on sensitive endpoints
- Input validation and sanitization
- Secure file uploads

## 📱 **Payment Methods**

### Stripe
- Credit/Debit card processing
- Secure tokenization
- Webhook integration for real-time updates

### PayPal
- PayPal account payments
- Sandbox and live environment support
- Automatic payment verification

### TWINT
- Swiss mobile payment integration
- QR code generation
- Manual confirmation workflow

### Bank Transfer
- Automated instruction generation
- Payment reference tracking
- Manual confirmation system

## 🛠 **Development**

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Database Seeding
```bash
# Development data
php artisan db:seed

# Production admin only
php artisan db:seed --class=ProductionSeeder
```

## 📊 **System Requirements**

### Minimum Requirements
- PHP 8.1+
- MySQL 8.0+ or MariaDB 10.3+
- 512MB RAM
- 1GB disk space

### Recommended Requirements
- PHP 8.2+
- MySQL 8.0+
- 2GB RAM
- 5GB disk space
- Redis for caching
- SSL certificate

## 🔧 **Configuration**

### Payment Gateway Configuration

**Stripe:**
1. Create Stripe account
2. Get API keys from dashboard
3. Configure webhook endpoints
4. Update `.env` with live keys

**PayPal:**
1. Create PayPal developer account
2. Create live application
3. Get client credentials
4. Update `.env` configuration

### Email Configuration

Configure SMTP settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

## 📈 **Monitoring & Maintenance**

### System Backup
- Automated database backups via admin dashboard
- File system backup recommendations
- Payment transaction logging

### Log Management
- Laravel log rotation
- Error monitoring
- Performance tracking

### Updates
- Regular security updates
- Laravel framework updates
- Dependency management

## 🤝 **Contributing**

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 📄 **License**

This project is proprietary software developed for EN NUR organization.

## 🆘 **Support**

For technical support or questions:
- Email: admin@ennur.ch
- Documentation: See `/docs` folder
- Issues: GitHub Issues section

## 🔄 **Changelog**

### v1.0.0 (Current)
- Initial release
- Complete payment processing system
- Admin dashboard
- PDF export functionality
- User management system
- Membership renewal system

---

**⚠️ Security Notice:** Never commit `.env` files or expose API keys. Always use environment variables for sensitive configuration. 