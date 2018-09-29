<?php

namespace Encore\Admin\Models;

use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Database\Eloquent\Model;
use DateTime;

/**
 * Class LogReport.
 *
 * @property LogReport[] $LogReport
 */
class LogReport extends Model 
{
    use AdminBuilder;

    protected $fillable = ['name_missed_call','extintion_missed_call',"total_waiting_time","total_call_time","historyid","callid","duration","time_start","time_answered","time_end","reason_terminated","from_no","to_no","from_dn","to_dn","dial_no","reason_changed","final_number","final_dn","chain","from_type","to_type","final_type","from_dispname","to_dispname","final_dispname","call_type","call_sub_type"];

    
   protected $appends = ['customesource','customedestination','customedurationcustome','custometotalwaitingtime','custometotalcalltime'];

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
    
    public function getCustomesourceAttribute()
    {
        $value = $this->final_number;
        if($value == ''){
            $value = $this->to_no;
        }
        return str_replace('Ext.','', $value);
    }
    
    public function getCustomedestinationAttribute()
    {
        $value = $this->from_no;
        return str_replace('Ext.','', $value);
    }
    
    public function getCustomedurationcustomeAttribute()
    {
        $duration = new DateTime($this->duration);
        return $duration->format("H:i:s");
    }
    
    public function getCustometotalwaitingtimeAttribute(){
        $totalWaitingTime = new DateTime($this->total_waiting_time);
        return $totalWaitingTime->format("H:i:s");
        
    }
    //total_call_time
    public function getCustometotalcalltimeAttribute(){
        $totalCallTime = new DateTime($this->total_call_time);
        return $totalCallTime->format("H:i:s");
    }
    
}