<?php
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use DI\Container;

return function (App $app) {
    $container = new Container();

    // Set up PDO for MySQL
    $container->set('db', function () {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    });

    AppFactory::setContainer($container);
    $app = AppFactory::create();
    return $app;
};
