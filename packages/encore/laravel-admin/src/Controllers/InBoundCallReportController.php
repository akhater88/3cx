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
            $grid->customesource('Source');
            $grid->customedestination('Destination');
            $grid->to_dispname('Name');
            $grid->call_type('Status');
            $grid->call_sub_type('Status Call');
            $grid->customedurationcustome('Duration');
            $grid->custometotalwaitingtime('Total Waiting Time');
            $grid->custometotalcalltime('Total Call Time');
            
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
                
                $filter->between('time_start','Date & time')->datetime();
                
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

                $filter->betweenCustome('total_waiting_time', 'Total Waiting Time')->time();
                
                $filter->betweenCustome('total_call_time', 'Total Call Time')->time();
                
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