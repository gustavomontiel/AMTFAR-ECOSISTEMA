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

// Run App
$app->run();
