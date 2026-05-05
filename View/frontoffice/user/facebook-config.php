<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Load env securely
$env = parse_ini_file(__DIR__ . '/../../.env');
$fb_app_id = $env['FACEBOOK_APP_ID'] ?? '';
$fb_app_secret = $env['FACEBOOK_APP_SECRET'] ?? '';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_dir = dirname($_SERVER['PHP_SELF']);
$fb_redirect_uri = $protocol . "://" . $host . $base_dir . '/facebook-callback.php';

$fb_login_url = "https://www.facebook.com/v19.0/dialog/oauth?client_id=" . $fb_app_id . "&redirect_uri=" . urlencode($fb_redirect_uri) . "&scope=email,public_profile";
