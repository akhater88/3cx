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
            $content->description('Missed calls Report');
            
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
            $grid->model()->selectRaw('from_dispname,final_dispname,to_no,final_number,extintion_missed_call,name_missed_call,count(id) as count_missed')->whereRaw("(call_type = 'unanswered' and call_sub_type = 'queue_missed_call') $toleranceCondition ")->having('count_missed','>',$countMissedCall)->groupBy(['name_missed_call','extintion_missed_call']);//->having('count_missed','>',$countMissedCall);
            
            $grid->setView('admin::grid.missedcall');
            $grid->column('Extentions')->display(function(){
                $extintion = $this->extintion_missed_call;
                if($extintion == ''){
                    $extintion = $this->final_number;
                }
                if($extintion == ''){
                    $extintion = $this->to_no;
                }
                return str_replace('Ext.','', $extintion);
            });//extintion_missed_call('Extintions');
            $grid->column('Name')->display(function(){
                $name = $this->name_missed_call;
                if($name == ''){
                    $name = $this->final_dispname;
                }
                if($name == ''){
                    $name = $this->to_dispname;
                }
                return $name;
            });//name_missed_call('Name');
            $grid->count_missed('Count');
            $grid->filter(function (Filter $filter) {
                
                $filter->disableIdFilter();
                $filter->in('extintion_missed_call','Extentions')->multipleSelect('/admin/auth/reports/missedcallreport/extintionoption',[],[],'id1','text');
                $filter->where(function($query){
                    
                },'Number of calls','count_missed');
                
               $filter->between('time_start','Interval')->datetime();
               
               $filter->where(function($query){
                   
               },'Tolerance','tolerance')->checkbox(['1'=>'tolerance']);
               
               $filter->where(function($query){},'Ringing Time more than','ring_time')->time();
               $filter->where(function($query){},'Talking Time less than','talk_time')->time();
               
                

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
