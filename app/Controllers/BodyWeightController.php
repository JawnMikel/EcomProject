<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BodyWeightEntryModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class BodyWeightController
{
    public function __construct(
        private Twig $view,
        private BodyWeightEntryModel $weightModel
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/EcomProject/public/login')->withStatus(302);
        }

        $entries = $this->weightModel->getRecentByUser((int) $userId);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return $this->view->render($response, 'dashboard/body_weight.twig', [
            'entries' => $entries,
            'success' => $flashSuccess,
            'error' => $flashError,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $date = trim($data['entry_date'] ?? '');
        $weight = trim($data['weight_value'] ?? '');

        if (!$date || !$weight) {
            $_SESSION['flash_error'] = 'Please provide date and weight.';
            return $response->withHeader('Location', '/EcomProject/public/body-weight')->withStatus(302);
        }

        if (!is_numeric($weight) || (float) $weight <= 0) {
            $_SESSION['flash_error'] = 'Enter a valid weight number.';
            return $response->withHeader('Location', '/EcomProject/public/body-weight')->withStatus(302);
        }

        try {
            $this->weightModel->addEntry((int) $_SESSION['user_id'], $date, (float) $weight);
            $_SESSION['flash_success'] = 'Body weight entry added successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to save the weight entry. Please try again.';
        }

        return $response->withHeader('Location', '/EcomProject/public/body-weight')->withStatus(302);
    }
}
