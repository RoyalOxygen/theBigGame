<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$stateId = (int) ($_GET['state_id'] ?? 0);
$stmt = db()->prepare('SELECT id, name FROM local_governments WHERE state_id = ? ORDER BY name');
$stmt->execute([$stateId]);
json_response(['lgas' => $stmt->fetchAll()]);
