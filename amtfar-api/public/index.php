<?php
// public/index.php
use DI\Container;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Initialize Eloquent Database connection
require __DIR__ . '/../config/database.php';

// Instantiate PHP-DI Container (optional but recommended for ADR)
// $container = new Container();
// AppFactory::setContainer($container);

// Instantiate App
$app = AppFactory::create();

// Add Error Middleware
$displayErrorDetails = $_ENV['APP_ENV'] !== 'production';
$app->addErrorMiddleware($displayErrorDetails, true, true);

// Body Parsing Middleware for JSON requests
$app->addBodyParsingMiddleware();

// Routing Middleware
$app->addRoutingMiddleware();

// Register Routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

// Add CORS Middleware
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Run App
$app->run();
