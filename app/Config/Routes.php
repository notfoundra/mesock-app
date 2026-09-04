<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

service('auth')->routes($routes);

$routes->group('', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->post('dashboard/update-status', 'DashboardController::updateStatus');
    $routes->post('dashboard/quick-create', 'DashboardController::quickCreate');

    $routes->group('projects', static function ($routes) {
        $routes->get('/', 'ProjectController::index');
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

    // Menu di bawah ini cuma bisa diakses tim IE (super akses)
    $routes->group('planning', ['filter' => 'teamaccess'], static function ($routes) {
        $routes->get('/', 'PlanningController::index');
    });

    $routes->group('evidence', ['filter' => 'teamaccess'], static function ($routes) {
        $routes->get('/', 'EvidenceController::index');
    });

    $routes->group('master', ['filter' => 'teamaccess'], static function ($routes) {
        $routes->get('/', 'MasterDataController::index');
        $routes->get('(:segment)', 'MasterDataController::index/$1');
        $routes->get('(:segment)/create', 'MasterDataController::create/$1');
        $routes->post('(:segment)/store', 'MasterDataController::store/$1');
        $routes->get('(:segment)/(:num)/edit', 'MasterDataController::edit/$1/$2');
        $routes->post('(:segment)/(:num)/update', 'MasterDataController::update/$1/$2');
        $routes->post('(:segment)/(:num)/delete', 'MasterDataController::delete/$1/$2');
    });

    $routes->group('users', ['filter' => 'teamaccess'], static function ($routes) {
        $routes->get('/', 'UserManagementController::index');
        $routes->get('(:num)/edit', 'UserManagementController::edit/$1');
        $routes->post('(:num)/update', 'UserManagementController::update/$1');
    });

    $routes->group('activity-logs', ['filter' => 'teamaccess'], static function ($routes) {
        $routes->get('/', 'ActivityLogController::index');
    });
});
