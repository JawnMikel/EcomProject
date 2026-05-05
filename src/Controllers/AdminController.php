<?php

namespace Gainz\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Admin Controller
 * Handles admin-only operations
 */
class AdminController extends BaseController
{
    /**
     * Create new exercise
     * Admin only
     */
    public function createExercise(Request $request, Response $response): Response
    {
        try {
            // TODO: Verify admin role from JWT token

            $data = $this->getJsonBody($request);

            // TODO: Validate exercise data
            // TODO: Save exercise to database

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Exercise created successfully',
                'exercise_id' => null
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Failed to create exercise', 500);
        }
    }

    /**
     * Create training program
     * Admin only
     */
    public function createProgram(Request $request, Response $response): Response
    {
        try {
            // TODO: Verify admin role from JWT token

            $data = $this->getJsonBody($request);

            // TODO: Validate program data
            // TODO: Save program to database

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Program created successfully',
                'program_id' => null
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Failed to create program', 500);
        }
    }
}
