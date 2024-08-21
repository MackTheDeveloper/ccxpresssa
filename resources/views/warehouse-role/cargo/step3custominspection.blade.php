<div style="float:left;width:100%;margin-bottom:10px;">
	<div style="float: left;margin-right: 10px;"><span style="color:#000">Custom Inspection :</span> {{$ajaxData['status']}}</div>
	<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxData['on']}}</div>
	<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxData['changedBy']}}</div>
</div>

<div style="float:left;width:100%">
	<h3 style="background: #efefef;border-bottom: 1px solid #000;padding: 10px;width: 100%;float: left;font-size: 15px;margin:0px">Comments</h3>
	<?php if (!empty($ajaxData['comments'])) {
		foreach ($ajaxData['comments'] as $k => $v) { ?>
			<div style="width:70%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;">{{$v->notes}}</div>
			<div style="width:15%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;"><span style="margin-right:15px;color:#000">On</span><span>{{date('d-m-Y',strtotime($v->created_on))}}</span></div>
			<div style="width:15%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;"><span style="margin-right:15px;color:#000">By</span><span>
					<?php
					$dataUser = app('App\User')->getUserName($v->created_by);
					if (!empty($dataUser))
						echo $dataUser->name;
					else
						echo '-';
					?></span></div>
		<?php }
	} else { ?>
		No comment
	<?php } ?>
</div>