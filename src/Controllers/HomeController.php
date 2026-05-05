<?php

namespace Gainz\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Home Controller
 * Handles home page and general requests
 */
class HomeController extends BaseController
{
    /**
     * Display home page
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->jsonResponse($response, [
            'message' => 'Welcome to GAINZ - Fitness Tracking Application',
            'version' => '1.0.0',
            'endpoints' => [
                'auth' => '/register, /login, /logout',
                'workouts' => '/workouts',
                'exercises' => '/exercises'
            ]
        ]);
    }
}
