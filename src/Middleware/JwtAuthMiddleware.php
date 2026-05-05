<?php

namespace Gainz\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWT Authentication Middleware
 * Verifies JWT tokens in Authorization header
 */
class JwtAuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Get authorization header
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            // Allow some routes without authentication
            $path = $request->getUri()->getPath();
            if ($this->isPublicRoute($path)) {
                return $handler->handle($request);
            }

            // Return 401 for protected routes
            return $this->unauthorizedResponse();
        }

        try {
            // Extract token from "Bearer <token>"
            if (strpos($authHeader, 'Bearer ') !== 0) {
                return $this->unauthorizedResponse();
            }

            $token = substr($authHeader, 7);

            // Verify token
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']));

            // Add decoded token to request
            $request = $request->withAttribute('user', $decoded);

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse();
        }
    }

    /**
     * Check if route is public (no authentication required)
     */
    private function isPublicRoute(string $path): bool
    {
        $publicRoutes = [
            '/',
            '/register',
            '/login',
            '/exercises', // Allow listing exercises without auth
        ];

        foreach ($publicRoutes as $route) {
            if (strpos($path, $route) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return 401 Unauthorized response
     */
    private function unauthorizedResponse(): Response
    {
        $response = new \Slim\Psr7\Response();
        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);

        $response->getBody()->write(json_encode([
            'error' => true,
            'message' => 'Unauthorized - Invalid or missing token'
        ]));

        return $response;
    }
}
