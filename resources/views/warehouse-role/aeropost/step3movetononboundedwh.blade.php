<div style="float:left;width:100%;margin-bottom:10px;">
	<div style="float: left;margin-right: 10px;"><span style="color:#000">{{$ajaxData['status']}}</span></div>
	<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxData['on']}}</div>
	<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxData['changedBy']}}</div>

	<div style="float: left;margin-right: 10px;margin-left: 20px;"><span style="color:#000">Nonbounded Warehouse Confirmation : </span>{{$ajaxDataWHConfirmation['status']}}</div>
	<?php if($ajaxDataWHConfirmation['status'] != 'Pending') { ?>
	<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxDataWHConfirmation['on']}}</div>
	<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxDataWHConfirmation['changedBy']}}</div>
	<?php } ?>
</div>

