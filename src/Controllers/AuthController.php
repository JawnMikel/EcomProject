<?php

namespace Gainz\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Authentication Controller
 * Handles user registration, login, and logout
 */
class AuthController extends BaseController
{
    /**
     * User registration
     * Requires: email, password, age, language
     */
    public function register(Request $request, Response $response): Response
    {
        try {
            $data = $this->getJsonBody($request);

            // Validate required fields
            if (empty($data['email']) || empty($data['password'])) {
                return $this->errorResponse($response, 'Email and password are required', 400);
            }

            // Validate age (minimum 16 years old)
            if (empty($data['age']) || $data['age'] < (int)$_ENV['MIN_USER_AGE']) {
                return $this->errorResponse(
                    $response,
                    'You must be at least ' . $_ENV['MIN_USER_AGE'] . ' years old',
                    400
                );
            }

            // TODO: Hash password and store in database
            // TODO: Send verification email
            // TODO: Implement 2FA setup

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Registration successful. Please check your email.',
                'user_id' => null // Will be returned after DB integration
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User login
     * Requires: email, password
     */
    public function login(Request $request, Response $response): Response
    {
        try {
            $data = $this->getJsonBody($request);

            if (empty($data['email']) || empty($data['password'])) {
                return $this->errorResponse($response, 'Email and password are required', 400);
            }

            // TODO: Verify credentials from database
            // TODO: Generate JWT token
            // TODO: Return token and user info

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Login successful',
                'token' => null // Will be JWT token after DB integration
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User logout
     */
    public function logout(Request $request, Response $response): Response
    {
        // TODO: Invalidate token if needed

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
