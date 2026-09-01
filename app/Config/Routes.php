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
    });

    $routes->group('evidence', static function ($routes) {
        $routes->get('/', 'EvidenceController::index');
    });

    $routes->group('master', static function ($routes) {
        $routes->get('/', 'MasterDataController::index');
    });

    $routes->group('users', static function ($routes) {
        $routes->get('/', 'UserManagementController::index');
    });

    $routes->group('activity-logs', static function ($routes) {
        $routes->get('/', 'ActivityLogController::index');
    });
});
