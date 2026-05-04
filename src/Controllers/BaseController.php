<?php

namespace Gainz\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */
class BaseController
{
    /**
     * Write JSON response
     */
    protected function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
        
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    /**
     * Write error response
     */
    protected function errorResponse(Response $response, string $message, int $status = 400): Response
    {
        return $this->jsonResponse($response, [
            'error' => true,
            'message' => $message
        ], $status);
    }

    /**
     * Get JSON body from request
     */
    protected function getJsonBody(Request $request): ?array
    {
        $body = $request->getBody()->getContents();
        return json_decode($body, true);
    }
}
