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

class ReceivedCallReportController extends Controller
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
            $content->description('Received calls Report');
            
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
            //          
            $countReceived = 0;
            if(isset($parameters['count_received']) && $parameters['count_received'] != ''){
                $countReceived = $parameters['count_received'];
            }
            
            $grid->model()->selectRaw('final_number,to_no,to_dispname,final_dispname,count(id) as count_received')->whereRaw(" call_type = 'answered' ")->having('count_received','>',$countReceived)->groupBy(['final_number','final_number']);
            
            $grid->column('Extentions')->display(function(){
                $extintion = $this->final_number;
                if($extintion == ''){
                    $extintion = $this->to_no;
                }
                return str_replace('Ext.','', $extintion);
            });
            $grid->column('Name')->display(function(){
                    $name = $this->final_dispname;
                if($name == ''){
                    $name = $this->to_dispname;
                }
                return $name;
            });
            $grid->count_received('Count');
            $grid->filter(function (Filter $filter) {
                
                $filter->disableIdFilter();
                $filter->where(function($query){
                    switch ($this->input){
                        case 1:
                            $query->where('from_no','like','Ext.%');
                            break;
                        case 2:
                            $query->where('from_no','like','Ext.8%');
                            break;
                        case 3:
                            $query->whereRaw(" CHAR_LENGTH(from_no) > 4  and from_no not like 'Ext.%' ");
                            break;
                        case 4:
                            $query->whereRaw(" CHAR_LENGTH(from_no) > 10 and from_no not like 'Ext.%' ");
                            break;
                    }
                    
                    
                },'Source')->select(['1'=>'Internal','2'=>'Operator Queue','3'=>'External (National & International)','4'=>'External (international)']);
                $filter->in('final_number','Extentions')->multipleSelect('/admin/auth/reports/receivedcallreport/extintionoption',[],[],'id1','text');
                $filter->where(function($query){
                    
                },'Number of calls','count_received');
                
               $filter->between('time_start','Interval')->datetime();

            });
                $exporter = new ExcelExpoter();
                
                $header = [
                    'Extintion',
                    'Name',
                    'Received calls',
                ];
                $exporter->fileName('receivedcallsreport')
                ->title(trans('reports.cards.excel'))
                ->tableColumns([
                    'final_number',
                    'final_dispname',
                    'count_received',
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
        $data = LogReport::selectRaw('final_number as id1 ,final_number as text')->whereRaw(" call_type = 'answered' ")->groupBy('text')->get();
        return $data;
    }
    
}
