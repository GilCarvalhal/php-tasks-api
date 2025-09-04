<?php
declare(strict_types=1);

namespace App\Support;

final class Request
{
    /**
     * @param string $method Método HTTP (GET, POST, PUT, DELETE, ...)
     * @param string $path Caminho da URL (ex.: "/tasks/1")
     * @param array $query Parâmetros de query (ex.: $_GET)
     * @param array $headers Cabeçalhos HTTP (ex.: "Content-Type" => "application/json")
     * @param mixed $jsonBody Corpo JSON já decodificado (ou null)
     * @param array $routeParams Parâmetros de rota resolvidos pelo Router (ex.: ["id" => "1"])
     */
    public function __construct
    (
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $headers,
        private readonly ?array $jsonBody = null,
        private array $routeParams = [],
    ){}

    /**
     * Cria uma Request a partir das superglobais PHP.
     * 
     * - Lê método de $_SERVER['REQUEST_METHOD']
     * - Extrai o path de $_SERVER['REQUEST_URI']
     * - Copia o query de $_GET
     * - Tenta decodificar o corpo como JSON quando Content-Type inclui "application/json"
     * 
     * @return self
     */
    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, \PHP_URL_PATH) ?: '/';

        $query = $_GET ?? [];

        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];

        $raw = file_get_contents('php://input') ?: '';

        $jsonBody = null;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? ($headers['Content-Type'] ?? $headers['content-type'] ?? '');

        if($raw !== '' && str_contains(strtolower($contentType), 'application/json'))
        {
            $decoded = json_decode($raw, true);

            $jsonBody = is_array($decoded) ? $decoded : null;
        }

        return new self($method, $path, $query, $headers, $jsonBody);
    }

    /**
     * Retorna uma nova instância com parâmetros de rota aplicados (imutável).
     * 
     * @param array<string, string> $params
     * @return self
     */
    public function withRouteParams(array $params): self
    {
        $clone = clone $this;

        $clone->routeParams = $params;

        return $clone;
    }

    /**
     * Retorna o corpo JSON já decodificado (se Content-Type for application/json), ou null.
     * 
     * @return array<string, mixed>|null
     */
    public function json(): ?array
    {
        return $this->jsonBody;
    }

    /**
     * Obtém um parâmetro de rota resolvido (ex.: "{id}" em "/tasks/{id}").
     * 
     * @param string $key Nome do parâmetro de rota
     * @param mixed $default Valor padrão caso o parâmetro não exista
     * @return mixed
     */
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Obtém um parâmetro da query string (ex.: "?page=2").
     * 
     * @param string $key Nome do parâmetro de query
     * @param mixed $default Valor padrão caso o parâmetro não exista
     * @return mixed
     */
    public function queryParam(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }
}