<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Dashboard::index', ['filter' => 'session']);
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'session']);
$routes->get('calendar', 'Calendar::index', ['filter' => 'session']);

// Shield-Routen (Login, Logout usw.)
service('auth')->routes($routes);