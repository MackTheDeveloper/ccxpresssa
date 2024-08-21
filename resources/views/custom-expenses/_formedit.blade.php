@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Custom Expense' : 'Create Custom Expense'; ?>
@stop

<?php
if (!empty($model->id)) {
    $counter = App\CustomExpensesDetails::where('expense_id', $model->id)->where('deleted', '0')->count();
} else {
    $counter = 0;
}
?>

@section('breadcrumbs')
@include('menus.ups-expense')
@stop


@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Custom Expense' : 'Add Custom Expense'; ?></h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="box box-success">
        <div class="box-body">



            <?php
            $actionUrl = route('storeexpenseusingawl');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form create-form-expenenseinbasiccargo','id'=>'createExpenseForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}

            <input type="hidden" name="ups_file_number" value="<?php echo $model->ups_file_number ?>" class="ups_file_number">
            <input type="hidden" name="custom_id" value="<?php echo $model->custom_id ?>" class="custom_id">
            <input type="hidden" name="voucher_number" value="<?php echo $model->voucher_number; ?>">
            <input type="hidden" class="count_expense" name="count_expense" value="<?php echo $counter; ?>">


            <div class="expensemaincontainer">
                <div class="col-md-12">

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('exp_date') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('exp_date', 'Date', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::text('exp_date', date('d-m-Y', strtotime($model->exp_date)), ['class' => 'form-control datepicker fexpdate', 'placeholder' => 'Enter Date']); ?>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('ups_details_id') ? 'has-error' :'' }}">
                            <div class="col-md-12 required">
                                <?php echo Form::label('ups_details_id', 'AWB Number', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <?php echo Form::select('ups_details_id', $dataFileNumber, $model->ups_details_id, ['class' => 'form-control selectpicker fups_details_id', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('custom_file_number') ? 'has-error' :'' }}">
                            <div class="col-md-12 required">
                                <?php echo Form::label('custom_file_number', 'Custom File No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <?php echo Form::text('custom_file_number', $model->custom_file_number, ['placeholder' => 'Enter Name', 'class' => 'fcustom_id form-control']); ?>
                            </div>
                        </div>
                    </div>


                </div>


                <div class="col-md-12">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('voucher_number') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('voucher_number', 'Voucher No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <span class="form-control">#<?php echo $model->voucher_number; ?></span>
                            </div>
                        </div>
                    </div>
                </div>






                <div class="expensesubcontainer">
                    <span style="width: 100%;text-align: left;display: none;margin-bottom: 10px;" class="btn btn-success spanexpense spanexpense-0"><span class="fa fa-plus fa-plus-expense" data-faplusid="0" id="fa-plus-0" style="float: left;padding: 5px;"></span><span style="float: right;" class="remove-sec" data-removeid="0">Remove</span></span>
                    <div id="container-0" class="allcontainer">
                        <div class="col-md-12" style="">

                            <table class="table table-expense" style="">
                                <thead>
                                    <tr>
                                        <th style="width: 22%;">Description</th>
                                        <th style="width: 8%;">Amount</th>
                                    </tr>
                                </thead>


                                <tbody>
                                    <?php if (empty($dataExpenseDetails)) { ?>
                                        <tr id="0">
                                            <td><?php echo Form::textarea('expenseDetails[description][0]', '', ['class' => 'form-control cvalidation', 'id' => 'description-0', 'rows' => 1]); ?></td>
                                            <td><?php echo Form::text('expenseDetails[amount][0]', '', ['class' => 'form-control cvalidation famount', 'placeholder' => '', 'onkeypress' => 'return isNumber(event)']); ?></td>
                                        </tr>
                                        <?php } else {
                                        $i = 0;
                                        foreach ($dataExpenseDetails as $k => $v) {  ?>
                                            <tr id="<?php echo $i; ?>">
                                                <td><?php echo Form::textarea("expenseDetails[description][$i]", $v->description, ['class' => 'form-control cvalidation', 'id' => 'description-0', 'rows' => 1]); ?></td>
                                                <td><?php echo Form::text("expenseDetails[amount][$i]", $v->amount, ['class' => 'form-control cvalidation famount', 'placeholder' => '', 'onkeypress' => 'return isNumber(event)']); ?></td>
                                            </tr>
                                    <?php $i++;
                                        }
                                    } ?>
                                </tbody>

                            </table>


                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="text-align: center;">

                    <div class="form-group" style="padding-top: 28px;">
                        <button type="submit" id="CreateExpenseFormButton" class="btn btn-success btn-prime white btn-flat">Update</button>
                        <?php
                        $cancleRoute = route('customexpneses');
                        ?>
                        <a class="btn btn-danger" href="<?php echo $cancleRoute; ?>" title="">Cancel</a>
                    </div>

                </div>

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
        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46 && charCode != 45) {
            return false;
        }
        return true;
    }
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {


        $('#ups_details_id').change(function() {
            $('#loading').show();
            if ($(this).val() != '') {
                var ab = $("#ups_details_id option:selected").html();
                $('.ups_file_number').val(ab);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var urlzn = '<?php echo url("customexpnese/getcustomdata"); ?>';
                $.ajax({
                    url: urlzn,
                    type: 'POST',
                    dataType: "json",
                    data: {
                        'id': $(this).val()
                    },
                    success: function(data) {
                        $('#loading').hide();
                        if (data != '' && data != null) {
                            $('.custom_id').val(data.id);
                            $('#custom_file_number').val(data.file_number);
                        } else {
                            $('.custom_id').val('');
                            $('#custom_file_number').val('');
                        }
                    }
                });
            } else {
                $('.custom_id').val('');
                $('#custom_file_number').val('');
                $('#loading').hide();
            }
        })


        $('#createExpenseForm').on('submit', function(event) {
            $('#loading').show();

            $('.fexpdate').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fcustom_id').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fups_details_id').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fdescription').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.famount').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $("#loading").hide();

        });
        $('#createExpenseForm').validate({
            submitHandler: function(form) {
                $('#loading').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var createExpenseForm = $("#createExpenseForm");
                var formData = createExpenseForm.serialize();
                var urlz = '<?php echo url("customexpnese/updatecustomexpnese/$model->id"); ?>';

                $.ajax({
                    url: urlz,
                    async: false,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        $('#loading').hide();
                        $('.selectpicker').selectpicker('refresh');
                        window.location.href = "<?php echo route('customexpneses'); ?>";

                    },
                });
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "ups_details_id") {
                    var pos = $('.fups_details_id button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });

        var counter = 0;
        if ('<?php echo $counter; ?>' != 0) {
            counter = <?php echo $counter - 1; ?>;
        }

        $('.allcontainer').on("click", ".addmoreexpense", function(e) {
            $('.count_expense').val(parseInt($('.count_expense').val()) + 1);
            counter = counter + 1;
            if (counter == 0) {
                counter = 1;
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlzte = '<?php echo url("customexpnese/addmorecustomexpense"); ?>';
            $.ajax({
                url: urlzte,
                type: 'POST',
                data: {
                    'counter': counter
                },
                success: function(data) {
                    $('#loading').hide();
                    $('.table-expense tbody').append(data);
                }
            });

        });

        $(document).on('click', '.removeexpense', function() {
            $('#loading').show();
            $('.count_expense').val(parseInt($('.count_expense').val()) - 1);
            $('.table-expense tbody tr#' + $(this).data('cid')).remove();
            $('#loading').hide();
        })

    });
</script>
@stop