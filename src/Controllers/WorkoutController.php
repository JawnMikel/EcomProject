<?php

namespace Gainz\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Workout Controller
 * Handles workout logging and retrieval
 */
class WorkoutController extends BaseController
{
    /**
     * List user's workouts
     */
    public function list(Request $request, Response $response): Response
    {
        try {
            // TODO: Get user from JWT token
            // TODO: Fetch workouts from database
            // TODO: Filter by date if provided

            return $this->jsonResponse($response, [
                'workouts' => []
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Failed to fetch workouts', 500);
        }
    }

    /**
     * Create new workout
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $this->getJsonBody($request);

            // TODO: Validate workout data
            // TODO: Save workout to database
            // TODO: Return created workout

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Workout created successfully',
                'workout_id' => null
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Failed to create workout', 500);
        }
    }

    /**
     * Get specific workout
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $workoutId = $args['id'];

            // TODO: Fetch workout by ID
            // TODO: Verify user ownership

            return $this->jsonResponse($response, [
                'workout' => null
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Workout not found', 404);
        }
    }
}
