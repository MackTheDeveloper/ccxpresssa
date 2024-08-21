@extends('layouts.custom')

@section('title')
Account Types
@stop

<?php
$permissionAccountTypesEdit = App\User::checkPermission(['update_account_types'], '', auth()->user()->id);
$permissionAccountTypesDelete = App\User::checkPermission(['delete_account_types'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.account-types')
@stop

@section('content')
<section class="content-header">
    <h1>Account Types</h1>
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
                        <th>System Type</th>
                        <th>QuickBook Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cashcredit as $user)
                    <tr data-editlink="{{ route('editcashcreditaccounttype',[$user->id]) }}" id="<?php echo $user->id; ?>" class="edit-row">
                        <td style="display: none;">{{$user->id}}</td>
                        <td>{{$user->name}}</td>
                        <td>{{!empty($user->QbAccountName) ? $user->QbAccountName : '-'}}</td>
                        <td>
                            <div class='dropdown'>
                                <?php
                                $delete =  route('deletecashcreditaccounttype', $user->id);
                                $edit =  route('editcashcreditaccounttype', $user->id);
                                ?>
                                <?php if ($permissionAccountTypesEdit) { ?>
                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                <?php } ?>
                                <?php if ($permissionAccountTypesDelete) { ?>
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