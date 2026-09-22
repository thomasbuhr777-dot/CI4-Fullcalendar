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
   $routes->post('events', 'Api\Event::create');

   $routes->get('events/(:num)', 'Api\Event::show/$1');
   $routes->post('events/(:num)', 'Api\Event::update/$1');
   $routes->delete('events/(:num)', 'Api\Event::delete/$1');

   $routes->post('events/(:num)/move', 'Api\Event::move/$1');

});

// Shield-Routen (Login, Logout usw.)
service('auth')->routes($routes);