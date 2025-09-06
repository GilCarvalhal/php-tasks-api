<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Support\Database;

$pdo  = Database::pdo();
$file = __DIR__ . '/migrations/001_create_tasks.sql';

if (!is_file($file)) {
    fwrite(STDERR, "Arquivo de migration não encontrado: {$file}\n");
    exit(1);
}

$sql = file_get_contents($file) ?: '';
$statements = array_filter(array_map('trim', explode(';', $sql)));

try {
    foreach ($statements as $stmt) {
        if ($stmt !== '') {
            $pdo->exec($stmt);
        }
    }
    echo "Migrations executadas com sucesso.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Erro ao executar migrations: " . $e->getMessage() . "\n");
    exit(1);
}
