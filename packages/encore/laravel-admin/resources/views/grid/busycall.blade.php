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
                    <th>Extensions</th>
                    <th>Name</th>
                    <th>Missed Call While Busy</th>
                </tr>
            </thead>

            <tbody>
   			
   	@if(isset($dataToGrid))
   	 	@foreach($dataToGrid as $row)
   	 		 <tr>
   	 		 	<td>
       	 		 	{!! $row['ext'] !!}
   	 		 	</td>
				<td>
   	 		 		{!! $row['name'] !!}
   	 		 	</td>
   	 		 	<td>
   	 		 		{!! $row['counter'] !!}
   	 		 	</td>
			 <tr>
		 @endforeach
    @endif

   			   
   </tbody>

            {!! $grid->renderFooter() !!}

        </table>
    </div>
    <div class="box-footer clearfix">
        {!! $grid->paginator() !!}
    </div>
    <!-- /.box-body -->
</div>
   
  