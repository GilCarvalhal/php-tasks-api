<?php
declare(strict_types=1);

namespace App\Support;

use PDO;
use Throwable;
use RuntimeException;

final class Database
{
    /** Instância única (cache) do PDO */
    private static ?PDO $pdo = null;

    /** Quantidade máxima de tentativas de conexão antes de falhar */
    private const MAX_TENTATIVAS_CONEXAO = 5;

    /** Intervalo entre tentativas (microsegundos). */
    private const INTERVALO_TENTATIVA_US = 200_000; // 200ms
    
    /** Construtor privado para impedir instanciação direta.
     * 
     * Use Database::pdo() para obter a conexão.
     */
    private function __construct(){}

    /**
     * Retorna uma conexão PDO ao MySQL.
     * 
     * Faz tentativas de retry enquanto o banco sobe (útil no Docker).
     * 
     * @throws RuntimeException
     * @return PDO
     */
    public static function pdo(): PDO
    {
        if(self::$pdo instanceof PDO)
        {
            return self::$pdo;
        }

        $host = getenv('DB_HOST') ?: 'db';
        $port = (int) (getenv('DB_PORT') ?: 3306);
        $db = getenv('DB_DATABASE') ?: 'app';
        $user = getenv('DB_USERNAME') ?: 'app';
        $pass = getenv('DB_PASSWORD') ?: 'app';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        $options =
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $contadorTentativas = 0;

        /** @var ?Throwable $ultimaExcecao */
        $ultimaExcecao = null;

        while($contadorTentativas < self::MAX_TENTATIVAS_CONEXAO)
        {
            try
            {
                self::$pdo = new PDO($dsn, $user, $pass, $options);

                return self::$pdo;
            }
            catch(Throwable $erro)
            {
                $ultimaExcecao = $erro;

                $contadorTentativas++;

                usleep(self::INTERVALO_TENTATIVA_US);
            }
        }

        $mensagem = $ultimaExcecao?->getMessage() ?? 'Erro desconhecido';

        throw new RuntimeException(
            'Falha ao conectar no MySQL após ' . self::MAX_TENTATIVAS_CONEXAO . " tentativas: {$mensagem}"
        );
    }
}