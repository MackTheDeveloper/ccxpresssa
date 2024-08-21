@extends('layouts.custom')

@section('title')
Replenish Account Listing
@stop

<?php
$permissionCashBankDepositeVouchersEdit = App\User::checkPermission(['update_deposite_vouchers'], '', auth()->user()->id);
$permissionCashBankDepositeVouchersDelete = App\User::checkPermission(['delete_deposite_vouchers'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.cash-credit')
@stop

@section('content')
<section class="content-header">
    <h1>Replenish Account Listing</h1>
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
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th>Account</th>
                        <th>Amount</th>
                        <th>Deposit Date</th>
                        <th>Approved By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deposits as $dinfo)
                    <tr data-editlink="{{ route('editdepositcashcredit',[$dinfo->id]) }}" id="<?php echo $dinfo->id; ?>" class="edit-row">
                        <td style="display: none;">{{$dinfo->id}}</td>
                        <td><?php $datCashCredit = App\CashCredit::getCashCreditData($dinfo->cash_credit_account);
                            echo !empty($datCashCredit) ? $datCashCredit->name : '-'; ?></td>
                        <td style="text-align:right">{{number_format($dinfo->amount,2)}}</td>
                        <td><?php echo date('d-m-Y', strtotime($dinfo->deposit_date)) ?></td>
                        <td><?php $modelU = new App\User;
                            $datUser = $modelU->getUserName($dinfo->approved_by_user);
                            echo !empty($datUser) ? $datUser->name : '-'; ?></td>
                        <td>
                            <div class='dropdown'>
                                <?php
                                $delete =  route('deletedepositcashcredit', $dinfo->id);
                                $edit =  route('editdepositcashcredit', $dinfo->id);
                                ?>
                                <?php if ($permissionCashBankDepositeVouchersEdit) { ?>
                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                <?php } ?>
                                <?php if ($permissionCashBankDepositeVouchersDelete) { ?>
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                <?php } ?>


                            </div>

                        </td>
                    </tr>
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
        $('#example').DataTable({
            'stateSave': true,
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function() {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
                $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
            },

        });
    })
</script>
@stop