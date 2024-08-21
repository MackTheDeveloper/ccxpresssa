@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Courier' : 'Create Courier'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <?php 
        $checkPermissionCreateCourier = App\User::checkPermission(['create_couriers'],'',auth()->user()->id); 
        $checkPermissionImportCourier = App\User::checkPermission(['import_couriers'],'',auth()->user()->id);
        ?>
        <?php if($checkPermissionCreateCourier) { ?>
        <li class="widemenu">
            <a href="{{ route('couriers') }}">Manage Couriers</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionImportCourier) { ?>
        <li class="widemenu">
            <a href="{{ route('importcourier') }}">Import Couriers</a>
        </li>
        <?php } ?>
    </ul>
</aside>
@stop
@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Courier' : 'Create Courier'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('courier/update',$model->id);
                    else
                        $actionUrl = url('courier/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','autocomplete'=>'off')) }}
                    {{ csrf_field() }}

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
                                <?php echo Form::label('consignee_name', 'Consignee Name',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('consignee_name',$model->consignee_name,['class'=>'form-control','placeholder' => 'Enter Consignee Name']); ?>
                                @if ($errors->has('consignee_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('consignee_name') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('no_manifeste') ? 'has-error' :'' }}">
                                <?php echo Form::label('no_manifeste', 'Shipping detail',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('no_manifeste',$model->no_manifeste,['class'=>'form-control','placeholder' => 'Enter Shipping Detail']); ?>
                                @if ($errors->has('no_manifeste'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('no_manifeste') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('awe_tracking') ? 'has-error' :'' }}">
                                <?php echo Form::label('awe_tracking', 'AWE Tracking Number',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('awe_tracking',$model->awe_tracking,['class'=>'form-control','placeholder' => 'Enter AWE Tracking Number']); ?>
                                @if ($errors->has('awe_tracking'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('awe_tracking') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('origin_country_code') ? 'has-error' :'' }}">
                                <?php echo Form::label('origin_country_code', 'Origin Coutry Code',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('origin_country_code',$model->origin_country_code,['class'=>'form-control','placeholder' => 'Enter Origin Coutry Code']); ?>
                                @if ($errors->has('origin_country_code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('origin_country_code') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('origin_city') ? 'has-error' :'' }}">
                                <?php echo Form::label('origin_city', 'Origin City',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('origin_city',$model->origin_city,['class'=>'form-control','placeholder' => 'Enter Origin City']); ?>
                                @if ($errors->has('origin_city'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('origin_city') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('nbr_pcs') ? 'has-error' :'' }}">
                                <?php echo Form::label('nbr_pcs', 'NBR PCS',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('nbr_pcs',$model->nbr_pcs,['class'=>'form-control','placeholder' => 'Enter Origin NBR PCS']); ?>
                                @if ($errors->has('nbr_pcs'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('nbr_pcs') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('weight') ? 'has-error' :'' }}">
                                <?php echo Form::label('weight', 'Weight (Kg)',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('weight',$model->weight,['class'=>'form-control','placeholder' => 'Enter Weight']); ?>
                                @if ($errors->has('weight'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('weight') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('declared_value') ? 'has-error' :'' }}">
                                <?php echo Form::label('declared_value', 'Declared Value',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('declared_value',$model->declared_value,['class'=>'form-control','placeholder' => 'Enter Declared Value']); ?>
                                @if ($errors->has('declared_value'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('declared_value') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('freight') ? 'has-error' :'' }}">
                                <?php echo Form::label('freight', 'Freight',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('freight',$model->freight,['class'=>'form-control','placeholder' => 'Enter Freight']); ?>
                                @if ($errors->has('freight'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('freight') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('freight_certificate') ? 'has-error' :'' }}">
                                <?php echo Form::label('freight_certificate', 'Freight Certificate',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('freight_certificate',$model->freight_certificate,['class'=>'form-control','placeholder' => 'Enter Freight Certificate']); ?>
                                @if ($errors->has('freight_certificate'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('freight_certificate') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('trucking') ? 'has-error' :'' }}">
                                <?php echo Form::label('trucking', 'Trucking',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('trucking',$model->trucking,['class'=>'form-control','placeholder' => 'Enter Trucking']); ?>
                                @if ($errors->has('trucking'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('trucking') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('insurance') ? 'has-error' :'' }}">
                                <?php echo Form::label('insurance', 'Insurance',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('insurance',$model->insurance,['class'=>'form-control','placeholder' => 'Enter Insurance']); ?>
                                @if ($errors->has('insurance'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('insurance') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('value_custom_purpose') ? 'has-error' :'' }}">
                                <?php echo Form::label('value_custom_purpose', 'Value For Custom Purpose',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('value_custom_purpose',$model->value_custom_purpose,['class'=>'form-control','placeholder' => 'Enter Value For Custom Purpose']); ?>
                                @if ($errors->has('value_custom_purpose'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('value_custom_purpose') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('charges_in_usd') ? 'has-error' :'' }}">
                                <?php echo Form::label('charges_in_usd', 'Charges In USD',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('charges_in_usd',$model->charges_in_usd,['class'=>'form-control','placeholder' => 'Enter Charges In USD']); ?>
                                @if ($errors->has('charges_in_usd'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('charges_in_usd') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('charges_in_haiti') ? 'has-error' :'' }}">
                                <?php echo Form::label('charges_in_haiti', 'Charges In Haitian',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('charges_in_haiti',$model->charges_in_haiti,['class'=>'form-control','placeholder' => 'Enter Charges In Haitian']); ?>
                                @if ($errors->has('charges_in_haiti'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('charges_in_haiti') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('freight_collect') ? 'has-error' :'' }}">
                                <?php echo Form::label('freight_collect', 'Freight Collect',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('freight_collect',$model->freight_collect,['class'=>'form-control','placeholder' => 'Enter Freight Collect']); ?>
                                @if ($errors->has('freight_collect'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('freight_collect') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('free_domicile') ? 'has-error' :'' }}">
                                <?php echo Form::label('free_domicile', 'Free Domicile',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('free_domicile',$model->free_domicile,['class'=>'form-control','placeholder' => 'Enter Free Domicile']); ?>
                                @if ($errors->has('free_domicile'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('free_domicile') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('freight_prepaid') ? 'has-error' :'' }}">
                                <?php echo Form::label('freight_prepaid', 'Freight Prepaid',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('freight_prepaid',$model->freight_prepaid,['class'=>'form-control','placeholder' => 'Enter Freight Prepaid']); ?>
                                @if ($errors->has('freight_prepaid'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('freight_prepaid') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('file_reference') ? 'has-error' :'' }}">
                                <?php echo Form::label('file_reference', 'File Reference',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('file_reference',$model->file_reference,['class'=>'form-control','placeholder' => 'Enter File Reference']); ?>
                                @if ($errors->has('file_reference'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('file_reference') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('credit') ? 'has-error' :'' }}">
                                <?php echo Form::label('credit', 'Credit',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('credit',$model->credit,['class'=>'form-control','placeholder' => 'Enter Credit']); ?>
                                @if ($errors->has('credit'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('credit') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('invest_in_htg') ? 'has-error' :'' }}">
                                <?php echo Form::label('invest_in_htg', 'Invest In HTG',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('invest_in_htg',$model->invest_in_htg,['class'=>'form-control','placeholder' => 'Enter Invest In HTG']); ?>
                                @if ($errors->has('invest_in_htg'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('invest_in_htg') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('invest_in_usd') ? 'has-error' :'' }}">
                                <?php echo Form::label('invest_in_usd', 'Invest In USD',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('invest_in_usd',$model->invest_in_usd,['class'=>'form-control','placeholder' => 'Enter Invest In USD']); ?>
                                @if ($errors->has('invest_in_usd'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('invest_in_usd') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                    <div class="form-group">
                            <div class="col-md-12" style="text-align: right;padding-right: 120px;">
                                <button type="submit" class="btn btn-success">
                                    <?php
                                        if(!$model->id)
                                            echo "Submit";
                                        else
                                            echo "Update";
                                        ?>
                                </button>
                            </div>
                        </div>
                     </div>   

                    {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
