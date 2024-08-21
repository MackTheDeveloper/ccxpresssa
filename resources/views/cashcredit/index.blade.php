@extends('layouts.custom')

@section('title')
Cash/Bank
@stop

<?php 
    $permissionCashBankEdit = App\User::checkPermission(['update_cash_bank'],'',auth()->user()->id); 
    $permissionCashBankDelete = App\User::checkPermission(['delete_cash_bank'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.cash-credit')
@stop


@section('content')
<section class="content-header">
    <h1>Cash/Bank</h1>
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
                <th>Cash/Bank Type</th>
                <th>Name</th>
                <th>Avalilable Balance</th>
                <th>as of</th>
                <th>Currency</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cashcredit as $user)
                <tr data-editlink="{{ route('editcashcredit',[$user->id]) }}" id="<?php echo $user->id; ?>" class="edit-row">
                    <td style="display: none;">{{$user->id}}</td>
                    <td><?php $detailData = App\CashCreditDetailType::getData($user->detail_type); echo $detailData->name; ?></td>
                    <td>{{$user->name}}</td>
                    <td class="alignright">{{number_format($user->available_balance,2)}}</td>
                    <td><?php echo date('d-m-Y',strtotime($user->as_of)) ?></td>
                    <td><?php $currencyData = App\Currency::getData($user->currency); echo !empty($currencyData->code)? $currencyData->code : '-'; ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletecashcredit',$user->id);
                        $edit =  route('editcashcredit',$user->id);
                        ?>
                        <?php if($permissionCashBankEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionCashBankDelete) { ?>
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
    $('#example').DataTable(
    {
        'stateSave': true,
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
            });       
            $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
            });
        },
        
    });
} )
</script>
@stop

