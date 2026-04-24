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

        // Membres
        $routes->get('members',                         'Admin\MembersController::index');
        $routes->get('members/create',                  'Admin\MembersController::create');
        $routes->post('members',                        'Admin\MembersController::store');
        $routes->get('members/(:num)/edit',             'Admin\MembersController::edit/$1');
        $routes->post('members/(:num)/update',          'Admin\MembersController::update/$1');
        $routes->post('members/(:num)/delete',          'Admin\MembersController::delete/$1');
        $routes->post('members/(:num)/toggle',          'Admin\MembersController::toggle/$1');

        // Paiements membres
        $routes->get('members/(:num)/payments',         'Admin\MemberPaymentsController::index/$1');
        $routes->get('members/(:num)/payments/add',              'Admin\MemberPaymentsController::create/$1');
        $routes->post('members/(:num)/payments',                 'Admin\MemberPaymentsController::store/$1');
        $routes->get('members/(:num)/payments/(:num)/edit',      'Admin\MemberPaymentsController::edit/$1/$2');
        $routes->post('members/(:num)/payments/(:num)/update',   'Admin\MemberPaymentsController::update/$1/$2');
        $routes->post('members/(:num)/payments/(:num)/delete',   'Admin\MemberPaymentsController::delete/$1/$2');

        // Trésorerie — paiements
        $routes->get('treasury', 'Admin\TreasuryController::index');

        // Trésorerie — enveloppes de caisse
        $routes->get ('treasury/envelopes',                'Admin\TreasuryEnvelopesController::index');
        $routes->get ('treasury/envelopes/create',         'Admin\TreasuryEnvelopesController::create');
        $routes->post('treasury/envelopes',                'Admin\TreasuryEnvelopesController::store');
        $routes->get ('treasury/envelopes/(:num)/edit',    'Admin\TreasuryEnvelopesController::edit/$1');
        $routes->post('treasury/envelopes/(:num)/update',  'Admin\TreasuryEnvelopesController::update/$1');
        $routes->post('treasury/envelopes/(:num)/delete',  'Admin\TreasuryEnvelopesController::delete/$1');

        // Clés membres
        $routes->post('members/(:num)/keys',                      'Admin\MembersController::storeKey/$1');
        $routes->post('members/(:num)/keys/(:num)/return',        'Admin\MembersController::returnKey/$1/$2');

        // Utilisateurs admin
        $routes->get('users',                            'Admin\AdminUsersController::index');
        $routes->get('users/pick-member',                'Admin\AdminUsersController::pickMember');
        $routes->get('users/from-member/(:num)',         'Admin\AdminUsersController::createForMember/$1');
        $routes->post('users',                           'Admin\AdminUsersController::store');
        $routes->get('users/create',                     'Admin\AdminUsersController::create');
        $routes->get('users/(:num)/edit',                'Admin\AdminUsersController::edit/$1');
        $routes->post('users/(:num)/update',             'Admin\AdminUsersController::update/$1');
        $routes->post('users/(:num)/delete',             'Admin\AdminUsersController::delete/$1');
        $routes->post('users/(:num)/toggle',             'Admin\AdminUsersController::toggle/$1');
    });
});
