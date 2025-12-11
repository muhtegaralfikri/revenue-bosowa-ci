<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public routes
$routes->get('/', 'AuthController::login');
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/logout', 'AuthController::logout');

// Protected routes (require authentication)
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/dashboard', 'DashboardController::index');
    
    // Input Revenue (combined Target & Realisasi)
    $routes->get('/input', 'InputController::index');
    $routes->post('/input/store', 'InputController::store');
});
