@extends('layouts.custom')

@section('title')
Closed Files Listing
@stop

@section('breadcrumbs')
@include('menus.closed-file')
@stop

@section('breadcrumbs')
@include('menus.cargo-files')
@stop

@section('content')
<section class="content-header">
    <h1>Closed Files Listing</h1>
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
            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="col-md-12 row" style="margin-bottom: 10px">
                <div class="col-md-1">
                    <label style="margin-top: 5px">Module</label>
                </div>
                <div class="col-md-2">
                    <select id="flagModule" class="form-control">
                        <option selected="" value="Cargo">Cargo</option>
                        <option value="HouseFile">House File</option>
                        <option value="UpsMaster">Ups Master</option>
                        <option value="Ups">Ups</option>
                        <option value="AeropostMaster">Aeropost Master</option>
                        <option value="Aeropost">Aeropost</option>
                        <option value="CcpackMaster">CCPack Master</option>
                        <option value="CCPack">CCPack</option>
                    </select>
                </div>
                <div class="from-date-filter-div filterout col-md-2">
                    <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter" value="<?php echo date('01-m-Y'); ?>">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter" value="<?php echo date('d-m-Y'); ?>">
                </div>
                <div class="col-md-2">
                    <?php echo Form::select('close_unclose_by', $users, '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'close_unclose_by', 'placeholder' => 'All']); ?>
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
            {{ Form::close() }}
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>File No.</th>
                        <th>Tracking Number</th>
                        <th>Consignee</th>
                        <th>Shipper</th>
                        <th>Closed On</th>
                        <th>Closed By</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        DatatableInitiate();

        /* $('#flagModule').change(function() {
            var flagModule = $(this).val();
            DatatableInitiate(flagModule);
        }) */

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var flagModule = $('#flagModule').val();
                var closedBy = $('#close_unclose_by').val();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                DatatableInitiate(flagModule, closedBy, fromDate, toDate);
            },
        });

        //$('.re-activate').click(function() {
        $(document).delegate('.re-activate', 'click', function() {
            var id = $(this).data('id');
            var flagModule = $('#flagModule').val();

            Lobibox.confirm({
                msg: "Are you sure to Re-Activate the file?",
                callback: function(lobibox, type) {
                    if (type == 'yes') {
                        $.ajax({
                            type: 'post',
                            url: '<?php echo url('reactivatefile'); ?>',
                            data: {
                                'id': id,
                                'flagModule': flagModule
                            },
                            success: function(response) {
                                Lobibox.notify('info', {
                                    size: 'mini',
                                    delay: 2000,
                                    rounded: true,
                                    delayIndicator: false,
                                    msg: 'File has been Re-Activated successfully.'
                                });
                                DatatableInitiate(flagModule);
                            }
                        });
                    } else {

                    }
                }
            })
        })
    })

    function DatatableInitiate(flagModule = 'Cargo', closedBy = '', fromDate = '', toDate = '') {
        var i = 1;
        var table = $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [],
            "scrollX": true,
            /* "order": [
                [0, "desc"]
            ], */
            "ajax": {
                url: "{{url('closefiles/listbydatatableserverside')}}", // json datasource
                data: function(d) {
                    d.flagModule = flagModule;
                    d.closedBy = closedBy;
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                $('#loading').show();
                i++;
                $("#loading").hide();
            }
        });
    };
</script>
@stop