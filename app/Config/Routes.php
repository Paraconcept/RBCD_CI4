<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Site public
$routes->get('/', 'Home::index');

// ----------------------------------------------------------------
// Administration
// ----------------------------------------------------------------
$routes->group('admin', static function ($routes) {

    // Auth (sans filtre)
    $routes->get('login',  'Admin\AuthController::login');
    $routes->post('login', 'Admin\AuthController::loginPost');
    $routes->get('logout', 'Admin\AuthController::logout');

    // Zone protégée
    $routes->group('', ['filter' => 'adminAuth'], static function ($routes) {
        $routes->get('dashboard', 'Admin\DashboardController::index');
        $routes->get('/',        'Admin\DashboardController::index');
    });
});
