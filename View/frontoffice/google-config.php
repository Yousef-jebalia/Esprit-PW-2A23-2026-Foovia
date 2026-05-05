<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$envFile = __DIR__ . '/../../.env';
$env = is_file($envFile) ? parse_ini_file($envFile) : [];
if (!is_array($env)) {
	$env = [];
}

$clientID = $env['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $env['GOOGLE_CLIENT_SECRET'] ?? '';

// Get the base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_dir = dirname($_SERVER['PHP_SELF']);
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
