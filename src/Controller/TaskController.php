<?php
declare(strict_types=1);

namespace App\Controller;

use App\Support\Request;
use App\Support\Response;
use App\Repository\TaskRepository;

final class TaskController
{
    public function __construct(private TaskRepository $repo) {}

    public function index(Request $req): void
    {
        Response::json($this->repo->all());
    }

    public function show(Request $req): void
    {
        $id = (int) $req->param('id');

        $task = $this->repo->find($id);
        if (!$task) {
            Response::notFound('Task não encontrada');
            return;
        }

        Response::json($task);
    }

    public function store(Request $req): void
    {
        $body  = $req->json() ?? [];
        $title = trim((string) ($body['title'] ?? ''));

        if ($title === '' || mb_strlen($title) > 255) {
            Response::unprocessable('Título inválido', ['title' => 'obrigatório, até 255 chars']);
            return;
        }

        $id = $this->repo->create(['title' => $title]);
        Response::json(['id' => $id, 'title' => $title], 201);
    }

    public function update(Request $req): void
    {
        $id = (int) $req->param('id');

        if (!$this->repo->find($id)) {
            Response::notFound('Task não encontrada');
            return;
        }

        $body  = $req->json() ?? [];
        $title = trim((string) ($body['title'] ?? ''));

        if ($title === '' || mb_strlen($title) > 255) {
            Response::unprocessable('Título inválido', ['title' => 'obrigatório, até 255 chars']);
            return;
        }

        $this->repo->update($id, ['title' => $title]);
        Response::json(['id' => $id, 'title' => $title]);
    }

    public function destroy(Request $req): void
    {
        $id = (int) $req->param('id');

        if (!$this->repo->find($id)) {
            Response::notFound('Task não encontrada');
            return;
        }

        $this->repo->delete($id);
        Response::noContent();
    }
}
