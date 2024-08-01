<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', function($routes) {
    $routes->post('register', 'UserController::register');
    
    $routes->post('verify', 'UserController::verify');
    
    $routes->post('login', 'UserController::login');
});
