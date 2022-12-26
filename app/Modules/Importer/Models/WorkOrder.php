<?php

namespace App\Modules\Importer\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model {
    protected $table = 'work_order';
    protected $primaryKey = 'work_order_id';

    protected $fillable = array('work_order_number', 'external_id', 'priority','received_date','category','fin_loc');
}