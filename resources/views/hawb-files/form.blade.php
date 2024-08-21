@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update House AWB File' : 'Add House AWB File'; ?>
@stop


@section('breadcrumbs')
@include('menus.cargo-files')
@stop

@section('content')
<section class="content-header" style="margin-bottom: 1.5%">
    <h1 style="float: left"><?php echo $model->id ? 'Update House AWB File ' . ($model->cargo_operation_type == 1 ? '(Import)' : ($model->cargo_operation_type == 2 ? '(Export)' : 'Locale')) : 'Add House AWB File'; ?></h1>
    <?php if ($model->id) { ?>
        <h1 style="float: right;color: green">File Status :
            <?php echo isset(Config::get('app.ups_new_scan_status')[!empty($model->hawb_scan_status) ? $model->hawb_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($model->hawb_scan_status) ? $model->hawb_scan_status : '-'] : '-'; ?>
        </h1>
    <?php } ?>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body cargo-forms create-form">

            <?php if ($model->id)
                $actionUrl = url('hawbfile/update', $model->id);
            else
                $actionUrl = url('hawbfile/store');
            ?>

            <?php if ($model->id) {

                if ($model->cargo_operation_type == 1)
                    echo View::make('hawb-files.importedit', array('actionUrl' => $actionUrl, 'model' => $model, 'modelCargoPackage' => $modelCargoPackage, 'modelCargoContainer' => $modelCargoContainer, 'dataImportAwbNos' => $dataImportAwbNos, 'billingParty' => $billingParty, 'agents' => $agents))->render();
                else {
                    $model->weight = $modelCargoPackage->pweight;
                    $model->no_of_pieces = $modelCargoPackage->ppieces;
                    echo View::make('hawb-files.exportedit', array('actionUrl' => $actionUrl, 'model' => $model, 'dataExportAwbNos' => $dataExportAwbNos, 'modelCargoPackage' => $modelCargoPackage, 'billingParty' => $billingParty, 'agents' => $agents))->render();
                }
            } else {  ?>
                <div class="tab-v1">
                    <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                        <li class="active"><a href="#importform" data-toggle="tab">Import</a></li>
                        <li><a href="#exportform" data-toggle="tab">Export</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="importform">
                            <?php echo View::make('hawb-files.import', array('actionUrl' => $actionUrl, 'model' => $model, 'modelCargoPackage' => $modelCargoPackage, 'modelCargoContainer' => $modelCargoContainer, 'dataImportAwbNos' => $dataImportAwbNos, 'agents' => $agents))->render();

                            ?>
                        </div>
                        <div class="tab-pane fade" id="exportform">
                            <?php echo View::make('hawb-files.export', array('actionUrl' => $actionUrl, 'model' => $model, 'dataExportAwbNos' => $dataExportAwbNos, 'agents' => $agents))->render();
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>





        </div>
    </div>
</section>
@endsection

@section('page_level_js')
<script type="text/javascript">
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {




    });
</script>
@stop