<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ExerciseModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ExerciseController
{
    private string $basePath = '/EcomProject/public';

    public function __construct(
        private Twig $view,
        private ExerciseModel $exerciseModel,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $category = $request->getQueryParams()['category'] ?? null;
        $difficulty = $request->getQueryParams()['difficulty'] ?? null;

        $exercises = $this->exerciseModel->getAll($category, $difficulty);
        $categories = $this->exerciseModel->getCategories();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return $this->view->render($response, 'exercises/index.twig', [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'title' => 'Exercise Library',
            'exercises' => $exercises,
            'categories' => $categories,
            'selected_category' => $category,
            'selected_difficulty' => $difficulty,
            'success' => $flashSuccess,
            'error' => $flashError,
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $exercise = $this->exerciseModel->findById($id);

        if (!$exercise) {
            return $response->withHeader('Location', $this->basePath . '/exercises')->withStatus(302);
        }

        return $this->view->render($response, 'exercises/show.twig', [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'title' => $exercise['name'],
            'exercise' => $exercise,
        ]);
    }

    public function search(Request $request, Response $response): Response
    {
        $query = $request->getQueryParams()['q'] ?? '';
        $exercises = $this->exerciseModel->search($query);

        $response->getBody()->write(json_encode($exercises));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $name = trim($data['name'] ?? '');
        $categoryId = (int) ($data['category_id'] ?? 1);
        $difficulty = $data['difficulty'] ?? 'beginner';
        $description = trim($data['description'] ?? '');
        $equipment = trim($data['equipment'] ?? '');

        if (!$name) {
            $_SESSION['flash_error'] = 'Exercise name is required.';
            return $response->withHeader('Location', $this->basePath . '/exercises')->withStatus(302);
        }

        try {
            $this->exerciseModel->create($name, $categoryId, $difficulty, $description, $equipment);
            $_SESSION['flash_success'] = 'Exercise created successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to create exercise.';
        }

        return $response->withHeader('Location', $this->basePath . '/exercises')->withStatus(302);
    }
}