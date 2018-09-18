<?php

namespace Encore\Admin\Models;

use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LogReport.
 *
 * @property LogReport[] $LogReport
 */
class LogReport extends Model 
{
    use AdminBuilder;

   protected $fillable = ["historyid","callid","duration","time_start","time_answered","time_end","reason_terminated","from_no","to_no","from_dn","to_dn","dial_no","reason_changed","final_number","final_dn","chain","from_type","to_type","final_type","from_dispname","to_dispname","final_dispname","call_type","call_sub_type"];

    
    protected $appends = [];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable('log_report');

        parent::__construct($attributes);
    }
}