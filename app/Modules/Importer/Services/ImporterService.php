<?php

namespace App\Modules\Importer\Services;

use App\Modules\Importer\Repositories\ImporterRepository;
use Illuminate\Container\Container;

class ImporterService
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var ImporterRepository
     */
    protected $importer;

    /**
     * Initialize class parameters
     *
     * @param Container $app
     * @param ImporterRepository $importer
     */
    public function __construct(Container $app, ImporterRepository $importer)
    {
        $this->app = $app;
        $this->importer = $importer;
    }
}
