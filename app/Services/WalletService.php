<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

class WalletService
{
    public static function ensure(int $userId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM wallets WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $wallet = $stmt->fetch();
        if ($wallet) {
            return $wallet;
        }
        $pdo->prepare('INSERT INTO wallets (user_id, available_balance, ledger_balance, created_at, updated_at) VALUES (?, 0, 0, NOW(), NOW())')->execute([$userId]);
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
    }

    public static function credit(int $userId, float $amount, string $category, string $description, ?string $reference = null, ?int $relatedId = null): array
    {
        return self::post($userId, $amount, 'credit', $category, $description, $reference, $relatedId);
    }

    public static function debit(int $userId, float $amount, string $category, string $description, ?string $reference = null, ?int $relatedId = null): array
    {
        return self::post($userId, $amount, 'debit', $category, $description, $reference, $relatedId);
    }

    private static function post(int $userId, float $amount, string $direction, string $category, string $description, ?string $reference, ?int $relatedId): array
    {
        if ($amount <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $wallet = self::lockWallet($pdo, $userId);
            $balance = (float) $wallet['available_balance'];
            if ($direction === 'debit' && $balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }
            $newBalance = $direction === 'credit' ? $balance + $amount : $balance - $amount;
            $ref = $reference ?: generate_reference('WTX');

            $pdo->prepare('UPDATE wallets SET available_balance = ?, ledger_balance = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$newBalance, $newBalance, $wallet['id']]);

            $pdo->prepare('INSERT INTO wallet_transactions (wallet_id, user_id, reference, direction, category, amount, balance_after, description, related_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())')
                ->execute([
                    $wallet['id'],
                    $userId,
                    $ref,
                    $direction,
                    $category,
                    $amount,
                    $newBalance,
                    $description,
                    $relatedId,
                    'success',
                ]);

            $txId = (int) $pdo->lastInsertId();
            $pdo->commit();

            $stmt = $pdo->prepare('SELECT * FROM wallet_transactions WHERE id = ?');
            $stmt->execute([$txId]);
            return $stmt->fetch() ?: [];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function lockWallet(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM wallets WHERE user_id = ? FOR UPDATE');
        $stmt->execute([$userId]);
        $wallet = $stmt->fetch();
        if (!$wallet) {
            $pdo->prepare('INSERT INTO wallets (user_id, available_balance, ledger_balance, created_at, updated_at) VALUES (?, 0, 0, NOW(), NOW())')->execute([$userId]);
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch();
        }
        return $wallet ?: [];
    }
}
