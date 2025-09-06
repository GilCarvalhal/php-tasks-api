<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class MysqlTaskRepository implements TaskRepository
{
    public function __construct(private PDO $pdo) {}

    
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, title FROM tasks ORDER BY id DESC');
        return $stmt->fetchAll() ?: [];
    }

    
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, title FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO tasks (title) VALUES (:title)');
        $stmt->execute(['title' => $data['title']]);
        return (int) $this->pdo->lastInsertId();
    }

    
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE tasks SET title = :title WHERE id = :id');
        return $stmt->execute(['title' => $data['title'], 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
