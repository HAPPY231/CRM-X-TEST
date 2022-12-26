<?php

use App\Modules\Importer\Http\Controllers\ImporterController;
use Illuminate\Support\Facades\Route;

// Render app page
use Illuminate\Broadcasting\BroadcastController;

$router->get('/', [\App\Http\Controllers\StartController::class, 'index']);


$modules = [
    'Address',
    'CustomerSettings',
    'History',
    'Invoice',
    'Person',
    'Service',
    'Type',
    'User',
    'UserSettings',
    'WorkOrder',
'Importer',
];

$path = realpath(__DIR__ . '/../app/Modules/');

foreach ($modules as $module) {
    require $path . '/' . $module . '/Http/routes.php';
}
