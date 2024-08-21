@extends('layouts.custom')

@section('title')
Account Sub Types
@stop

<?php
$permissionAccountSubTypesEdit = App\User::checkPermission(['update_account_sub_types'], '', auth()->user()->id);
$permissionAccountSubTypesDelete = App\User::checkPermission(['delete_account_sub_types'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.detail-types')
@stop

@section('content')
<section class="content-header">
    <h1>Account Sub Types</h1>
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
                        <th>System Detail Type</th>
                        <th>System Type</th>
                        <th>QuickBook Type</th>
                        <th>QuickBook Detail Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cashcredit as $user)
                    <tr data-editlink="{{ route('editcashcreditdetailtype',[$user->id]) }}" id="<?php echo $user->id; ?>" class="edit-row">
                        <td style="display: none;">{{$user->id}}</td>
                        <td>{{$user->name}}</td>
                        <td><?php $accountData = App\CashCreditAccountType::getData($user->account_type_id);
                            echo !empty($accountData) ? $accountData->name : '-'; ?>
                        </td>
                        <td>{{!empty($user->QbAccountName) ? $user->QbAccountName : '-'}}</td>
                        <td>{{!empty($user->QbSubAccountName) ? $user->QbSubAccountName : '-'}}</td>
                        <td>
                            <div class='dropdown'>
                                <?php
                                $delete =  route('deletecashcreditdetailtype', $user->id);
                                $edit =  route('editcashcreditdetailtype', $user->id);
                                ?>
                                <?php if ($permissionAccountSubTypesEdit) { ?>
                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                <?php } ?>
                                <?php if ($permissionAccountSubTypesDelete) { ?>
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