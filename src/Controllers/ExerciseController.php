<?php

namespace Gainz\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Exercise Controller
 * Handles exercise library and information
 */
class ExerciseController extends BaseController
{
    /**
     * List all exercises
     */
    public function list(Request $request, Response $response): Response
    {
        try {
            // TODO: Filter by category if provided
            // TODO: Filter by muscle group if provided
            // TODO: Fetch from database with pagination

            return $this->jsonResponse($response, [
                'exercises' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => 20
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Failed to fetch exercises', 500);
        }
    }

    /**
     * Get specific exercise details
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $exerciseId = $args['id'];

            // TODO: Fetch exercise details from database
            // TODO: Include variations and tips

            return $this->jsonResponse($response, [
                'exercise' => null
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Exercise not found', 404);
        }
    }
}
