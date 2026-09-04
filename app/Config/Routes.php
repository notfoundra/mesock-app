<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

service('auth')->routes($routes);

// Semua route di bawah ini wajib login (Shield session filter)
$routes->group('', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');

    $routes->group('projects', static function ($routes) {
        $routes->get('/', 'ProjectController::index');
    });

    $routes->group('planning', static function ($routes) {
        $routes->get('/', 'PlanningController::index');
    });

    $routes->group('tasks', static function ($routes) {
        $routes->get('/', 'TaskController::index');
        $routes->get('project/(:num)', 'TaskController::project/$1');
        $routes->post('project/(:num)/store', 'TaskController::store/$1');
        $routes->post('(:num)/toggle', 'TaskController::toggle/$1');
        $routes->post('(:num)/delete', 'TaskController::delete/$1');

        $routes->get('daily', 'DailyTaskController::index');
        $routes->post('daily/(:num)/toggle', 'DailyTaskController::toggle/$1');
        $routes->get('daily/templates', 'DailyTaskController::templates');
        $routes->post('daily/templates/store', 'DailyTaskController::templateStore');
        $routes->post('daily/templates/(:num)/toggle', 'DailyTaskController::templateToggle/$1');
        $routes->post('daily/templates/(:num)/delete', 'DailyTaskController::templateDelete/$1');
    });

    $routes->group('evidence', static function ($routes) {
        $routes->get('/', 'EvidenceController::index');
    });

    $routes->group('master', static function ($routes) {
        $routes->get('/', 'MasterDataController::index');
        $routes->get('(:segment)', 'MasterDataController::index/$1');
        $routes->get('(:segment)/create', 'MasterDataController::create/$1');
        $routes->post('(:segment)/store', 'MasterDataController::store/$1');
        $routes->get('(:segment)/(:num)/edit', 'MasterDataController::edit/$1/$2');
        $routes->post('(:segment)/(:num)/update', 'MasterDataController::update/$1/$2');
        $routes->post('(:segment)/(:num)/delete', 'MasterDataController::delete/$1/$2');
    });

    $routes->group('users', static function ($routes) {
        $routes->get('/', 'UserManagementController::index');
    });

    $routes->group('activity-logs', static function ($routes) {
        $routes->get('/', 'ActivityLogController::index');
    });
});
