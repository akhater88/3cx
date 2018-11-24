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
                    <th>Extension</th>
                    <th>Name</th>
                </tr>
            </thead>

            <tbody>
   			@section('content')
            		<?php  $sumIn = 0; $countIn = 0;?>  
            @endsection
   	@if(isset($dataIn))
   	 	@foreach($dataIn as $row)
   	 		 <tr>
   	 		 	<td>
       	 		 	@section('content')
                    		<?php  $ext = str_replace('Ext.','', $row->extintion_inbound);?>  
                    @endsection
   	 		 		{!! $ext !!}
   	 		 	</td>
				<td>
					@if($row->final_dispname != '')
   	 		 			{!! $row->final_dispname !!}
   	 		 		@else
   	 		 			{!! $row->to_dispname !!}
   	 		 		@endif	
   	 		 	</td>
   	 		 			@section('content')
                        		<?php  $sumIn += $row->count_call;$countIn++; ?>
                        	 @endsection
			 <tr>
		 @endforeach
    @endif
    			@section('content')
            		<?php  $sumOut = 0; $countOut = 0;?>  
            @endsection
    @if(isset($dataOut))
   	 	@foreach($dataOut as $row)
   	 		 <tr>
   	 		 	<td>
   	 		 		@section('content')
                    		<?php  $ext = str_replace('Ext.','', $row->extintion_outbound);?>  
                    @endsection
   	 		 		{!! $ext !!}
   	 		 	</td>
				<td>
					{!! $row->from_dispname !!}
   	 		 			
   	 		 	</td>
   	 		 			@section('content')
                        		<?php  $sumOut += $row->count_call;$countOut++; ?>
                        	 @endsection
			 <tr>
		 @endforeach
    @endif
   			   <tr>
                		<td>
                			Inbound Calls Mean
                		</td>
                		<td>
                		@if($countIn != 0)
                			{!! $sumIn/$countIn !!}
                		@else
                			{!! $countIn !!}
                		@endif
                		<td>
                </tr>
                <tr>
                		<td>
                			Outbound Calls Mean
                		</td>
                		<td>
                			@if($countOut != 0)
                    			{!! $sumOut/$countOut !!}
                    		@else
                    			{!! $countOut !!}
                    		@endif
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
   
  