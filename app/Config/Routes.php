<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Site public — Auth membres
$routes->get('connexion',   'Public\AuthController::login');
$routes->post('connexion',  'Public\AuthController::loginPost');
$routes->get('deconnexion', 'Public\AuthController::logout');
$routes->post('deconnexion','Public\AuthController::logout');

// Site public — Tableau hebdomadaire (lecture libre)
$routes->get('tableau',               'Public\ScheduleController::week');
$routes->get('tableau/(:num)/(:num)', 'Public\ScheduleController::week/$1/$2');

// Site public — Mon compte (membres connectés)
$routes->group('mon-compte', ['filter' => 'publicAuth'], static function ($routes) {
    $routes->get ('/',                'Public\AccountController::index');
    $routes->post('coordonnees',      'Public\AccountController::saveCoordonnees');
    $routes->post('mot-de-passe',     'Public\AccountController::savePassword');
    $routes->post('confidentialite',  'Public\AccountController::saveConfidentialite');
    $routes->post('toggle-privacy',   'Public\AccountController::togglePrivacy');
    $routes->post('upload-photo',     'Public\AccountController::uploadPhoto');
    $routes->post('delete-photo',     'Public\AccountController::deletePhoto');
});

// Site public — Actions protégées (membres connectés)
$routes->group('tableau', ['filter' => 'publicAuth'], static function ($routes) {
    $routes->post('arbitrage/(:num)/signup',  'Public\ScheduleController::signupArbitrage/$1');
    $routes->post('arbitrage/(:num)/cancel',  'Public\ScheduleController::cancelArbitrage/$1');
    $routes->post('arbitrage/(:num)/confirm', 'Public\ScheduleController::confirmArbitrage/$1');
    $routes->post('bar/signup',               'Public\ScheduleController::signupBar');
    $routes->post('bar/(:num)/cancel',        'Public\ScheduleController::cancelBar/$1');
});

// Site public
$routes->get('/', 'Public\HomeController::index');

// Le Club
$routes->get('club/histoire',         'Public\PagesController::clubHistoire');
$routes->get('club/comite',           'Public\PagesController::clubComite');
$routes->get('club/membres',          'Public\PagesController::clubMembres');
$routes->get('club/membres/(:num)',   'Public\PagesController::clubMembre/$1');
$routes->get('club/ecole-de-billard', 'Public\PagesController::ecoleBillard');
$routes->get('contact',               'Public\PagesController::contact');

// Actualités (publiques)
$routes->get('actualites',        'Public\PagesController::newsIndex');
$routes->get('actualites/(:any)', 'Public\PagesController::newsDetail/$1');

// Saison
$routes->get('saison/resultats',             'Public\PagesController::saisonResultats');
$routes->get('saison/coupe-des-regions/(:num)', 'Public\PagesController::cdrTeam/$1');
$routes->get('saison/intm/(:num)',             'Public\PagesController::intmTeam/$1');

// Archives (publiques)
$routes->get('archives/resultats',    'Public\PagesController::archivesResultats');
$routes->get('galeries',              'Public\GalleriesController::index');
$routes->get('galeries/(:segment)',   'Public\GalleriesController::show/$1');

// Archives Journal (accessible sans connexion — accordion masqué si non connecté)
$routes->get('archives/journal', 'Public\PagesController::archivesJournal');

// Documents utiles
$routes->get('documents',             'Public\PagesController::documents');
$routes->get('documents/statuts',     'Public\PagesController::documentsStatuts');
$routes->get('documents/roi',         'Public\PagesController::documentsRoi');
$routes->get('documents/rgpd',        'Public\PagesController::documentsRgpd');

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
        $routes->get ('change-password', 'Admin\AuthController::changePassword');
        $routes->post('change-password', 'Admin\AuthController::changePasswordPost');

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
        $routes->get('treasury/export', 'Admin\TreasuryController::export');
        $routes->get ('treasury/settings',      'Admin\TreasuryController::settings');
        $routes->post('treasury/settings/save', 'Admin\TreasuryController::saveSettings');

        // Trésorerie — enveloppes de caisse
        $routes->get ('treasury/envelopes',                'Admin\TreasuryEnvelopesController::index');
        $routes->get ('treasury/envelopes/export',         'Admin\TreasuryEnvelopesController::export');
        $routes->get ('treasury/envelopes/create',         'Admin\TreasuryEnvelopesController::create');
        $routes->post('treasury/envelopes',                'Admin\TreasuryEnvelopesController::store');
        $routes->get ('treasury/envelopes/(:num)/edit',    'Admin\TreasuryEnvelopesController::edit/$1');
        $routes->post('treasury/envelopes/(:num)/update',  'Admin\TreasuryEnvelopesController::update/$1');
        $routes->post('treasury/envelopes/(:num)/delete',  'Admin\TreasuryEnvelopesController::delete/$1');

        // Clés membres (depuis fiche membre)
        $routes->post('members/(:num)/keys',               'Admin\MembersController::storeKey/$1');
        $routes->post('members/(:num)/keys/(:num)/return', 'Admin\MembersController::returnKey/$1/$2');

        // Heures d'ouverture
        $routes->get ('opening-hours',               'Admin\OpeningHoursController::index');
        $routes->post('opening-hours/save',          'Admin\OpeningHoursController::save');

        // École de billard
        $routes->get ('school',      'Admin\SchoolController::index');
        $routes->post('school/save', 'Admin\SchoolController::save');

        // Clés du club (page centrale)
        $routes->get ('club-keys',                   'Admin\ClubKeysController::index');
        $routes->post('club-keys',                   'Admin\ClubKeysController::store');
        $routes->post('club-keys/(:num)/assign',     'Admin\ClubKeysController::assign/$1');
        $routes->post('club-keys/(:num)/return',     'Admin\ClubKeysController::returnKey/$1');
        $routes->post('club-keys/(:num)/delete',     'Admin\ClubKeysController::delete/$1');

        // Journal "Partie Libre"
        $routes->get ('journal',                   'Admin\JournalController::index');
        $routes->get ('journal/create',            'Admin\JournalController::create');
        $routes->post('journal',                   'Admin\JournalController::store');
        $routes->get ('journal/(:num)/edit',       'Admin\JournalController::edit/$1');
        $routes->post('journal/(:num)/update',     'Admin\JournalController::update/$1');
        $routes->post('journal/(:num)/delete',     'Admin\JournalController::delete/$1');

        // Galeries photos
        $routes->get ('galleries',                              'Admin\GalleriesController::index');
        $routes->get ('galleries/create',                       'Admin\GalleriesController::create');
        $routes->post('galleries',                              'Admin\GalleriesController::store');
        $routes->get ('galleries/(:num)/edit',                  'Admin\GalleriesController::edit/$1');
        $routes->post('galleries/(:num)/update',                'Admin\GalleriesController::update/$1');
        $routes->post('galleries/(:num)/delete',                'Admin\GalleriesController::delete/$1');
        $routes->get ('galleries/(:num)/photos',                'Admin\GalleriesController::show/$1');
        $routes->post('galleries/(:num)/photos/upload',         'Admin\GalleriesController::uploadPhotos/$1');
        $routes->post('galleries/(:num)/photos/(:num)/delete',  'Admin\GalleriesController::deletePhoto/$1/$2');
        $routes->post('galleries/(:num)/photos/(:num)/cover',   'Admin\GalleriesController::setCover/$1/$2');

        // Résultats sportifs individuels
        $routes->get ('sport-results',                   'Admin\SportResultsController::index');
        $routes->get ('sport-results/create',            'Admin\SportResultsController::create');
        $routes->post('sport-results',                   'Admin\SportResultsController::store');
        $routes->get ('sport-results/(:num)/edit',       'Admin\SportResultsController::edit/$1');
        $routes->post('sport-results/(:num)/update',     'Admin\SportResultsController::update/$1');
        $routes->post('sport-results/(:num)/delete',     'Admin\SportResultsController::delete/$1');

        // Actualités
        $routes->get ('news',                   'Admin\NewsController::index');
        $routes->get ('news/create',            'Admin\NewsController::create');
        $routes->post('news',                   'Admin\NewsController::store');
        $routes->get ('news/(:num)/edit',       'Admin\NewsController::edit/$1');
        $routes->post('news/(:num)/update',     'Admin\NewsController::update/$1');
        $routes->post('news/(:num)/delete',     'Admin\NewsController::delete/$1');
        $routes->post('news/(:num)/toggle',     'Admin\NewsController::toggle/$1');

        // Coupe des Régions (CDR)
        $routes->get ('cdr',                   'Admin\CdrController::index');
        $routes->get ('cdr/create',            'Admin\CdrController::create');
        $routes->post('cdr',                   'Admin\CdrController::store');
        $routes->get ('cdr/(:num)/edit',       'Admin\CdrController::edit/$1');
        $routes->post('cdr/(:num)/update',     'Admin\CdrController::update/$1');
        $routes->post('cdr/(:num)/delete',     'Admin\CdrController::delete/$1');

        // I.N.T.M.
        $routes->get ('intm',                   'Admin\IntmController::index');
        $routes->get ('intm/create',            'Admin\IntmController::create');
        $routes->post('intm',                   'Admin\IntmController::store');
        $routes->get ('intm/(:num)/edit',       'Admin\IntmController::edit/$1');
        $routes->post('intm/(:num)/update',     'Admin\IntmController::update/$1');
        $routes->post('intm/(:num)/delete',     'Admin\IntmController::delete/$1');

        // Tableau des rencontres
        $routes->get ('schedule',                        'Admin\ScheduleController::index');
        $routes->get ('schedule/(:num)/(:num)',          'Admin\ScheduleController::index/$1/$2');
        $routes->get ('schedule/create',                 'Admin\ScheduleController::create');
        $routes->post('schedule',                        'Admin\ScheduleController::store');
        $routes->get ('schedule/(:num)/edit',            'Admin\ScheduleController::edit/$1');
        $routes->post('schedule/(:num)/update',          'Admin\ScheduleController::update/$1');
        $routes->post('schedule/(:num)/delete',          'Admin\ScheduleController::delete/$1');
        $routes->post('schedule/(:num)/referee',         'Admin\ScheduleController::designateReferee/$1');
        $routes->post('schedule/(:num)/referee/remove',  'Admin\ScheduleController::removeReferee/$1');
        $routes->post('schedule/bar/assign',             'Admin\ScheduleController::assignBar');

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
