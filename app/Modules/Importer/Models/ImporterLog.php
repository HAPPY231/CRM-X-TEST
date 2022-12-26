<?php

namespace App\Modules\Importer\Models;

use Illuminate\Database\Eloquent\Model;

class ImporterLog extends Model {
    protected $table = 'importer_log';
    protected $primaryKey = 'id';
    public $timestamps=false;

    protected $fillable = array('type', 'entries_processed', 'entries_created');
}
