<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AuthController
{
    private string $basePath = '/EcomProject/public';

    public function __construct(
        private Twig      $view,
        private UserModel $userModel,
    ) {}

    public function showLogin(Request $request, Response $response): Response
    {
        if (!empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/dashboard')->withStatus(302);
        }
        return $this->view->render($response, 'auth/login.twig', [
            'error' => $_SESSION['flash_error'] ?? null,
        ]);
    }

    public function login(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error']);
        $data     = (array) $request->getParsedBody();
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $user = $this->userModel->findByUsername($email);
        }

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Invalid credentials. Please check your email/username and password.';
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        return $response->withHeader('Location', $this->basePath . '/dashboard')->withStatus(302);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        if (!empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/dashboard')->withStatus(302);
        }
        return $this->view->render($response, 'auth/register.twig', [
            'error' => $_SESSION['flash_error'] ?? null,
        ]);
    }

    public function register(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error']);
        $data      = (array) $request->getParsedBody();
        $username  = trim($data['username']  ?? '');
        $email     = trim($data['email']     ?? '');
        $password  = $data['password']  ?? '';
        $confirm   = $data['confirm']   ?? '';
        $birthDate = $data['birth_date'] ?? '';

        // Basic validation
        $error = $this->validateRegistration($username, $email, $password, $confirm, $birthDate);
        if ($error) {
            $_SESSION['flash_error'] = $error;
            return $response->withHeader('Location', $this->basePath . '/register')->withStatus(302);
        }

        if ($this->userModel->emailExists($email)) {
            $_SESSION['flash_error'] = 'Email is already registered.';
            return $response->withHeader('Location', $this->basePath . '/register')->withStatus(302);
        }

        if ($this->userModel->usernameExists($username)) {
            $_SESSION['flash_error'] = 'Username is already taken.';
            return $response->withHeader('Location', $this->basePath . '/register')->withStatus(302);
        }

        $hash   = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->userModel->create($username, $email, $hash, $birthDate);

        $_SESSION['user_id']   = $userId;
        $_SESSION['username']  = $username;
        $_SESSION['user_role'] = 'user';

        return $response->withHeader('Location', $this->basePath . '/dashboard')->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        session_destroy();
        return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
    }

    private function validateRegistration(
        string $username,
        string $email,
        string $password,
        string $confirm,
        string $birthDate
    ): ?string {
        if (!$username || !$email || !$password || !$birthDate) {
            return 'All fields are required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address.';
        }
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            return 'Passwords do not match.';
        }
        // Age check (must be 16+)
        $birth = DateTime::createFromFormat('Y-m-d', $birthDate);
        if (!$birth) {
            return 'Invalid birth date.';
        }
        $age = (new DateTime())->diff($birth)->y;
        if ($age < 16) {
            return 'You must be at least 16 years old to register.';
        }
        return null;
    }
}
