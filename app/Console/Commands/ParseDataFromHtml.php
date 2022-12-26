<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Importer\Http\Controllers\ImporterController;

class ParseDataFromHtml extends Command
{
    protected $signature = 'parse-data-from-html';

    protected $description = 'Parse data from work orders and inserts it to database work orders';

    public function handle()
    {
        ImporterController::index();
    }
}