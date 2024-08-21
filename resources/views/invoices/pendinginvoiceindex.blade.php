@extends('layouts.custom')

@section('title')
Pending Cargo Invoices
@stop

@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        
        
       <li class="widemenu">
            <a href="{{ route('invoices') }}">Invoice Listing</a>
        </li>
        <li class="widemenu">
            <a href="{{ route('createinvoice') }}">Add Invoice</a>
        </li>
        <li class="widemenu active">
            <a href="{{ route('pendinginvoices') }}">Pending Invoices</a>
        </li>
        <li class="widemenu">
            <a href="{{ route('invoices') }}">Invoice Report</a>
        </li>
        
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.cargo-invoice')
@stop

@section('content')
<section class="content-header">
    <h1>Pending Cargo Invoices</h1>
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
            <?php //App\Invoices::getAllPendingInvoices(); ?>
            <div style="float: right;width: 200px;margin: 0px;height: 35px;position: absolute;left: 70%;z-index: 111;top:20px">
        
        <a title="Click here to print"  target="_blank" href="{{ route('printpendinginvoices') }}"><i class="fa fa-print btn btn-primary"></i></a>
    </div>
            <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>Date</th>
                <th>Invoice No.</th>
                <th>File No.</th>
                <th>AWB / BL No.</th>
                <th>Billing Party</th>
                <th>Type</th>
                <th>Total Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pendingInvoices as $items)
                <tr>
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                            echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                    <td>{{$items->type_flag}}</td>
                    <td class="alignright">{{number_format($items->total,2)}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteinvoice',$items->id);
                        $edit =  route('editinvoice',$items->id);
                        ?>
                        
                        <?php if($items->type_flag == 'Local') { ?>
                        
                        <a href="{{ route('viewcargolocalfiledetailforcashier',$items->cargo_id) }}" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        
                        <?php } else {?>
                            <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        
                        <?php }?>
                        
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        
                       
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
    jQuery.extend( jQuery.fn.dataTableExt.oSort, {
        "date-uk-pre": function ( a ) {
            if (a == null || a == "") {
                return 0;
            }
            var ukDatea = a.split('-');
            return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
        },
    
        "date-uk-asc": function ( a, b ) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },
    
        "date-uk-desc": function ( a, b ) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        }
    });
    $('#example').DataTable(
    {
        'stateSave': true,
        "columnDefs": [ {
            "targets": [-1],
            "orderable": false
            },{ type: 'date-uk', targets: 4 }],
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

