<div style="float: left;width: 100%;margin-bottom:10px;">
		<a style="" title="Release Receipt"  href="javascript:void(0)"><span style="border-radius:0px" class="btn btn-success generatereleasereceipt" data-id='<?php echo $ajaxData['id']; ?>'>Release Receipt</a>
</div>
<div style="float:left;width:100%;margin-bottom:10px;">
	<div style="float: left;margin-right: 10px;"><span style="color:#000">Shipement Release:</span> {{$ajaxData['status']}}</div>
	<div style="float: left;margin-right: 10px;"><span style="color:#000">On:</span> {{$ajaxData['on']}}</div>
	<div style="float: left;margin-right: 10px;"><span style="color:#000">By:</span> {{$ajaxData['changedBy']}}</div>
</div>