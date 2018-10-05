<?php
namespace Encore\Admin\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Encore\Admin\Grid;
use Encore\Admin\Models\LogReport;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Controllers\Tools\ExcelExpoter;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Layout\Content;
use Maatwebsite\Excel\Facades\Excel;

class MissedCallReportController extends Controller
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
            $content->description('Missedcalls Report');
            
            $content->body($this->grid());
//             $filter = $this->filter();
            
//             $content->row(new Box('Filter', $filter));
//             $data = $this->table();
//             $sumMissedCalls =  $data['count'];
//             $table1 = new Table($data['headers'], $data['rows']);
//             $content->body($table1);
//             $content->row(function ($row) use($sumMissedCalls){
//                 $row->column(12, new InfoBox('MissedCalls', 'file', 'red', '',$sumMissedCalls));
                
//             });
               
        });
    }
    
    protected function showFormParameters()
    {
       
      
    }
    
    protected function filter(){
        $form = new Form();
        $form->multipleSelect('Extintions')->options('/admin/auth/reports/destinationoption');
        $form->number('number', 'Number of calls');
        $form->dateTimeRange('date_time_start', 'date_time_end', 'Date Rang');
        return $form;
    }
    
    protected function table(){
        $parameters = request()->except(['_pjax', '_token']);
        //dd($parameters);
        $data = LogReport::selectRaw('count(id) as count_missed,extintion_missed_call,name_missed_call')->whereRaw("call_type = 'unanswered' and call_sub_type = 'queue_missed_call'")->groupBy(['name_missed_call','extintion_missed_call'])->get();
        $headers = ['Id', 'extintion', 'Name', 'Missed Calls',];
        $rows = [];
        $counter = 1;
        $sumMissedCalls = 0;
        foreach ($data as $row){
            $rowArray = [$counter,str_replace('Ext.','',$row->extintion_missed_call),$row->name_missed_call,$row->count_missed] ;
            $rows[] = $rowArray;
            $sumMissedCalls += $row->count_missed;
            
        }
        
        $contentData = ['rows'=>$rows,'headers'=>$headers,'count'=>$sumMissedCalls];
        
        
        return $contentData;
    }
    
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
       
        
        $table = Admin::grid(LogReport::class, function (Grid $grid){
            $user = Admin::user();
            $parameters = request()->except(['_pjax', '_token']);
//             dd($parameters);
            $countMissedCall = 0;
            if(isset($parameters['count_missed']) && $parameters['count_missed'] != ''){
                $countMissedCall = $parameters['count_missed'];
            }
            $toleranceCondition = '';
            if(isset($parameters['tolerance']) && $parameters['tolerance'] != '' && $parameters['ring_time'] != '' && $parameters['talk_time'] != '' ){
                
                $toleranceCondition = ' OR (total_waiting_time > "1970-01-01 '.$parameters['ring_time'] .'"  and  duration < "1970-01-01 '.$parameters['talk_time'] .'" ) ';
            } 
            $grid->model()->selectRaw('extintion_missed_call,name_missed_call,count(id) as count_missed')->whereRaw("(call_type = 'unanswered' and call_sub_type = 'queue_missed_call') $toleranceCondition ")->having('count_missed','>',$countMissedCall)->groupBy(['name_missed_call','extintion_missed_call']);
            
            $grid->setView('admin::grid.missedcall');
            $grid->extintion_missed_call('Ext.');
            $grid->name_missed_call('Name');
            $grid->count_missed('Count');
            $count = $this->table()['count'];
            $grid->filter(function (Filter $filter) {
                
                $filter->disableIdFilter();
                $filter->in('extintion_missed_call','Extintions')->multipleSelect('/admin/auth/reports/missedcallreport/extintionoption',[],[],'id1','text');
                $filter->where(function($query){
                    
                },'Number of calls','count_missed');
                
               $filter->between('time_start','Interval')->datetime();
               
               $filter->where(function($query){
                   
               },'Tolerance','tolerance')->checkbox(['1'=>'tolerance']);
               
               $filter->where(function($query){},'Ringing Time','ring_time')->time();
               $filter->where(function($query){},'Talking Time','talk_time')->time();
               
                

            });
                $exporter = new ExcelExpoter();
                
                $header = [
                    'Extintion',
                    'Name',
                    'Missed calls',
                ];
                $exporter->fileName('missedcallsreport')
                ->title(trans('reports.cards.excel'))
                ->tableColumns([
                    'count_missed',
                    'name_missed_call',
                    'extintion_missed_call',
                    
                    
                    
                ])
                ->header($header);
                
                $grid->exporter($exporter);
                
                $grid->disablePagination();
                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableRowSelector();
                Admin::script('$("a[class=export-selected]").remove();');
                Admin::script('$("a[class=current_page_export]").remove();');
                
                
        });
        
            return $table;
    }
    
    public function extintionOption(){
        $data = LogReport::selectRaw('extintion_missed_call as id1 ,extintion_missed_call as text')->whereRaw("call_type = 'unanswered' and call_sub_type = 'queue_missed_call'")->groupBy('text')->get();
        return $data;
    }
    
}
