@extends('layouts.custom')

@section('title')
Administration Expense Listing
@stop

<?php
$permissionOtherExpenseEdit = App\User::checkPermission(['update_other_expenses'], '', auth()->user()->id);
$permissionOtherExpenseDelete = App\User::checkPermission(['delete_other_expenses'], '', auth()->user()->id);
?>


@section('breadcrumbs')
@include('menus.administration-expense')
<?php /* if(app('App\User')->checkUserByRole() == 'Cashier' || app('App\User')->checkUserByRole() == 'Caissière') { ?>
    @include('menus.cashier-cargo-expense')
<?php } else { ?>
<?php if($flagFrom == 'cargo') { ?>
    @include('menus.cargo-expense')
   <?php } else { ?> 
    @include('menus.ups-expense')
    <?php } }  */ ?>
@stop



@section('content')
<section class="content-header">
    <h1>Administration Expense Listing</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="from-date-filter-div filterout col-md-2">
                    <select id="expenseStatus" class="form-control">
                        <option selected="" value="">All</option>
                        <option value="Requested">Requested</option>
                        <option value="on Hold">on Hold</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Approved">Approved</option>
                        <option value="Disbursement done">Disbursement done</option>

                    </select>
                </div>

                <div style="float: right;margin-right: 10px;height: 35px;z-index: 111;top: 18px;">
                    <?php $actionUrl = route('approveallselectedadministrationexpense'); ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form create-form-expenenseinbasiccargo','id'=>'createExpenseForm','autocomplete'=>'off','style'=>'float:left;width:auto')) }}
                    {{ csrf_field() }}
                    <input type="hidden" name="ids" class="ids" value="">
                    <input type="hidden" name="flag" value="aeropostExpense">
                    <button style="" type="submit" class="btn btn-success">Approve Expenses</button>
                    {{ Form::close() }}
                </div>
            </div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="text-align: center"><input type="checkbox" id="selectAll"></th>
                        <th style="display: none">ID</th>
                        <th></th>
                        <th>Date</th>
                        <th>Voucher No.</th>
                        <th>Department</th>
                        <th>Total Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    @foreach ($data as $items)
                    <?php $dataExpenses = App\OtherExpensesDetails::checkExpense($items->id);
                    $cls = '';
                    if ($dataExpenses > 0)
                        $cls = 'expandpackage fa fa-plus';
                    ?>
                    <tr data-editlink="{{ route('editotherexpense',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                        <td style="text-align: center">
                            <?php if ($items->expense_request == 'Requested') { ?>
                                <input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="{{$items->id}}" value="{{$items->id}}" />
                            <?php } ?>
                        </td>
                        <td style="display: none">{{$items->id}}</td>
                        <td style="display: block;text-align: center;padding-top: 13px;" class="<?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->id; ?>"></td>
                        <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                        <td>{{$items->voucher_number}}</td>
                        <td><?php $departmentData = App\CashCreditDetailType::getData($items->department);
                            echo !empty($departmentData->name) ? $departmentData->name : '-'; ?></td>
                        <td class="alignright"><?php echo App\OtherExpenses::getExpenseTotal($items->id);  ?></td>
                        <td><?php echo $items->note != '' ? $items->note : '-'; ?></td>
                        <td>{{$items->expense_request}}</td>
                        <td>
                            <div class='dropdown'>
                                <?php
                                $delete =  route('deleteotherexpensevoucher', $items->id);
                                $edit =  route('editotherexpense', [$items->id, $flagFrom]);
                                ?>
                                <?php
                                //echo App\Expense::getPrints($items->expense_id,$items->cargo_id); 
                                /*$urlAction = url("/expense/getprintsingleexpense/$items->expense_id/$items->cargo_id");
                        $backgroundActions = new App\User;
                        $backgroundActions->backgroundPost($urlAction);*/
                                ?>
                                <a title="Click here to print" target="_blank" href="{{ route('getprintsingleotherexpense',[$items->id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;
                                <?php if ($permissionOtherExpenseEdit) { ?>
                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                <?php } ?>
                                <?php if ($permissionOtherExpenseDelete) { ?>
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                <?php } ?>


                            </div>

                        </td>
                    </tr>
                    <?php $i++; ?>
                    @endforeach

                </tbody>

            </table>
        </div>
    </div>





</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
            "date-uk-pre": function(a) {
                if (a == null || a == "") {
                    return 0;
                }
                var ukDatea = a.split('-');
                return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
            },

            "date-uk-asc": function(a, b) {
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },

            "date-uk-desc": function(a, b) {
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            }
        });
        $('#example').DataTable({
            'stateSave': true,
            "columnDefs": [{
                "targets": [0, 2, 9],
                "orderable": false
            }, {
                type: 'date-uk',
                targets: 3
            }],
            "order": [
                [1, "desc"]
            ],
            "scrollX": true,
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container())
                    .on('click', function() {
                        $('#loading').show();
                        setTimeout(function() {
                            $("#loading").hide();
                        }, 200);
                        $('.expandpackage').each(function() {
                            if ($(this).hasClass('fa-minus')) {
                                $(this).removeClass('fa-minus');
                                $(this).addClass('fa-plus');
                            }
                        })
                    });
                $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
            }
        });

        //$('.expandpackage').click(function(){
        $(document).delegate('.expandpackage', 'click', function() {
            var rowId = $(this).data('rowid');
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 200);
            //$('#loading').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var thiz = $(this);
            var parentTR = thiz.closest('tr');
            if (thiz.hasClass('fa-plus')) {
                /*$('.childrw').remove();
                $('.fa-minus').each(function(){
                    $(this).removeClass('fa-minus');    
                    $(this).addClass('fa-plus');
                })*/

                thiz.removeClass('fa-plus');
                thiz.addClass('fa-minus');
                var expenseId = $(this).data('expenseid');
                var rowId = $(this).data('rowid');
                var urlzte = '<?php echo route("expandotherexpenses"); ?>';
                $.ajax({
                    url: urlzte,
                    type: 'POST',
                    data: {
                        expenseId: expenseId,
                        rowId: rowId
                    },
                    success: function(data) {

                        $(data).insertAfter(parentTR).slideDown();
                    },
                });
                //$('#loading').hide();
            } else {
                thiz.removeClass('fa-minus');
                thiz.addClass('fa-plus');
                $('.child-' + rowId).remove();
                //parentTR.next('tr').remove();
                //$('#loading').hide();

            }
        })

        $('#expenseStatus').change(function() {
            $('#loading').show();
            var status = $(this).val();
            DatatableInitiate(status);
            $('#loading').hide();
        })

        $('#createExpenseForm').on('submit', function(event) {
            if ($('.singlecheckbox:checked').length < 1) {
                alert("Please select the expenses.");
                return false;
            } else {
                if (confirm("Are you sure, you want to approve all the expenses?")) {
                    return true;
                } else {
                    return false;
                }
            }
        });

        $(document).delegate("#selectAll", "click", function(e) {
            $('#example .singlecheckbox').prop('checked', this.checked);
            var checked = [];
            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    checked.push($(this).attr('id'))
                }
            });
            $('.ids').val(checked);
            //console.log(checked);
        });

        $(document).on('click', '.singlecheckbox', function() {
            var checkedFlag = 0;
            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    checkedFlag = 1;
                } else {
                    checkedFlag = 0;
                    return false;
                }
            });
            if (checkedFlag == 0) {
                $('#selectAll').prop('checked', false);
            }
            if (checkedFlag == 1) {
                $('#selectAll').prop('checked', true);
            }

            var checked = [];
            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    checked.push($(this).attr('id'))
                }
            });
            $('.ids').val(checked);
            //console.log(checked);
        });

    })
</script>
@stop