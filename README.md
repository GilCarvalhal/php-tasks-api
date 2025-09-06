# 📌 PHP Tasks API

[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Apache](https://img.shields.io/badge/Server-Apache_2.4-CA2136?logo=apache)](https://httpd.apache.org/)
[![MySQL](https://img.shields.io/badge/DB-MySQL_8-00758F?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](#-licença)

API RESTful de **gerenciamento de tarefas (CRUD)** escrita em **PHP 8.3+ puro**, com **Apache** e **MySQL**, estruturada em camadas (Controller, Repository, Model e Support).  
Sem frameworks, **sem Composer** e com foco em código limpo.

---

## ✨ Funcionalidades
- Criar, listar, detalhar, atualizar e excluir tarefas.  
- Respostas JSON padronizadas com códigos HTTP corretos (`201`, `204`, `404`, `422`).  
- Validação do título (obrigatório, máx. 255 caracteres).  
- Conexão segura via PDO (prepared statements, sem emulação).  
- Healthcheck: `/health` e `/health?deep`.  
- Pronto para rodar em **Docker + Docker Compose**.  

---

## ⚙️ Requisitos
- **Docker** e **Docker Compose** instalados  
- Porta **8080** livre no host (API)  
- Porta **3307** livre no host (MySQL mapeado)  

---

## 🚀 Como rodar

1. Clonar o repositório:
```
git clone https://github.com/<seu-usuario>/<seu-repo>.git
cd <seu-repo>
```

2. Criar o `.env`:
```
cp .env.example .env
```

Exemplo de conteúdo:
```
APP_ENV=dev

DB_HOST=db
DB_PORT=3306
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=app

MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=app
MYSQL_USER=app
MYSQL_PASSWORD=app
```

3. Subir os containers:
```
docker compose up -d --build
```

4. Executar as migrations:
```
docker compose exec app php database/migrate.php
```

5. Testar o healthcheck:
```
curl http://localhost:8080/health
curl http://localhost:8080/health?deep
```

---

## 🧭 Endpoints
Base URL: `http://localhost:8080`

### ➕ Criar tarefa
```
curl -X POST http://localhost:8080/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Estudar PHP"}'
```
Resposta **201**:
```
{"id":1,"title":"Estudar PHP"}
```

### 📋 Listar tarefas
```
curl http://localhost:8080/tasks
```
Resposta **200**:
```
[
  {"id":1,"title":"Estudar PHP"}
]
```

### 🔎 Detalhar tarefa
```
curl http://localhost:8080/tasks/1
```
Resposta **200**:
```
{"id":1,"title":"Estudar PHP"}
```
Resposta **404**:
```
{"error":"Task não encontrada"}
```

### ✏️ Atualizar tarefa
```
curl -X PUT http://localhost:8080/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Estudar PHP 8.3"}'
```
Resposta **200**:
```
{"id":1,"title":"Estudar PHP 8.3"}
```

### ❌ Excluir tarefa
```
curl -X DELETE http://localhost:8080/tasks/1 -i
```
Resposta **204** (sem corpo).

---

## 🌐 CORS
Configurado em `public/index.php`:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type
```

---

## 🗄️ Banco de dados
Arquivo `database/migrations/001_create_tasks.sql`:
```
CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🔧 Desenvolvimento
- Rebuild rápido:
```
docker compose build app && docker compose up -d
```
- Logs:
```
docker compose logs -f app
```
- Entrar no container:
```
docker compose exec app bash
```

---

## ❗ Solução de problemas
- **`mb_strlen` não encontrado**  
  O `Dockerfile` já instala `mbstring`. Confirme:  
  ```
  RUN docker-php-ext-install pdo pdo_mysql mbstring
  ```

- **Erro de conexão ao MySQL**  
  O `Database` aplica retry (5 tentativas). Se precisar, ajuste em `src/Support/Database.php`.

- **Erro 404 em todas as rotas**  
  Verifique se o `DocumentRoot` aponta para `/public` e se o `.htaccess` está presente.

---

## 🧩 Decisões de implementação
- **Sem Composer** → carregamento via `require_once` no `bootstrap.php`.  
- **Repository Pattern** → `TaskRepository` (contrato) + `MysqlTaskRepository` (implementação PDO).  
- **Controller fino** → valida entrada, chama repositório e retorna JSON.  
- **Router próprio** → suporta parâmetros nomeados (`/tasks/{id}`).  
- **Helpers** → `Request` (parsing seguro de JSON) e `Response` (JSON + status codes).  

---

## 📄 Licença
Distribuído sob a **MIT License**.  
Veja o arquivo `LICENSE` para mais detalhes.
