# Changelog

Tat ca thay doi quan trong cua du an se duoc ghi lai tai day.

## [2.1.0] - 2024-01-20

### Added
- Campaign System: Tao chien dich dang ky voi bonus tokens
  - Admin tao campaign voi slug, bonus, gioi han
  - URL: `/c/slug` hoac `/register?campaign=slug`
  - Thong ke nguoi dang ky va tokens da phat
- Enhanced Analytics Dashboard
  - Chart.js integration cho bieu do
  - Export CSV cho bao cao
  - Filter theo thoi gian
- Leaderboard System
  - Bang xep hang theo tokens earned
  - Top users hang thang
- Live Chat Widget
  - Chat truc tuyen voi support
  - Realtime notifications
- User Feedback System
  - Rating va review
  - Feedback form

### Improved
- Dashboard performance optimization
- Better mobile responsiveness
- Enhanced error messages

### Fixed
- Fixed timezone issues in analytics
- Fixed pagination in admin users list

## [2.0.0] - 2024-01-15

### Added
- Gift Code System
  - Tao gift codes (tokens/credits)
  - Bulk generate voi prefix
  - Export CSV danh sach codes
  - Theo doi su dung
- Daily Check-in Rewards
  - Diem danh hang ngay nhan tokens
  - Streak bonus cho check-in lien tuc
  - Calendar view
- Achievement/Badge System
  - Cac achievements unlock duoc
  - Badge display tren profile
  - Progress tracking
- Ticket Support System
  - Tao tickets voi priority
  - Conversation threads
  - Admin reply va close
  - Internal notes
- Notification Center
  - In-app notifications
  - Mark as read/unread
  - Notification preferences
- API Key Templates
  - Luu template cau hinh key
  - Quick create tu template
- Quick Search (Ctrl+K)
  - Tim kiem nhanh khap ung dung
  - Navigate bang keyboard

### Improved
- Sidebar navigation restructured
- Header voi user dropdown
- Dark mode colors improved
- Form validation messages

### Fixed
- Fixed 2FA verification edge cases
- Fixed deposit expiry timing

## [1.5.0] - 2024-01-10

### Added
- VietQR Payment Integration
  - Generate QR code tu VietQR API
  - Auto-generate reference code
  - Deposit management
  - Admin approve/reject
- Two-Factor Authentication (2FA)
  - TOTP-based 2FA
  - QR code setup
  - Backup codes
- Email Verification
  - Verification email on register
  - Resend verification
- Password Reset
  - Forgot password flow
  - Reset via email link
  - Token expiration
- User Profile Features
  - Edit name/email
  - Change password
  - Upload avatar
  - Delete account
- Enhanced Admin Features
  - Admin dashboard voi stats
  - Deposit management
  - Ban/unban users
  - Edit user balance
  - Change user plan
  - Impersonate users
  - Comprehensive audit logs
- System Settings Page
  - General settings
  - Maintenance mode
  - Payment settings (VietQR)
  - Email settings (SMTP)
  - Limits settings
- API Documentation Page
  - Interactive docs
  - Code examples (cURL, Python, Node.js, PHP)
  - Model list va pricing
  - Rate limits info

### Improved
- Database schema optimization
- Better error handling
- Improved security (CSRF, XSS)

### Fixed
- Fixed rate limiting accuracy
- Fixed session timeout issues

## [1.2.0] - 2024-01-05

### Added
- Subscription Plans
  - Multiple plan tiers
  - Plan features comparison
  - Auto-renewal
- Model Pricing Configuration
  - Input/output price per model
  - Active/inactive models
- Provider Management
  - Multiple AI providers
  - Provider status monitoring
- Rate Limiting
  - Per-minute limits
  - Per-day limits
  - Per-key limits

### Improved
- API response format consistency
- Better streaming support
- Logging improvements

## [1.1.0] - 2024-01-02

### Added
- Multi-language Support
  - Vietnamese (VI)
  - English (EN)
  - Language switcher
- Dark/Light Theme
  - Theme toggle
  - System preference detection
  - Persistent preference
- PWA Support
  - manifest.json
  - Service worker
  - Offline page

### Improved
- Mobile responsiveness
- Loading states
- Error pages (404, 500)

## [1.0.0] - 2024-01-01

### Added
- Initial Release
- User Authentication
  - Register/Login
  - Session management
  - Password hashing (bcrypt)
- API Gateway
  - OpenAI compatible API
  - /v1/chat/completions endpoint
  - /v1/models endpoint
  - Model mapping
  - Upstream proxy
  - Request logging
  - Error handling
  - Streaming support
- Billing System
  - User balance
  - Usage tracking
  - Transaction history
- API Key Management
  - Create/delete keys
  - Key statistics
- Admin Panel
  - User management
  - Basic settings
- Database
  - MySQL/MariaDB support
  - Migration system

---

## Versioning

Du an su dung [Semantic Versioning](https://semver.org/):
- MAJOR: Thay doi khong tuong thich nguoc
- MINOR: Tinh nang moi tuong thich nguoc
- PATCH: Bug fixes tuong thich nguoc

## Links

- [GitHub Repository](https://github.com/hotroanishop-bit/Hay)
- [Documentation](docs/)
- [Issues](https://github.com/hotroanishop-bit/Hay/issues)
