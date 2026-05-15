<!-- 2FA QUICK START GUIDE -->

# 🚀 Quick Start: 2FA Implementation

## Step 1: Create Database Table (2 minutes)

Copy and paste this SQL into phpMyAdmin:

```sql
CREATE TABLE IF NOT EXISTS two_factor_codes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT            NOT NULL,
    code         VARCHAR(6)     NOT NULL,
    expires_at   DATETIME       NOT NULL,
    attempts     INT            DEFAULT 0,
    max_attempts INT            DEFAULT 5,
    used         TINYINT(1)     DEFAULT 0,
    created_at   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_expires (user_id, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Step 2: Configure Email (1 minute)

Edit or create `.env` file in your project root:

```env
EMAIL_FROM=noreply@gainz-app.local
```

For Windows localhost testing, mail should work automatically.

## Step 3: Test It (1 minute)

1. Go to `/EcomProject/public/login`
2. Enter test user credentials
3. Check email for 6-digit code
4. Enter code on verification page
5. Should log in successfully ✅

## 🎯 What's New in the Login Flow

### Before (Old Flow)
```
User Email + Password 
    ↓
Check Credentials
    ↓
Direct Login ✓
```

### After (New with 2FA)
```
User Email + Password 
    ↓
Check Credentials
    ↓
Generate Code & Send Email
    ↓
Redirect to Verification Page
    ↓
User Enters Code
    ↓
Verify Code
    ↓
Login ✓
```

## 📧 How Emails Look

Users receive an email with:
- Your app logo (dumbbell icon)
- 6-digit code displayed prominently
- Instructions
- Expiry warning (10 minutes)
- Unstyled, plain text fallback

## 🔐 Key Features

| Feature | Details |
|---------|---------|
| Code Length | 6 digits |
| Valid Duration | 10 minutes |
| Max Attempts | 5 tries |
| Can Resend? | Yes, unlimited |
| Code Expiry | Auto-delete after time passes |
| Previous Codes | Auto-invalidated when new code sent |

## ⚡ Code Locations

| Component | Location |
|-----------|----------|
| 2FA Service | `app/Services/TwoFactorService.php` |
| Email Service | `app/Services/EmailService.php` |
| Auth Controller | `app/Controllers/AuthController.php` |
| Verification Page | `app/Views/auth/verify-2fa.twig` |
| Routes | `config/routes.php` |
| DB Table | `two_factor_codes` |

## 🛠️ Customization Examples

### Change Code Expiry to 5 Minutes
File: `app/Services/TwoFactorService.php` (line 15)
```php
private const EXPIRY_MINUTES = 5;  // Changed from 10
```

### Change Max Attempts to 3
File: `app/Services/TwoFactorService.php` (line 16)
```php
private const MAX_ATTEMPTS = 3;  // Changed from 5
```

### Change Code Length to 8 Digits
File: `app/Services/TwoFactorService.php` (line 14)
```php
private const CODE_LENGTH = 8;  // Changed from 6
```
Then update `verify-2fa.twig` to have 8 input boxes instead of 6.

## 🎨 UI Customization

The verification page uses your GAINZ theme colors:
- `#c8ff00` - Main accent (lime)
- `#080808` - Dark background
- `#111111` - Card background

To change colors, edit `app/Views/auth/verify-2fa.twig` style section.

## 🐛 Debugging

### To see generated codes (debug only - remove later)
Add to `app/Services/TwoFactorService.php` in `generateAndStoreCode()`:
```php
error_log("Generated 2FA code for user $userId: $code");
```

### To check database entries
```sql
SELECT * FROM two_factor_codes ORDER BY created_at DESC LIMIT 5;
```

### To clear expired codes manually
```sql
DELETE FROM two_factor_codes WHERE expires_at < NOW();
```

## 📞 Common Issues

### "Email not received"
- Check PHP mail configuration on your server
- Check spam folder
- Verify `EMAIL_FROM` in .env

### "Code always invalid"
- Ensure code is exactly 6 digits
- Check code hasn't expired (10 min limit)
- Verify database table was created

### "Stuck on verification page"
- Clear browser cookies/cache
- Try resending code
- Check browser console for JavaScript errors

## ✨ What the User Sees

### Login Page (Before 2FA)
- Username/Email field
- Password field
- Login button

### Verification Page (After 2FA)
- Message: "Enter the 6-digit code sent to email@example.com"
- 6 input boxes for code digits
- Auto-focus and auto-advance between fields
- Paste support (can paste full code)
- Resend Code button
- Attempt counter warning when low

## 🔒 Security Summary

- Codes are random 6-digit numbers
- Codes expire after 10 minutes
- Failed attempts counted (5 max)
- Previous codes invalidated on new request
- Session data cleaned up after verification
- No sensitive data in URLs or cookies

## 🎓 How It Works (Technical)

1. **Login Request**
   - User submits email + password
   - Credentials validated
   
2. **Code Generation**
   - Random 6-digit code generated
   - Stored in `two_factor_codes` table
   - Set expiry = now + 10 minutes
   
3. **Email Send**
   - HTML email generated with code
   - Sent to user's email address
   
4. **Temporary Session**
   - `2fa_pending_user_id` stored in session
   - User redirected to `/verify-2fa`
   
5. **Verification**
   - User submits code
   - Code checked against database
   - Verified code marked as used
   
6. **Login Complete**
   - User ID stored in session
   - 2FA temp data cleaned up
   - Redirect to dashboard

---

**Ready to go!** 🚀
