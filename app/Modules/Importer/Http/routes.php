<?php

$NS = MODULES_NS . 'Importer\Http\Controllers\\';

$router->name('importer.')->group(function () use ($router, $NS) {
    $router->get('importer', $NS . 'ImporterController@index');
    $router->get('import', $NS . 'ImporterController@import_work_orders');
    $router->get('import/file', $NS . 'ImporterController@import_work_orders');
    $router->get('import_log', $NS . 'ImporterController@importer_log');
    $router->get('import_new_file', $NS . 'ImporterController@import_file');
    $router->get('show_csv', $NS . 'ImporterController@show_csv');
    $router->post('save', $NS . 'ImporterController@save_file');
});

$router->resource('importer', $NS . 'ImporterController');
