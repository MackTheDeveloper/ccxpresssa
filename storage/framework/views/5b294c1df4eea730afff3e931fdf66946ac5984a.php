<?php $__env->startSection('title'); ?>
<?php 
if($model->id)
{
echo ($id == 1) ? 'Update Cargo File' : ($id == 2 ? 'Update Cargo File' :  'Update Cargo File'); 
}else{
echo ($id == 1) ? 'Add Cargo File' : ($id == 2 ? 'Add Cargo File' :  'Add Cargo File'); 
}
?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
    <?php echo $__env->make('menus.cargo-files', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1><?php 
if($model->id)
{
echo ($id == 1) ? 'Update Cargo File (Import)' : ($id == 2 ? 'Update Cargo File (Export)' :  'Update Cargo File (Locale)'); 
}else{
//echo ($id == 1) ? 'Add Import Shipment' : ($id == 2 ? 'Add Export File' :  'Add Locale Shipment'); 
    echo ($id == 1) ? 'Add Cargo File' : ($id == 2 ? 'Add Cargo File' :  'Add Cargo File'); 
}
?></h1>
</section>

<section class="content">
    <?php if(Session::has('flash_message')): ?>
        <div class="alert alert-success flash-success">
            <?php echo e(Session::get('flash_message')); ?>

        </div>
    <?php endif; ?>
    <div class="box box-success">
        <div class="box-body cargo-forms">
                    <?php
                    if($model->id)
                        $actionUrl = url('cargo/update',$model->id);
                    else
                        $actionUrl = url('cargo/store');    
                    
                    
                    if($id == 1)
                    { 
                        if($model->id) {
                            echo View::make('cargo.importupdate', array('actionUrl' => $actionUrl,'model'=>$model,'modelCargoPackage'=>$modelCargoPackage,'modelCargoContainer'=>$modelCargoContainer,'natureOfServices'=>$natureOfServices,'agents'=>$agents,'dataImportHawbAll'=>$dataImportHawbAll,'billingParty'=>$billingParty,'warehouses'=>$warehouses,'cashier'=>$cashier))->render();  
                        }else {
                            ?>
                            <div class="tab-v1">
                                <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                                    <li class="active"><a href="#importform" data-toggle="tab">Import</a></li>
                                    <li><a href="#exportform" data-toggle="tab">Export</a></li>
                                    <li><a href="#localeform" data-toggle="tab">Locale</a></li>
                                </ul>

                                <div class="tab-content">
                                    
                                    <div class="tab-pane fade in active" id="importform">
                                        <?php echo View::make('cargo.import', array('actionUrl' => $actionUrl,'model'=>$model,'modelCargoPackage'=>$modelCargoPackage,'modelCargoContainer'=>$modelCargoContainer,'natureOfServices'=>$natureOfServices,'agents'=>$agents,'dataImportHawbAll'=>$dataImportHawbAll,'dataExportHawbAll'=>$dataExportHawbAll,'warehouses'=>$warehouses,'modelHawb'=>$modelHawb,'modelHawbCargoPackage'=>$modelHawbCargoPackage,'modelHawbCargoContainer'=>$modelHawbCargoContainer,'dataImportAwbNos'=>$dataImportAwbNos))->render();  

                                        ?>
                                    </div>

                                    <div class="tab-pane fade" id="exportform">
                                        <?php echo View::make('cargo.export', array('actionUrl' => $actionUrl,'model'=>$model,'modelConsolidateAw'=>$modelConsolidateAw,'natureOfServices'=>$natureOfServices,'agents'=>$agents,'dataImportHawbAll'=>$dataImportHawbAll,'dataExportHawbAll'=>$dataExportHawbAll,'warehouses'=>$warehouses))->render();
                                         ?>
                                    </div>

                                    <div class="tab-pane fade" id="localeform">
                                        <?php  echo View::make('cargo.locale', array('actionUrl' => "Hello",'model'=>$model,'natureOfServices'=>$natureOfServices,'agents'=>$agents,'billingParty'=>$billingParty))->render(); ?>
                                    </div>
                                    
                                </div>
                            </div>
                      <?php  //echo View::make('cargo.import', array('actionUrl' => $actionUrl,'model'=>$model,'modelCargoPackage'=>$modelCargoPackage,'modelCargoContainer'=>$modelCargoContainer))->render(); 
                        } 
                    }
                    elseif($id == 2){
                        if($model->id) {
                        echo View::make('cargo.exportupdate', array('actionUrl' => $actionUrl,'model'=>$model,'modelConsolidateAw'=>$modelConsolidateAw,'modelCargoPackage'=>$modelCargoPackage,'natureOfServices'=>$natureOfServices,'agents'=>$agents,'dataExportHawbAll'=>$dataExportHawbAll,'billingParty'=>$billingParty,'warehouses'=>$warehouses,'cashier'=>$cashier))->render();
                        }else
                        { ?>
                            <div class="tab-v1">
                                <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                                    <li><a href="#importform" data-toggle="tab">Import</a></li>
                                    <li class="active"><a href="#exportform" data-toggle="tab">Export</a></li>
                                    <li><a href="#localeform" data-toggle="tab">Locale</a></li>
                                </ul>

                                <div class="tab-content">
                                    
                                    <div class="tab-pane fade" id="importform">
                                        <?php echo View::make('cargo.import', array('actionUrl' => $actionUrl,'model'=>$model,'modelCargoPackage'=>$modelCargoPackage,'modelCargoContainer'=>$modelCargoContainer))->render();  

                                        ?>
                                    </div>

                                    <div class="tab-pane fade in active" id="exportform">
                                        <?php echo View::make('cargo.export', array('actionUrl' => $actionUrl,'model'=>$model,'modelConsolidateAw'=>$modelConsolidateAw))->render();
                                         ?>
                                    </div>

                                    <div class="tab-pane fade" id="localeform">
                                        <?php  echo View::make('cargo.locale', array('actionUrl' => $actionUrl,'model'=>$model,'billingParty'=>$billingParty))->render(); ?>
                                    </div>
                                    
                                </div>
                            </div>
                       <?php    //echo View::make('cargo.export', array('actionUrl' => $actionUrl,'model'=>$model,'modelConsolidateAw'=>$modelConsolidateAw))->render();
                        }
                    }
                    else{
                        if($model->id) {
                        echo View::make('cargo.localeupdate', array('actionUrl' => $actionUrl,'model'=>$model,'natureOfServices'=>$natureOfServices,'agents'=>$agents,'billingParty'=>$billingParty,'premissionToUpdateLocal'=>$premissionToUpdateLocal,'cargoRenewContract'=>$cargoRenewContract))->render();
                        }
                        else
                        { ?>
                            <div class="tab-v1">
                                <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                                    <li><a href="#importform" data-toggle="tab">Import</a></li>
                                    <li><a href="#exportform" data-toggle="tab">Export</a></li>
                                    <li class="active"><a href="#localeform" data-toggle="tab">Locale</a></li>
                                </ul>

                                <div class="tab-content">
                                    
                                    <div class="tab-pane fade" id="importform">
                                        <?php echo View::make('cargo.import', array('actionUrl' => $actionUrl,'model'=>$model,'modelCargoPackage'=>$modelCargoPackage,'modelCargoContainer'=>$modelCargoContainer))->render();  

                                        ?>
                                    </div>

                                    <div class="tab-pane fade" id="exportform">
                                        <?php echo View::make('cargo.export', array('actionUrl' => $actionUrl,'model'=>$model,'modelConsolidateAw'=>$modelConsolidateAw))->render();
                                         ?>
                                    </div>

                                    <div class="tab-pane fade in active" id="localeform">
                                        <?php  echo View::make('cargo.locale', array('actionUrl' => $actionUrl,'model'=>$model,'billingParty'=>$billingParty))->render(); ?>
                                    </div>
                                    
                                </div>
                            </div>
                        <?php //echo View::make('cargo.locale', array('actionUrl' => $actionUrl,'model'=>$model))->render();   
                        }
                    }
                    ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.custom', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>