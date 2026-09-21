<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Dashboard::index', ['filter' => 'session']);
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'session']);
$routes->get('calendar', 'Calendar::index', ['filter' => 'session']);

$routes->group('api', ['filter' => 'session'], static function ($routes) {

    $routes->get('events', 'Api\Event::index');

});

// Shield-Routen (Login, Logout usw.)
service('auth')->routes($routes);