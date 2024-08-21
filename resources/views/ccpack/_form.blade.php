@extends('layouts.custom')
@section('title')
<?php echo 'Add CCpack File'; ?>
@stop

@section('breadcrumbs')
    @include('menus.ccpack')
@stop


@section('content')
<section class="content-header">
    <h1><?php echo 'Add CCpack File'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
        	
            

	<div class="tab-v1">
                        <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                            <li class="active"><a href="#importform" data-toggle="tab">Import</a></li>
                            <li style="display:none"><a href="#exportform" data-toggle="tab">Export</a></li>
                            
                        </ul>
                        <div class="tab-content">
                            <div id="importform" class="tab-pane fade in active"> 
                                <?php echo View::make('ccpack.import', array('model'=>$model))->render();  
                                ?>
                            </div>
                            <div id="exportform" class="tab-pane fade in"> 
                                <?php echo View::make('ccpack.export', array('model'=>$model))->render();  
                                ?>
                            </div>
                        </div>
                    </div>
        </div>
    </div>
</section>
@endsection
