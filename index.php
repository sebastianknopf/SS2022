<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

// global variables
$baseUrl = str_replace('/' . basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);

// create app, basic config
$app = AppFactory::create();
$app->setBasePath($baseUrl);

$app->addErrorMiddleware(true, false, false);

// index route
$app->get('/', function (Request $request, Response $response, $args) use ($baseUrl) {
    $routes = find_routes();

    $renderer = new PhpRenderer('./template');
    return $renderer->render($response, 'index.php', [
        'baseUrl' => $baseUrl,
        'routes' => $routes
    ]);
});

// ajax routes
$app->get('/tripDemandData', function (Request $request, Response $response, $args) use ($baseUrl) {
    $queryParams = $request->getQueryParams();

    $dateFrom = date('Ymd', strtotime($queryParams['dateFrom']));
    $dateUntil = date('Ymd', strtotime($queryParams['dateUntil']));
    $routeName = $queryParams['routeName'];
    $direction = $queryParams['direction'];
    $dayType = $queryParams['dayType'];

    $result = find_matching_trip_demand_data($dateFrom, $dateUntil, $routeName, $direction, $dayType);
    $response->getBody()->write(json_encode($result));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/mapDemandData', function (Request $request, Response $response, $args) use ($baseUrl) {
    $queryParams = $request->getQueryParams();

    $dateFrom = date('Ymd', strtotime($queryParams['dateFrom']));
    $dateUntil = date('Ymd', strtotime($queryParams['dateUntil']));
    $routeName = $queryParams['routeName'];
    $direction = $queryParams['direction'];
    $dayType = $queryParams['dayType'];

    $result = find_matching_map_demand_data($dateFrom, $dateUntil, $routeName, $direction, $dayType);
    $response->getBody()->write(json_encode($result));

    return $response->withHeader('Content-Type', 'application/json');
});


// run everything
$app->run();
