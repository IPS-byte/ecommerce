<?php
// config.php
declare(strict_types=1);

date_default_timezone_set('UTC');

const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'auth_platform';

// JWT
const JWT_SECRET = 'REPLACE_WITH_A_STRONG_RANDOM_64_CHAR_STRING';
const JWT_ISS = 'yourdomain.com';
const JWT_AUD = 'yourdomain.com';
const JWT_EXP = 900; // 15 minutes
const REFRESH_DAYS = 30;

// Mail (PHPMailer) - set these or stub send_email
const MAIL_FROM = 'no-reply@yourdomain.com';
const MAIL_FROM_NAME = 'Your App';
const SMTP_HOST = 'smtp.example.com';
const SMTP_USER = 'smtp_user';
const SMTP_PASS = 'smtp_pass';
const SMTP_PORT = 587;
const SMTP_SECURE = 'tls';
