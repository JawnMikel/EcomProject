<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TrainingProgramModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ProgramController
{
    public function __construct(
        private Twig $view,
        private TrainingProgramModel $programModel
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/EcomProject/public/login')->withStatus(302);
        }

        $programs = $this->programModel->getByUser((int) $userId);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return $this->view->render($response, 'dashboard/programs.twig', [
            'programs' => $programs,
            'success' => $flashSuccess,
            'error' => $flashError,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        if (!$name) {
            $_SESSION['flash_error'] = 'Program name cannot be empty.';
            return $response->withHeader('Location', '/EcomProject/public/programs')->withStatus(302);
        }

        try {
            $this->programModel->create((int) $_SESSION['user_id'], $name, $description);
            $_SESSION['flash_success'] = 'Program created successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to create program. Please try again.';
        }

        return $response->withHeader('Location', '/EcomProject/public/programs')->withStatus(302);
    }
}
