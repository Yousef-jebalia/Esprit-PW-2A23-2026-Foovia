<?php
// Try to locate Composer autoload in a few likely locations
$autoloadCandidates = [
	__DIR__ . '/../../../vendor/autoload.php',
];
$autoloadFound = false;
foreach ($autoloadCandidates as $p) {
	if (file_exists($p)) { require_once $p; $autoloadFound = true; break; }
}
if (!$autoloadFound) {
	error_log('Warning: vendor/autoload.php not found. Looked in: ' . implode(', ', $autoloadCandidates));
}

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

$clientID = $env['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $env['GOOGLE_CLIENT_SECRET'] ?? '';

// Get the base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_dir = isset($_SERVER['PHP_SELF']) ? dirname($_SERVER['PHP_SELF']) : '/integration foovia/MVC/View/front_office';
$base_dir = str_replace('\\', '/', $base_dir);
if ($base_dir !== '' && $base_dir[0] !== '/') { $base_dir = '/' . $base_dir; }
$redirectUri = $protocol . "://" . $host . $base_dir . '/google-callback.php';

$client = null;

if ($clientID !== '' && $clientSecret !== '') {
	$client = new Google_Client();
	$client->setClientId($clientID);
	$client->setClientSecret($clientSecret);
	$client->setRedirectUri($redirectUri);
	$client->addScope("email");
	$client->addScope("profile");
	$client->setPrompt("select_account");
}
