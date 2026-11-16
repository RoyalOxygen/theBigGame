<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

use App\Controllers\AdminController;

require_admin();
if (!is_post() || !verify_csrf()) {
    flash('error', 'Invalid request.');
    redirect('admin/index.php');
}
$action = (string) ($_POST['action'] ?? '');
$id = (int) ($_POST['id'] ?? 0);
match ($action) {
    'approve' => AdminController::approve($id),
    'reject' => AdminController::reject($id),
    'info' => AdminController::requestInfo($id),
    'note' => AdminController::addNote($id),
    'card_status' => AdminController::updateCardStatus($id),
    default => redirect('admin/index.php'),
};
