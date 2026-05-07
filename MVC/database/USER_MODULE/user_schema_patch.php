<?php
require_once __DIR__ . '/../../Model/config.php';
$db = config::getConnexion();

function columnExists(PDO $db, string $table, string $column): bool {
	$sql = "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name";
	$stmt = $db->prepare($sql);
	$stmt->execute([
		'table_name' => $table,
		'column_name' => $column
	]);

	return (int)$stmt->fetchColumn() > 0;
}

$columnsToAdd = [
	'reset_token' => "ALTER TABLE user ADD COLUMN reset_token VARCHAR(64) NULL",
	'reset_token_expires_at' => "ALTER TABLE user ADD COLUMN reset_token_expires_at DATETIME NULL",
	'subscription_user' => "ALTER TABLE user ADD COLUMN subscription_user VARCHAR(50) NOT NULL DEFAULT 'normal'",
	'account_state_user' => "ALTER TABLE user ADD COLUMN account_state_user VARCHAR(50) NOT NULL DEFAULT 'active'",
	'duration_user' => "ALTER TABLE user ADD COLUMN duration_user TIME NOT NULL DEFAULT '00:00:00'",
	'failed_attempts_user' => "ALTER TABLE user ADD COLUMN failed_attempts_user INT NOT NULL DEFAULT 0",
	'ban_until_user' => "ALTER TABLE user ADD COLUMN ban_until_user DATETIME NULL"
];

$added = [];
$skipped = [];

foreach ($columnsToAdd as $column => $alterSql) {
	if (columnExists($db, 'user', $column)) {
		$skipped[] = $column;
		continue;
	}

	$db->exec($alterSql);
	$added[] = $column;
}

echo "Added columns: " . (empty($added) ? 'none' : implode(', ', $added)) . PHP_EOL;
echo "Already existed: " . (empty($skipped) ? 'none' : implode(', ', $skipped)) . PHP_EOL;
