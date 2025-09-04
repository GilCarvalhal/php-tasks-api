<?php
declare(strict_types=1);

namespace App\Support;

final class Response
{
    /**
     * Envia uma resposta JSON.
     * 
     * @param array $data Payload a ser serializado em JSON.
     * @param int $status Código HTTP (ex.: 200, 201, 404).
     * @param array $headers Headers adicionais (ex.: ["X-Trace-Id" => "abc"]).
     * @return void
     */
    public static function json(array $data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $key => $value)
        {
            header("$key: $value");
        }

        echo json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * Envia 204 No Content
     * 
     * @return void
     */
    public static function noContent(): void
    {
        http_response_code(204);
    }

    /**
     * Envia 404 Not Found como uma mensagem.
     * 
     * @param string $msg Mensagem de erro (padrão: "Not Found").
     * @return void
     */
    public static function notFound(string $msg = 'Not Found'): void
    {
        self::json(['error' => $msg], 404);
    }

    /**
     * Envia 422 Unprocessable Entity para erros de validação.
     * 
     * @param string $msg Mensagem de erro geral (ex.: "Dados inválidos").
     * @param array $errors Detalhes por campo (ex.: ["title" => "obrigatório"]).
     * @return void
     */
    public static function unprocessable(string $msg, array $errors = []): void
    {
        self::json(['error' => $msg, 'details' => $errors], 422);
    }
}