<div class="detail-container1">
    	<div style="float: left;width: 100%;margin-bottom: 10px;font-weight: bold;text-align: center">
    	<div class="labeldata20">Performed By</div>
    	<div class="resultdata60">Activities</div>
    	<div class="resultdata20">Date/Time</div>
		</div>

    @foreach ($model as $model)
    	<div class="labeldata20"><?php $userData = app('App\User')->getUserName($model->user_id); echo $userData->name; ?></div>
    	<div class="resultdata60"><?php echo $model->description; ?></div>
        <div class="resultdata20">{{$model->updated_on}}</div>
    @endforeach
</div>