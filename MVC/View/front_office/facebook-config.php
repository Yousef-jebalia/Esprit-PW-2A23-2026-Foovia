<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Try to locate .env in likely locations
$envCandidates = [
	__DIR__ . '/../../../.env',
];
$envFile = null;
foreach ($envCandidates as $p) {
	if (is_file($p)) { $envFile = $p; break; }
}
$env = $envFile ? parse_ini_file($envFile) : [];
if (!is_array($env)) { $env = []; }
$fb_app_id = $env['FACEBOOK_APP_ID'] ?? '';
$fb_app_secret = $env['FACEBOOK_APP_SECRET'] ?? '';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_dir = isset($_SERVER['PHP_SELF']) ? dirname($_SERVER['PHP_SELF']) : '/integration foovia/MVC/View/front_office';
$base_dir = str_replace('\\', '/', $base_dir);
if ($base_dir !== '' && $base_dir[0] !== '/') { $base_dir = '/' . $base_dir; }
$fb_redirect_uri = $protocol . "://" . $host . $base_dir . '/facebook-callback.php';

$fb_login_url = null;

if ($fb_app_id !== '') {
	$fb_login_url = "https://www.facebook.com/v19.0/dialog/oauth?client_id=" . $fb_app_id . "&redirect_uri=" . urlencode($fb_redirect_uri) . "&scope=email,public_profile";
}
