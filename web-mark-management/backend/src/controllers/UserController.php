<?php
// namespace App\Controllers;

// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;

// class UserController
// {
//     protected $db;

//     public function __construct()
//     {
//         $host = $_ENV['DB_HOST'];
//         $dbname = $_ENV['DB_NAME'];
//         $user = $_ENV['DB_USER'];
//         $pass = $_ENV['DB_PASS'];
//         $this->db = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
//     }

//     public function getAllUsers(Request $request, Response $response): Response
//     {
//         $stmt = $this->db->query("SELECT id, name, matric_no, role FROM users");
//         $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

//         $response->getBody()->write(json_encode($users));
//         return $response->withHeader('Content-Type', 'application/json');
//     }
// }
