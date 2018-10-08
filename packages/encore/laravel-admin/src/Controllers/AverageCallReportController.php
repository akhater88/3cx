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
            $content->row('Inbond calls');
            $content->body($this->grid());
            $content->row('Outbond calls');
            $content->body($this->grid2());
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
            $grid->model()->selectRaw('final_number,to_no,final_dispname,from_dispname,count(id) as count_call')->whereOr('to_no','like','Ext.%')->whereOr('from_no','like','Ext.%')->groupBy(['final_number']);
            $grid->setView('admin::grid.averagecall');
            $grid->column('Extintions')->display(function(){
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
                $name = $this->final_dispname;
                if($name == ''){
                    $name = $this->from_dispname;
                }
                
                return $name;
            });
            $grid->disablePagination();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            Admin::script('$("a[class=export-selected]").remove();');
            Admin::script('$("a[class=current_page_export]").remove();');
        });
    }
    
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        
        return Admin::grid(LogReport::class, function (Grid $grid){
            $grid->model()->selectRaw('final_number,to_no,final_dispname,from_dispname,count(id) as count_call')->where('from_no','like','Ext.%')->groupBy(['final_number']);
            $grid->setView('admin::grid.missedcall');
            $grid->column('Extintions')->display(function(){
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
                $name = $this->final_dispname;
                if($name == ''){
                    $name = $this->from_dispname;
                }
                
                return $name;
            });
                $grid->disablePagination();
                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableRowSelector();
                Admin::script('$("a[class=export-selected]").remove();');
                Admin::script('$("a[class=current_page_export]").remove();');
        });
    }
    
    public function extintionOption(){
        $data = LogReport::selectRaw('extintion_missed_call as id1 ,extintion_missed_call as text')->whereRaw("call_type = 'unanswered' and call_sub_type = 'queue_missed_call'")->groupBy('text')->get();
        return $data;
    }
    
}
