@extends('layouts.custom')
@section('title')
<?php echo ($model->id) ? 'Update Aeropost Master File' : 'Add Aeropost Master File'; ?>
@stop

@section('breadcrumbs')
@include('menus.aeropost')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo ($model->id) ? 'Update Aeropost Master File' : 'Add Aeropost Master File'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body cargo-forms">
            <?php
            if ($model->id) {
                $actionUrl = url('aeropost-master/update', $model->id);
                echo View::make('aeropost-master.importFile', array('actionUrl' => $actionUrl, 'model' => $model, 'agents' => $agents, 'billingParty' => $billingParty))->render();
            ?>
            <?php } else {
                $actionUrl = url('aeropost-master/store'); ?>
                <div class="tab-v1">
                    <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                        <li class="active"><a href="#importform" data-toggle="tab">Import</a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="importform" class="tab-pane fade in active">
                            <?php echo View::make('aeropost-master.importFile', array('actionUrl' => $actionUrl, 'model' => $model, 'agents' => $agents))->render();
                            ?>
                        </div>
                    </div>
                </div>
            <?php }
            ?>
        </div>
    </div>
</section>
@endsection