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
use Encore\Admin\Controllers\Tools\ExcelExpoter;
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
            $content->description('Inbound calls');
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
            $grid->customedestination('Source');
            $grid->customesource('Destination');
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
                            $query->whereRaw(" CHAR_LENGTH(from_no) > 4  ");
                            break;
                        case 4:
                            $query->whereRaw(" CHAR_LENGTH(from_no) > 10  ");
                            break;
                    }
                    
                    
                },'Source')->select(['1'=>'Internal','2'=>'Operator Queue','3'=>'External (National & International)','4'=>'External (international)']);
                $filter->where(function($query){
                    $impolode = implode("','",$this->input);
                    $query->whereRaw("(final_number != '' and final_number in ('$impolode') ) or (final_number = '' and to_no in ('$impolode') )");
                },'Destination')->multipleSelect('/admin/auth/reports/destinationoption',[],[],'idC','text');;//in('to_no','Destination')->multipleSelect('/admin/auth/reports/destinationoption',[],[],'idC','text');

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
            // Expoter
            $exporter = new ExcelExpoter();
            $header = [
                'Date / Time',
                'Name',
                'Status',
                'Status Call',
                'Source',
                'Destination', 
                'Duration',
                'Total Waiting Time',
                'Total Call Time'
            ];
            $exporter->fileName('RedemptionReport')
            ->title('RedemptionReport')
            ->tableColumns([
               'time_start',
                'to_dispname',
               'call_type',
               'call_sub_type',
               'customesource',
               'customedestination',
               'customedurationcustome',
               'custometotalwaitingtime',
               'custometotalcalltime'
                
            ])
            ->header($header)->fileName('InBoundCallReport')->extension('csv');
            $grid->exporter($exporter);
            Admin::script('$("a[class=export-selected]").remove();');
               
        });
    }
    
    public function destinationoption(){
        $result = LogReport::where('to_no','like','Ext.%')->select('to_no as idC',"to_no as text")->groupBy('text')->get();
        
        return $result;
    }
}