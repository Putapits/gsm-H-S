# Environment Variables Setup

## Brevo API Configuration

After cloning this repository, you need to set up environment variables for the Brevo email service.

### Option 1: Using Windows Environment Variables

1. Open **System Properties** → **Advanced** → **Environment Variables**
2. Add these user variables:
   - `BREVO_API_KEY` = your Brevo API key
   - `BREVO_SENDER_EMAIL` = your verified sender email
   - `BREVO_SENDER_NAME` = your app name

3. Restart your web server (Apache/XAMPP)

### Option 2: Using .htaccess (Recommended for XAMPP)

Create a `.htaccess` file in your project root:

```apache
SetEnv BREVO_API_KEY "your_api_key_here"
SetEnv BREVO_SENDER_EMAIL "your_email@example.com"
SetEnv BREVO_SENDER_NAME "Your App Name"
```

**Important:** Add `.htaccess` to `.gitignore` to prevent committing secrets!

### Option 3: Using php.ini or Apache config

Add to your `php.ini` or Apache virtual host configuration:

```ini
env[BREVO_API_KEY] = "your_api_key_here"
env[BREVO_SENDER_EMAIL] = "your_email@example.com"
env[BREVO_SENDER_NAME] = "Your App Name"
```

### Getting Your Brevo API Key

1. Go to https://app.brevo.com/settings/keys/api
2. Create a new API key or copy an existing one
3. **Never commit this key to Git!**

### Verifying Setup

After setting environment variables, verify they're loaded:

```php
<?php
echo getenv('BREVO_API_KEY') ? 'API Key loaded!' : 'API Key missing!';
?>
```
