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
    $routes->get('/dashboard/debug', 'DashboardController::debug');
    
    // Input Revenue (combined Target & Realisasi)
    $routes->get('/input', 'InputController::index');
    $routes->post('/input/store', 'InputController::store');
    
    // User Management
    $routes->get('/users', 'UserController::index');
    $routes->post('/users/store', 'UserController::store');
    $routes->post('/users/update/(:num)', 'UserController::update/$1');
    $routes->get('/users/delete/(:num)', 'UserController::delete/$1');
    
    // Google Sheets Sync
    $routes->get('/sync', 'SyncController::index');
    $routes->post('/sync/run', 'SyncController::run');
});

// 404 Override
$routes->set404Override(function () {
    return view('errors/html/error_404');
});
