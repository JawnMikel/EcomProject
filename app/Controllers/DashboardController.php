<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController
{
    private string $basePath = '/EcomProject/public';

    public function __construct(
        private Twig      $view,
        private UserModel $userModel,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        // Simple security check: redirect to login if no session exists
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        // Redirect to onboarding if profile not completed
        $user = $this->userModel->findById((int) $_SESSION['user_id']);
        if ($user && empty($user['profile_completed'])) {
            return $response->withHeader('Location', $this->basePath . '/onboarding')->withStatus(302);
        }

        return $this->view->render($response, 'dashboard/index.twig', [
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['user_role'],
            'title'    => 'My Dashboard'
        ]);
    }
}
