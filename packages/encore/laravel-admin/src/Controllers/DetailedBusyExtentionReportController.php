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

class DetailedBusyExtentionReportController extends Controller
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
            $content->description('Detailed Busy Extention Report');
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
            $grid->model()->where('call_sub_type','=','busy');
            $parameters = request()->except(['_pjax', '_token']);
            $whereIn = "";
            if(isset($parameters['ext']) && $parameters['ext'] != ''){
                $extStr = implode("','",$parameters['ext']);
                $whereIn = " AND dial_no in ('$extStr') ";
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
            
            $dataCallAnswered = LogReport::selectRaw('dial_no,count(id) as call_counter')->whereRaw(' call_sub_type = "busy" '.$whereIn.' '.$whereFromDate.' '.$whereToDate)->groupBy(['dial_no'])->get();
            $dataToGrid = [];
            foreach ($dataCallAnswered as $row){
                $dial_no = $row->dial_no;
                $callCounter = $row->call_counter;
                $nameObject = LogReport::selectRaw('from_dispname as name, from_no,')->whereRaw(' from_no = "Ext.'.$dial_no.'" and call_type = "answered" ')->get();
                if($nameObject == null){
                    $nameObject = LogReport::selectRaw('to_dispname as name')->whereRaw(' to_no = "Ext.'.$dial_no.'" and call_type = "answered" ')->first();
                    if($nameObject == null){
                        $nameObject = LogReport::selectRaw('final_dispname as name')->whereRaw(' final_number = "Ext.'.$dial_no.'" and call_type = "answered" ')->first();
                    }
                }
                
                $name = $nameObject->name;
                $dataToGrid[] = ['ext'=>$dial_no,'name'=>$name,'counter'=>$callCounter];
            }
            $grid->setView('admin::grid.busycall',['dataToGrid'=>$dataToGrid]);
            $grid->disablePagination();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            
            $grid->filter(function (Filter $filter) {
                $filter->disableIdFilter();
                $current = Carbon::now();
                $filter->where(function($query){},'Extintion','ext')->multipleSelect('/admin/auth/reports/busyextention/extintionoption',[],[],'idC','text');
                $filter->between('time_start','Date & time')->datetime();
            });
            
            Admin::script('$("a[class=export-selected]").remove();');
            Admin::script('$("a[class=current_page_export]").remove();');
        });
    }
    
    
    
    
    public function extintionOption(){
        $data = LogReport::selectRaw('dial_no idC,dial_no text')->whereRaw(' call_sub_type = "busy" ')->groupBy(['dial_no'])->get();
        return $data;
    }
    
}
