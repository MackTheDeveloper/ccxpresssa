@extends('layouts.custom')

@section('title')
Cash/Bank Report
@stop


@section('breadcrumbs')
    @include('menus.reports')
@stop


@section('content')
<section class="content-header">
    <h1>Cash/Bank Report</h1>
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
            <div class="container-rep">
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>Cash/Bank Type</th>
                            <th>Name</th>
                            <th>Available Balance</th>
                            <th>as of</th>
                            <th>Currency</th>
                        </tr>
                    </thead>
                <tbody>
                     @foreach ($cashcredit as $user)
                            <tr data-editlink="{{ route('getcashcreditdataonclick',[$user->id,$user->name]) }}" id="<?php echo $user->id; ?>" class="edit-row">
                                <td style="display: none;">{{$user->id}}</td>
                                <td><?php $detailData = App\CashCreditDetailType::getData($user->detail_type); echo $detailData->name; ?></td>
                                <td>{{$user->name}}</td>
                                <td class="alignright">{{$user->available_balance}}</td>
                                <td><?php echo date('d-m-Y',strtotime($user->as_of)) ?></td>
                                <td><?php $currencyData = App\Currency::getData($user->currency); echo $currencyData->code; ?></td>
                            </tr>
                        @endforeach
                </tbody>
            </table>

            </div>

            
        </div>
    </div>

</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable({
        'stateSave': true,
        "ordering": false
    });

} )
</script>
@stop

