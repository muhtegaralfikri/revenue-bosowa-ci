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
    
    // Revenue management routes
    $routes->get('/targets', 'TargetController::index');
    $routes->get('/targets/create', 'TargetController::create');
    $routes->post('/targets/store', 'TargetController::store');
    $routes->get('/targets/edit/(:num)', 'TargetController::edit/$1');
    $routes->post('/targets/update/(:num)', 'TargetController::update/$1');
    $routes->get('/targets/delete/(:num)', 'TargetController::delete/$1');
    
    $routes->get('/realizations', 'RealizationController::index');
    $routes->get('/realizations/create', 'RealizationController::create');
    $routes->post('/realizations/store', 'RealizationController::store');
    $routes->get('/realizations/edit/(:num)', 'RealizationController::edit/$1');
    $routes->post('/realizations/update/(:num)', 'RealizationController::update/$1');
    $routes->get('/realizations/delete/(:num)', 'RealizationController::delete/$1');
    
    // Google Sheets sync
    $routes->get('/sync', 'SyncController::index');
    $routes->post('/sync/run', 'SyncController::run');
});
