<?php

namespace Encore\Admin\Traits;

trait LogRecord
{
    public function parselogRecord($record = [])
    {
        $record['skip'] = false;
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

  
}