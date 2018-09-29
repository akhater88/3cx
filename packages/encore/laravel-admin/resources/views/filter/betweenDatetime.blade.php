<div class="form-group">
    <label class="col-sm-2 control-label">{{$label}}</label>
    <div class="col-sm-8" style="width: 390px">
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
            </div>
            <input type="text" class="form-control" id="{{$id['start']}}" placeholder="From" name="{{$name['start']}}" value="{{ request($name['start'], array_get($value, 'start')) }}">
            <span class="input-group-addon" style="border-left: 0; border-right: 0;">-</span>
            <input type="text" class="form-control" id="{{$id['end']}}" placeholder="To" name="{{$name['end']}}" value="{{ request($name['end'], array_get($value, 'end')) }}">
               
        </div>
        <p>
            To get less than date fill To and leave from empty
      		<br>
      		To get greater than date fill From and leave To empty
      		</p>   
    </div>
</div>