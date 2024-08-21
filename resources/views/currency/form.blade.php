@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Currency' : 'Add Currency'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('countries') }}">Manage Countries</a>
        </li>
    </ul>
</aside>
@stop

<?php 
if(!empty($model->id))
{
    $countcurrency = App\CurrencyExchange::where('from_currency',$model->id)->count();
}else{
    $countcurrency = 0;
}

?>                    

@section('breadcrumbs')
    @include('menus.currency')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Currency' : 'Add Currency'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('currency/update',$model->id);
                    else
                        $actionUrl = url('currency/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-6 required">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                        <?php echo Form::label('name', 'Name',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                        <?php echo Form::text('name',$model->name,['class'=>'form-control','placeholder' => 'Enter Name','class'=>'fname form-control']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 required">
                    <div class="form-group {{ $errors->has('code') ? 'has-error' :'' }}">
                        <?php echo Form::label('code', 'Code',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                        <?php echo Form::text('code',$model->code,['class'=>'form-control','placeholder' => 'Enter Name','class'=>'fcode form-control']); ?>
                        </div>
                    </div>
                </div>
               
            </div>

            <div class="col-md-12">
                 <div class="col-md-6">
                    <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                    <?php echo Form::label('status', 'Status',['class'=>'col-md-4']); ?>
                    <div class="consolidate_flag-md-6 col-md-6">
                    <?php 
                       echo Form::radio('status', '1',$model->status == '1' || $model->status == '' ? 'checked' : '',['class'=>'flagconsol']); 
                                echo Form::label('', 'Active');
                                echo Form::radio('status', '0',$model->status == '0' ? 'checked' : '',['class'=>'flagconsol']); 
                                echo Form::label('', 'Inactive');   
                    ?>
                    </div>
                </div>
                </div>
            </div>
            
            <?php if(empty($model->id) || count($dataCurrencyExchange) == 0) {  ?>
            <div class="col-md-12 currency-exchange" style="border: 1px solid #ccc;padding: 10px">
                <div id="addcontainer-0" style="width: 100%;float: left;">
                    <div class="col-md-4">
                        <div class="required">
                        <?php echo Form::label('to_currency', 'Exchange Currency',['class'=>'control-label']); ?>
                        </div>
                        <?php echo Form::select('to_currency[0]', $currencies,'',['class'=>'form-control fto_currency','id'=>'to_currency-0','placeholder' => 'Select Currency']); ?>
                    </div>
                    <div class="col-md-2">
                        <div class="required">
                        <?php echo Form::label('exchange_value', 'Exchange Rate',['class'=>'control-label']); ?>
                        </div>
                        <?php echo Form::text('exchange_value[0]','0.00',['class'=>'form-control','id'=>'exchange_value-0','placeholder' => '','class'=>'fexchange_value form-control']); ?>
                    </div>
                    <div class="col-md-1" style="margin-top: 35px;">
                        <a href="javascript:void(0)" class='btn btn-success btn-xs addmorecurrencyexchange'>+</a>
                    </div>
                </div>
            </div>
            <?php } else {  ?>
                <div class="col-md-12 currency-exchange" style="border: 1px solid #ccc;padding: 10px">
                <?php $i = 0; foreach ($dataCurrencyExchange as $key => $value) { ?>
                    <div id="addcontainer-<?php echo $i; ?>" style="width: 100%;float: left;">
                        <div class="col-md-4">
                            <div class="required">
                            <?php echo Form::label('to_currency', 'Exchange Currency',['class'=>'control-label']); ?>
                            </div>
                            <?php echo Form::select("to_currency[$i]", $currencies,$value->to_currency,['class'=>'form-control fto_currency','id'=>"to_currency-$i",'placeholder' => 'Select Currency']); ?>
                        </div>
                        <div class="col-md-2">
                            <div class="required">
                            <?php echo Form::label('exchange_value', 'Exchange Rate',['class'=>'control-label']); ?>
                            </div>
                            <?php echo Form::text("exchange_value[$i]",$value->exchange_value,['class'=>'form-control','id'=>"exchange_value-$i",'placeholder' => '','class'=>'fexchange_value form-control']); ?>
                        </div>
                        <div class="col-md-1" style="margin-top: 35px;">
                            <a href="javascript:void(0)" class='btn btn-success btn-xs addmorecurrencyexchange'>+</a>
                            <?php if($i != 0) { ?>
                                <a href="javascript:void(0)" id="<?php echo $i; ?>" class='btn btn-danger btn-xs removecurrencyexchange'>-</a>
                            <?php } ?>
                        </div>
                    </div>
                <?php $i++; } ?>
            </div>
            <?php } ?>


            
            

            <div class="form-group col-md-12 btm-sub">
                            
                                <button type="submit" class="btn btn-success">
                                    <?php
                                        if(!$model->id)
                                            echo "Submit";
                                        else
                                            echo "Update";
                                        ?>
                                </button>
                            
                            <a class="btn btn-danger" href="{{url('currency')}}" title="">Cancel</a>
            </div>

                    {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
                return false;
            }
            return true;
        }
        $(document).ready(function() {
             $('#createforms').on('submit', function (event) {
                $('.fname').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fcode').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fto_currency').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fexchange_value').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                });
            $('#createforms').validate();   
        });


        var countcurrency = 0;
        if('<?php echo $countcurrency; ?>' != 0)
        {
            countcurrency  =  <?php echo $countcurrency-1;?>;
        }
        $(document).on("click",".addmorecurrencyexchange",function (e) {
            countcurrency = countcurrency+1;
            if(countcurrency == 0) { countcurrency = 1; }
            e.preventDefault();
            var str  =  '<div style="width: 100%;float: left;" id="addcontainer-'+countcurrency+'"><div class="col-md-4"><div class="required" aria-required="true"><label for="to_currency" class="control-label">Exchange Currency</label></div><select class="form-control fto_currency" name="to_currency['+countcurrency+']" id="to_currency-'+countcurrency+'">'+getcurrencydd(countcurrency)+'</select></div><div class="col-md-2"><div class="required" aria-required="true"><label for="exchange_value" class="control-label">Exchange Rate</label></div><input class="form-control fexchange_value" placeholder="" name="exchange_value['+countcurrency+']" id="exchange_value-'+countcurrency+' type="text" value="0.00"></div><div class="col-md-1" style="margin-top: 35px;"><a style="margin-right: 10px;" href="javascript:void(0)" class="btn btn-success btn-xs addmorecurrencyexchange">+</a><a style="" href="javascript:void(0)" class="btn btn-danger btn-xs removecurrencyexchange" id="'+countcurrency+'">-</a></div></div>';
            $('.currency-exchange').append(str);
        });

        function getcurrencydd(countcurrency)
        {
             $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
            var urlzt = '<?php echo url("currency/getcurrencydd"); ?>';
            var dataAll;
            $.ajax({
                    url:urlzt,
                    type:'POST',
                    data:{'countcurrency':countcurrency},
                    success:function(data) {
                        console.log(data);
                            $('#to_currency-'+countcurrency).html(data);
                            }
                });
        }

        $(document).on("click",".removecurrencyexchange",function (e) {
                $('#loading').show();
                e.preventDefault();
                var id = $(this).attr('id');
                $(".currency-exchange #addcontainer-"+id).remove();
                $('#loading').hide();
            });
        
</script>
@stop
