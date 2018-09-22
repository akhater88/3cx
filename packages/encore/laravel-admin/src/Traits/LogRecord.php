<?php

namespace Encore\Admin\Traits;

use DateTime;

trait LogRecord
{
    public function parselogRecord($record = [])
    {
        $record['skip'] = false;
        
        $record['total_waiting_time'] = '1970-01-01 '.$this->getTotalWaitingTime($record['time_start'], $record['time_end'] , $record['duration']);
        $record['total_call_time'] = '1970-01-01 '.$this->getTotalCallTime($record['time_start'], $record['time_end']);
        
        
        if(strtolower($record['from_type']) == 'line' && ((strtolower($record['to_type']) == 'queue' && $record['final_type'] == '' ) || strtolower($record['final_type']) == 'queue')){
            $record['call_type'] = 'unanswered';
            $record['call_sub_type'] = 'abondont';
        }
        elseif((strtolower($record['to_type'])=='vmail' && $record['final_type'] == '') || strtolower($record['final_type']) == 'vmail'){
            $record['call_type'] = 'unanswered';
            $record['call_sub_type'] = 'busy';
        }
        elseif(strtolower($record['from_type']) == 'queue' && (((strtolower($record['to_type']) == 'extension' &&  strtolower($record['final_type']) == '') || trtolower($record['final_type']) == 'extension') && !(strtolower($record['reason_terminated']) == 'terminatedbydst' || strtolower($record['reason_terminated']) == 'terminatebysrc' ))){
            $record['call_type'] = 'unanswered';
            $record['call_sub_type'] = 'queue_missed_call';
        }
        elseif((strtolower($record['from_type']) == 'queue' || strtolower($record['from_type']) == 'extension') && ((strtolower($record['to_type']) == 'extension' &&  strtolower($record['final_type']) == '') || strtolower($record['final_type']) == 'extension') && strtolower($record['reason_terminated']) == 'failed_cancelled' ){
            $record['call_type'] = 'unanswered';
            $record['call_sub_type'] = 'extension_miss_call';
        }
        elseif((strtolower($record['from_type']) == 'line' || strtolower($record['from_type']) == 'extension') && ((strtolower($record['to_type']) == 'extension' && strtolower($record['final_type']) == '') || strtolower($record['final_type']) == 'extension') && (strtolower($record['reason_terminated']) == 'terminatedbydst' || strtolower($record['reason_terminated']) == 'terminatebysrc' )){
            $record['call_type'] = 'answered';
            $record['call_sub_type'] = '---';
        }
        else{
            $record['skip'] = true;
        }
        return $record;
    }
    
    public function getTotalWaitingTime($time_start,$time_end,$duration){
        $time_start = new DateTime($time_start);
        $time_end = new DateTime($time_end);
        $diffTime = $time_start->diff($time_end);
        if($duration == ''){
            return $diffTime->format("%H:%I:%S");
        }
        $duration = strtotime($duration);
        $totalTime = strtotime($diffTime->format("%H:%I:%S"));
        $value = new DateTime();
        $value = ($totalTime - $duration);
        
        return date("H:i:s", $value);
    }
    
    public function getTotalCallTime($time_start, $time_end){
        $time_start = new DateTime($time_start);
        $time_end = new DateTime($time_end);
        $value = $time_start->diff($time_end);
        return $value->format("%H:%I:%S");
    }

  
}