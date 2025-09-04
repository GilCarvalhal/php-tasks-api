<?php
declare(strict_types=1);

namespace App\Support;

final class Router
{
    /**
     * Tabela de rotas indexada por método HTTP.
     *
     * @var array<
     *   string,
     *   array<int, array{
     *     regex: string,
     *     keys: array<int,string>,
     *     handler: callable
     *   }>
     * >
     */
    private array $routes =
        [
            'GET' => [],
            'POST' => [],
            'PUT' => [],
            'DELETE' => [],
            'OPTIONS' => [],            
        ];

        /**
         * Registra a rota GET.
         * 
         * @param string $path
         * @param callable $handler
         * @return void
         */
        public function get(string $path, callable $handler): void
        {
            $this->add('GET', $path, $handler);
        }

        /**
         * Registra a rota POST.
         * 
         * @param string $path
         * @param callable $handler
         * @return void
         */
        public function post(string $path, callable $handler): void
        {
            $this->add('POST', $path, $handler);
        }

        /**
         * Registra a rota PUT.
         * 
         * @param string $path
         * @param callable $handler
         * @return void
         */
        public function put(string $path, callable $handler): void
        {
            $this->add('PUT', $path, $handler);
        }

        /**
         * Registra a rota DELETE.
         * 
         * @param string $path
         * @param callable $handler
         * @return void
         */
        public function delete(string $path, callable $handler): void
        {
            $this->add('DELETE', $path, $handler);
        }
        /**
         * Registra a rota OPTIONS.
         * 
         * @param string $path
         * @param callable $handler
         * @return void
         */
        public function options(string $path, callable $handler): void
        {
            $this->add('OPTIONS', $path, $handler);
        }

        /**
         * Adiciona uma rota à tabela (normaliza e compila o path em regex).
         * 
         * @param string $method Método HTTP.
         * @param string $path Padrão do caminho (ex.: "/tasks/{id}")
         * @param callable $handler Função a executar quando casar.
         * @return void
         */
        private function add(string $method, string $path, callable $handler): void
        {
            [$regex, $keys] = $this->compile($path);

            $this->routes[$method][] = ['regex' => $regex, 'keys' => $keys, 'handler' => $handler];
        }

        /**
         * Compila o path da rota para regex e extrai os nomes dos parâmetros.
         * 
         * @param string $path
         * @return array{0:string,1:array<int,string>} [regex, keys]
         */
        private function compile(string $path): array
        {
            $path = $this->normalize($path);

            $keys = [];
            $regex = (string)\preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                static function (array $m) use (&$keys): string
                {
                $keys[] = $m[1];

                return '(?P<' . $m[1] . '>[^/]+)';
                },
                $path
        );
            return ['#^' . $regex . '$#', $keys];
        }

        /**
         * Normaliza paths (garante "/" inicial, remove "/" final, trata vazio).
         * 
         * @param string $path
         * @return string
         */
        private function normalize(string $path): string
        {
            $path = \trim($path);

            if ($path === '')
                {
                    return '/';
                }

            if ($path[0] !== '/')
                {
                    $path = '/' . $path;
                }

            if (\strlen($path) > 1)
                {
                    $path = \rtrim($path, '/');
                }

            return $path;
        }

        /**
         * Encontra e executa a rota que casa com o Request atual.
         * 
         * @param \App\Support\Request $request
         * @return void
         */
        public function dispatch(Request $request): void
        {
            $method = $request->method;

            $path = $this->normalize($request->path);

              foreach ($this->routes[$method] ?? [] as $route) {
                    if (\preg_match($route['regex'], $path, $m)) {
                $params = [];
                foreach ($route['keys'] as $k) {
                    $params[$k] = isset($m[$k]) ? \urldecode((string)$m[$k]) : null;
                }
                ($route['handler'])($request->withRouteParams($params));
                return;
            }
        }

            Response::notFound();
        }
}