<?php
namespace Encore\Admin\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Layout\Content;
use Encore\Admin\Models\LogReport;
use DateTime;

class InBoundCallReportController extends Controller
{
    use ModelForm;
	
	 /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            
            $content->header('Reports 3cx');
            $content->description('In Bound Calls');
            $content->body($this->grid());
        });
    }    


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(LogReport::class, function (Grid $grid) {
            $grid->model()->where('to_no','like','Ext.%');
            $grid->time_start('Date / Time');
            $grid->final_number('Source')->display(function($value){
                if($value == ''){
                    $value = $this->to_no;
                }  
                return str_replace('Ext.','', $value);
            });
            
            $grid->from_no('Destination')->display(function($value){
                return str_replace('Ext.','', $value);
            });
            
            $grid->to_dispname('Name');
            $grid->call_type('Status');
            $grid->call_sub_type('Status Call');
            $grid->column('Duration')->display(function(){
                $duration = new DateTime($this->duration);
                return $duration->format("H:i:s");
            });
            $grid->column('Total Waiting Time')->display(function(){
                $time_start = new DateTime($this->time_start);
                $time_end = new DateTime($this->time_end);
                $diffTime = $time_start->diff($time_end);
                if($this->duration == ''){
                    return $diffTime->format("%H:%I:%S");
                }
                $duration = strtotime($this->duration);
                $totalTime = strtotime($diffTime->format("%H:%I:%S"));
                $value = new DateTime();
                $value = ($totalTime - $duration);
                
                return date("H:i:s", $value);//->format("H:i:s");
            });
            
            $grid->column('Total Call Time')->display(function(){
                $time_start = new DateTime($this->time_start);
                $time_end = new DateTime($this->time_end);
                $value = $time_start->diff($time_end);
                return $value->format("%H:%I:%S");
            });
                 
            $grid->filter(function (Filter $filter) {
                
                $filter->disableIdFilter();
                
                $current = Carbon::now();
                $filter->where(function($query){
                    switch ($this->input){
                        case 1:
                            $query->where('from_no','like','Ext.%');
                            break;
                        case 2:
                            $query->where('from_no','like','Ext.8%');
                            break;
                        case 3:
                            $query->whereRaw(" from_no in ('2','3','4','5','6') OR CHAR_LENGTH(from_no) > 10  ");
                            break;
                        case 4:
                            $query->whereRaw(" CHAR_LENGTH(from_no) > 10  ");
                            break;
                    }
                    
                    
                },'Source')->select(['1'=>'Internal','2'=>'Operator Queue','3'=>'External (National & International)','4'=>'Externa (international)']);
                $filter->where(function($query){
                    $query->where('from_no',$this->input);
                    
                },'Destination')->select('/admin/auth/reports/destinationoption');

                $filter->betweenCustome('duration', 'Duration')->time();
                
                $filter->where(function($query){
                    switch ($this->input){
                        case 'answered':
                            $query->where('call_type','answered');
                            break;
                        case 'unanswered':
                            $query->where('call_type','unanswered');
                            break;
                        case 'abondont':
                            $query->where("call_sub_type","abondont");
                            break;

                    }
                    
                },"Status")->select(['answered'=>'Answered','unanswered'=>'Unanswered','abondont'=>'Abandoned']);
//                 $filter->where(function($query){
//                     $input = 'Ext.'.$this->input;
//                     $query->whereRaw (" (final_number = '' AND to_no = '$input') OR final_number = '$input' ");
                    
//                 },'Source');
//                 $filter->where(function ($query) {
//                     $input = $this->input;
                    
//                     $query->whereDate('log_report.time_start', '>=', $input . ' 00:00:00');
//                 }, 'filter start date')
//                 ->date()
//                 ->default($current->subDays(7)
//                     ->toDateString());
                
//                 $filter->where(function ($query) {
//                     $input = $this->input;
//                     $query->whereDate('log_report.time_end', '<=', $input . ' 23:59:59');
//                 }, 'filter end date')
//                 ->date()
//                 ->default($current->addDays(6)
//                     ->toDateString());
            });
                
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableRowSelector();
               
        });
    }
    
    public function destinationoption(){
        $result = LogReport::where('to_no','like','Ext.%')->select('from_no as id',"from_no as text")->groupBy('text')->get();
        
        return $result;
    }
}