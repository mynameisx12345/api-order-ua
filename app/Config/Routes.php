<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->group('api', function ($routes) {
    $routes->group('items', function ($routes) {
        $routes->get('productCategories', 'Products::productCategories');
        $routes->get('hotProducts', 'Products::hotProducts');
        $routes->get('products', 'Products::products');
        $routes->post('addToCart','Products::insertShoppingCart');
        $routes->get('retrieveCart', 'Products::retrieveCart');
        $routes->post('saveOrder', 'Products::saveOrder');
        $routes->get('searchProducts','Products::searchProducts');
        $routes->post('saveProduct','Products::saveProduct');
        $routes->post('saveCategory','Products::saveCategory');
        $routes->post('addHotProduct','Products::addHotProduct');
        $routes->get('getProducts', 'Products::getProducts');
        $routes->get('getSoldToday','Products::getSoldToday');
        $routes->post('like', 'Products::like');
        $routes->post('addComment', 'Products::addComment');
        $routes->post('editComment', 'Products::editComment');
        $routes->post('removeComment', 'Products::removeComment');
        $routes->get('getComments', 'Products::getComments');
        $routes->get('removeCategory', 'Products::removeCategory');
        $routes->get('removeHot', 'Products::removeHot');
    });

    $routes->group('users', function ($routes){
        $routes->post('login','UsersController::login');
        $routes->get('getUserList','UsersController::getUserList');
        $routes->post('addUser','UsersController::addUser');
        $routes->post('updateUser','UsersController::updateUser');
    });

    $routes->group('orders', function ($routes){
        $routes->get('getOrders','Products::getOrders');
        $routes->get('getOrdersDetailed', 'Products::getOrdersDetailed');
        $routes->post('updateOrderStatus', 'Products::updateOrderStatus');
    });

    $routes->group('reports', function ($routes){
        $routes->get('reportDailySales','Products::reportDailySales');
    });
});
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}


