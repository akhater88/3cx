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

class OutBoundCallReportController extends Controller
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
            $content->description('Outbound calls');
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
            $grid->model()->where('from_no','like','Ext.%');
            $grid->time_start('Date / Time');
            $grid->customedestination('Source');
            $grid->customesource('Destination');
            $grid->to_dispname('Name');
            //$grid->call_type('Status');
            $grid->call_sub_type('Status Call');
            $grid->customedurationcustome('Duration');
            $grid->custometotalwaitingtime('Total Waiting Time');
            $grid->custometotalcalltime('Total Call Time');
            
            $grid->filter(function (Filter $filter) {
                $filter->disableIdFilter();
                $current = Carbon::now();
                $filter->in('from_no','Source')->multipleSelect('/admin/auth/reports/outboundcallreport/destinationoption',[],[],'idC','text');
                
                $filter->where(function($query){
                    switch ($this->input){
                        case 1:
                            $query->whereRaw(' final_number like "Ext.%" or (final_number = "" and to_no like "Ext.%" )');
                            break;
                        case 2:
                            $query->whereRaw(' final_number like "Ext.8%" or (final_number = "" and to_no like "Ext.8%" )');
                            break;
                        case 3:
                            $query->whereRaw(' (CHAR_LENGTH(final_number) > 4 and final_number not like "Ext.%") or (final_number = "" and CHAR_LENGTH(to_no) > 4 and to_no not like "Ext.%" )  ');
                            break;
                        case 4:
                            $query->whereRaw(' (CHAR_LENGTH(final_number) > 9 and final_number not like "Ext.%" ) or (final_number = "" and CHAR_LENGTH(to_no) > 9 and to_no not like "Ext.%"  ) ');
                            break;
                    }
                    
                    
                },'Destination')->select(['1'=>'Internal','2'=>'Operator Queue','3'=>'External (National & International)','4'=>'External (international)']);
               
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
                ->header($header)->fileName('OutBoundCallReport')->extension('csv');
                $grid->exporter($exporter);
                Admin::script('$("a[class=export-selected]").remove();');
                
        });
    }
    
    public function destinationoption(){
        $result = LogReport::where('from_no','like','Ext.%')->select('from_no as idC',"from_no as text")->groupBy('text')->get();
        
        return $result;
    }
}