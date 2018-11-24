<div class="box">
    @if(isset($title))
    <div class="box-header with-border">
        <h3 class="box-title"> {{ $title }}</h3>
    </div>
    @endif

    <div class="box-header with-border">
        <div class="pull-right">
            {!! $grid->renderExportButton() !!}
            {!! $grid->renderCreateButton() !!}
        </div>
        <span>
            {!! $grid->renderHeaderTools() !!}
        </span>
    </div>

    {!! $grid->renderFilter() !!}

    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    @foreach($grid->columns() as $column)
                    <th>{{$column->getLabel()}}{!! $column->sorter() !!}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
             @section('content')
            <?php  $sum = 0; ?>  
            @endsection
                @foreach($grid->rows() as $row)
                		 
                <tr {!! $row->getRowAttributes() !!}>
                    @foreach($grid->columnNames as $name)
                    <td {!! $row->getColumnAttributes($name) !!}>
                        {!! $row->column($name) !!}
                        @if($name == 'count_missed')
                        @section('content')
                        		<?php  $sum += $row->column($name) ?>
                        	 @endsection
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            
                <tr>
                		<td>
                			Total
                		</td>
                		<td>
                			{!! $sum !!}
                		<td>
                </tr>
            </tbody>

            {!! $grid->renderFooter() !!}

        </table>
    </div>
    <div class="box-footer clearfix">
        {!! $grid->paginator() !!}
    </div>
    <!-- /.box-body -->
</div>
