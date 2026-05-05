<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController
{
    public function __construct(
        private Twig $view
    ) {}

    public function index(Request $request, Response $response): Response
    {
        // Simple security check: redirect to login if no session exists
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/EcomProject/public/login')->withStatus(302);
        }

        return $this->view->render($response, 'dashboard/index.twig', [
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['user_role'],
            'title'    => 'My Dashboard'
        ]);
    }
}