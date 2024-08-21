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
                            <th>Sr. No.</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php $i = 1; ?>
                        @foreach ($dataCashCredit as $dinfo)
                        <?php $amtDesc = explode('-', $dinfo->description); ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{date('d-m-Y h:i:s',strtotime($dinfo->updated_on))}}</td>
                                <td><?php echo $amtDesc[1]; ?></td>
                                <td style="text-align: right;"><?php echo $dinfo->cash_credit_flag == 1 ? number_format(str_replace(',','',$amtDesc[0]),2) : '-';  ?></td>
                                <td style="text-align: right;"><?php echo $dinfo->cash_credit_flag == 2 ? number_format(str_replace(',','',$amtDesc[0]),2) : '-';  ?></td>
                            </tr>
                            <?php $i++; ?>
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

