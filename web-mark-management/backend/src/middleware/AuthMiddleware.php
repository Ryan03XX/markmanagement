<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware
{
    private $secret;
    private $requiredRole;

    public function __construct($secret, $requiredRole = null)
    {
        $this->secret = $secret;
        $this->requiredRole = $requiredRole;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized();
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            $user = (array)$decoded->data;

            // ✅ 角色检查
            if ($this->requiredRole && $user['role'] !== $this->requiredRole) {
                return $this->unauthorized("Access denied for role: " . $user['role']);
            }

            // 添加用户信息到 request attribute 中
            $request = $request->withAttribute('user', $user);
            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorized($e->getMessage());
        }
    }

    private function unauthorized($msg = 'Unauthorized')
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $msg
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}
