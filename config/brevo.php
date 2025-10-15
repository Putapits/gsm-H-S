<?php
/**
 * Brevo API Configuration
 * 
 * IMPORTANT: Get your API key from https://app.brevo.com/settings/keys/api
 * 
 * SECURITY NOTE: 
 * - This file reads from environment variables set in .htaccess or server config
 * - Never hardcode API keys in this file
 * - The actual keys are stored in .htaccess (which is gitignored)
 */

// Your Brevo API Key - reads from environment variable
define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');

// Sender Email (must be verified in your Brevo account)
define('BREVO_SENDER_EMAIL', getenv('BREVO_SENDER_EMAIL') ?: 'santos.peterjames.divinagracia@gmail.com');

// Sender Name
define('BREVO_SENDER_NAME', getenv('BREVO_SENDER_NAME') ?: 'Health & Sanitation');
