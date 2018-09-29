<?php
namespace Encore\Admin\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Encore\Admin\Grid;
use Encore\Admin\Models\LogReport;
use Encore\Admin\Widgets\InfoBox;
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
            $content->description('Missed Call Report');
            
            $content->body($this->grid());
            $content->row(function ($row) {
                $row->column(12, new InfoBox('MissedCalls', 'tel', 'red', '', '1020'));
            });
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
            $user = Admin::user();
            $grid->model()->selectRaw('count(id) as count_missed,extintion_missed_call,name_missed_call')->whereRaw("call_type = 'unanswered' and call_sub_type = 'queue_missed_call'")->groupBy(['name_missed_call','extintion_missed_call']);
            $grid->extintion_missed_call('Ext.');
            $grid->name_missed_call('Name');
            $grid->count_missed('Count');
            
            
            
            $grid->filter(function (Filter $filter) {
                
                $filter->disableIdFilter();
                
                $current = Carbon::now();
                
                $filter->where(function ($query) {
                    $input = $this->input;
                    
                    $query->whereDate('sub_order_items.created_at', '>=', $input . ' 00:00:00');
                }, trans('reports.cards.filter.from_date'))
                ->date()
                ->default($current->subDays(7)
                    ->toDateString());
                
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereDate('sub_order_items.created_at', '<=', $input . ' 23:59:59');
                }, trans('reports.cards.filter.to_date'))
                ->date()
                ->default($current->addDays(6)
                    ->toDateString());
                
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('product', function ($query) use ($input) {
                        $query->where('products.country_id', $input);
                    });
                }, trans('reports.cards.filter.country'))
                ->select(Country::all()->pluck('name', 'id'));
                
                $filter->in('product.id', trans('reports.cards.filter.product'))
                ->multipleSelect(Product::all()->pluck('name', 'id'));
            });
//                 $exporter = new ExcelExpoter();
                
//                 $header = [
//                     trans('reports.cards.excel_headers.name'),
//                     trans('reports.cards.excel_headers.average_load_amount'),
//                     trans('reports.cards.excel_headers.total_purchased_cards'),
//                 ];
//                 $exporter->fileName(trans('reports.cards.excel'))
//                 ->title(trans('reports.cards.excel'))
//                 ->tableColumns([
//                     'pname',
//                     'avg_load_amount',
//                     'total_purchased_cards',
                    
//                 ])
//                 ->header($header);
                
//                 $grid->exporter($exporter);
                
                $grid->disablePagination();
                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableRowSelector();
                Admin::script('$("a[class=export-selected]").remove();');
                
        });
    }
    
}
