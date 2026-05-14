<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ProfileController
{
    private string $basePath = '/EcomProject/public';

    public function __construct(
        private Twig $view,
        private UserModel $userModel,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $user = $this->userModel->findById($_SESSION['user_id']);

        return $this->view->render($response, 'dashboard/profile.twig', [
            'username' => $_SESSION['username'],
            'user' => $user,
            'role' => $_SESSION['user_role'],
            'title' => 'My Profile'
        ]);
    }

    public function update(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $data = (array) $request->getParsedBody();

        // Clean empty values
        $profileData = [];
        foreach ($data as $key => $value) {
            if ($value !== '' && $value !== null) {
                $profileData[$key] = $value;
            }
        }

        $success = $this->userModel->updateProfile($_SESSION['user_id'], $profileData);

        if ($success) {
            $_SESSION['flash_success'] = 'Profile updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update profile. Please try again.';
        }

        return $response->withHeader('Location', $this->basePath . '/profile')->withStatus(302);
    }
}