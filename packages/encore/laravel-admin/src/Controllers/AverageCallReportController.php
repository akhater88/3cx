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

class AverageCallReportController extends Controller
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
            $content->description('Average number of calls Report');
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
       
        
        return Admin::grid(LogReport::class, function (Grid $grid){
            $parameters = request()->except(['_pjax', '_token']);
//                         dd($parameters);
                        $whereInCond = "";
                        $whereOutCond = "";
            if(isset($parameters['ext']) && $parameters['ext'] != ''){
                $extStr = implode("','",$parameters['ext']);
                $whereInCond = " AND extintion_inbound in ('$extStr') ";
                $whereOutCond = " AND extintion_outbound in ('$extStr') ";
            }
            $where = '';
            if(isset($parameters['status']) && $parameters['status'] != ''){
                $status = $parameters['status'];
                $where = '';
                switch ($status){
                    case 'answered':
                        $where = ' AND call_type = "answered" ';
                        break;
                    case 'unanswered':
                        $where = ' AND call_type = "unanswered" ';
                        break;
                    case 'abondont':
                        $where = ' AND call_sub_type = "abondont" ';
                        break;
                }
                
            }
            $whereFromDate = '';
            $whereToDate = '';
            if(isset($parameters['time_start']) && $parameters['time_start'] != ''){
                $timeArray = $parameters['time_start'];
                if($timeArray['start'] != null){
                    $whereFromDate = " AND time_start > '".$timeArray['start']."' ";
                }
                
                if($timeArray['end'] != null){
                    $whereToDate = " AND time_start < '".$timeArray['end']."' ";
                }
                
            }
            
            $dataIn = LogReport::selectRaw('extintion_inbound,final_dispname,to_dispname,count(id) as count_call')->whereRaw(' inbound_outbound_flag like "In%" '.$where.' '.$whereInCond.' '.$whereFromDate.' '.$whereToDate)->groupBy(['extintion_inbound'])->get();
            $dataOut = LogReport::selectRaw('extintion_outbound,from_dispname,count(id) as count_call')->whereRaw(' inbound_outbound_flag like "Out" Or inbound_outbound_flag like "%Out" '.$where.' '.$whereOutCond.' '.$whereFromDate.' '.$whereToDate)->groupBy(['extintion_outbound'])->get();
            $attrArray = ['dataIn' => $dataIn,'dataOut' => $dataOut];
            if(isset($parameters['calltype']) && $parameters['calltype'] != ''){
                if($parameters['calltype'] == '1'){
                    unset($attrArray['dataOut']);
                } 
                elseif($parameters['calltype'] == '2'){
                    unset($attrArray['dataIn']);
                }
            }
           
            
            $grid->setView('admin::grid.averagecall',$attrArray);
            
            $grid->disablePagination();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            
            $grid->filter(function (Filter $filter) {
                $filter->disableIdFilter();
                $current = Carbon::now();
                $filter->where(function($query){},'Call Type','calltype')->select(['0'=>'All','1'=>'Inbound','2'=>'Outbound']);
                $filter->where(function($query){},'Extintion','ext')->multipleSelect('/admin/auth/reports/averagecallreport/extintionoption',[],[],'idC','text');;//in('to_no','Destination')->multipleSelect('/admin/auth/reports/destinationoption',[],[],'idC','text');
                $filter->where(function($query){},"Status",'status')->select(['all'=>'All','answered'=>'Answered','unanswered'=>'Unanswered','abondont'=>'Abandoned']);
                $filter->between('time_start','Date & time')->datetime();
            });
            
            Admin::script('$("a[class=export-selected]").remove();');
            Admin::script('$("a[class=current_page_export]").remove();');
        });
    }
    
    
    
    
    public function extintionOption(){
        $data = LogReport::selectRaw('extintion_inbound as id1 ,extintion_inbound as text')->groupBy('text')->get();
        return $data;
    }
    
}
