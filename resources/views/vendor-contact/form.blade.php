@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Contact' : 'Add Contact'; ?>
@stop


@section('breadcrumbs')
    @include('menus.vendors')
@stop



@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Contact' : 'Add Contact'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('vendorcontact/update',$model->id);
                    else
                        $actionUrl = url('vendorcontact/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <input type="hidden" name="flagFromWhere" value="<?php echo $flagFromWhere; ?>">
             <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('vendor_id') ? 'has-error' :'' }}">
                        <div class="col-md-12 required row">
                        <?php echo Form::label('vendor_id', 'Vendors',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                        <?php 
                        $vendorId = $model->vendor_id;
                        $model->vendor_id = $model->company_name;
                        echo Form::text('vendor_id',$model->vendor_id,['class'=>'form-control fclient_id']); ?>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="hidden_vendor_id"  id="hidden_vendor_id" value="<?php echo $vendorId; ?>">
                <input type="hidden" name="company_name"  id="company_name" value="<?php echo $model->company_name; ?>">
            </div>


            <div class="col-md-12">
                <h4 style="background: #f3f3f3;padding: 10px;">Contact Details</h4>                    
            </div>

            
            <div class="col-md-12 container-contact">
                        <table class="tableexpense" style="border: 1px solid #ccc;width: 100%">
                            <thead>
                                <tr style="border-bottom: 1px solid #ccc;height: 39px;text-align: center;font-weight: bold;">
                                    <td style="border-right: 1px solid #ccc;width: 15%;">Contact Name</td>
                                    <td style="border-right: 1px solid #ccc;text-align: center;padding-left: 5px;width: 15%;">Position</td>
                                    <td style="border-right: 1px solid #ccc;width: 15%;">Cell Number</td>
                                    <td style="border-right: 1px solid #ccc;width: 15%;">Direct line</td>
                                    <td style="border-right: 1px solid #ccc;width: 15%;">Work</td>
                                    <td style="border-right: 1px solid #ccc;width: 15%;">Email</td>
                                    <?php if(empty($model->id)) { ?>
                                    <td style="width: 10%;">Action</td>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>

                            <?php if(empty($model->id)) { ?>

                                <tr id="tbtr-0" style="border-bottom: 1px solid #ccc;height: 39px;">
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[name][0]",'',['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>'name-0']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[personal_contact][0]",'',['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>'personal_contact-0']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[cell_number][0]",'',['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>'cell_number-0','onkeypress'=>'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[direct_line][0]",'',['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>'direct_line-0','onkeypress'=>'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[work][0]",'',['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>'work-0','onkeypress'=>'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::email("clientContact[email][0]",'',['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>'email-0']); ?>
                                    </td>
                                    <td style="text-align: center;"><a href="javascript:void(0)" class='btn btn-success btn-xs addmorecontact'>+</a>
                                    </td>
                                </tr>

                            <?php } else {  $i = 0;  foreach($dataContacts as $k => $v) {   ?>

                                 <tr id="tbtr-<?php echo $i; ?>" style="border-bottom: 1px solid #ccc;height: 39px;">
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[name][$i]",$v->name,['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>"name-$i"]); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[personal_contact][$i]",$v->personal_contact,['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>"personal_contact-$i"]); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[cell_number][$i]",$v->cell_number,['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>"cell_number-$i",'onkeypress'=>'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[direct_line][$i]",$v->direct_line,['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>"direct_line-$i",'onkeypress'=>'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[work][$i]",$v->work,['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>"work-$i",'onkeypress'=>'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::email("clientContact[email][$i]",$v->email,['class'=>'form-control invfield invfieldtbl unitpricefld','id'=>"email-$i"]); ?>
                                    </td>
                                </tr>

                            <?php  $i++; } } ?>

                            </tbody>
                        </table>
            </div>

            

            

            

            <div class="form-group col-md-12 btm-sub">
                            
                                <button type="submit" class="btn btn-success">
                                    <?php
                                        if(!$model->id)
                                            echo "Submit";
                                        else
                                            echo "Update";
                                        ?>
                                </button>
                            
                            <a class="btn btn-danger" href="{{url('vendorcontacts')}}" title="">Cancel</a>
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
                if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
                    return false;
                }
                return true;
            }
    
        $(document).ready(function() {


            $('select').change(function(){
                    if ($(this).val()!="")
                    {
                        $(this).valid();
                    }
                });

             $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
        

                

        $( "#vendor_id" ).autocomplete({
                select: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $("#hidden_vendor_id").val(ui.item.value);
                    $("#company_name").val(ui.item.label);
                    $('#loading').hide();
                },
                focus: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $("#vendor_id").val(ui.item.label);
                    $('#loading').hide();
                },
                change: function (event, ui)
                    {
                        if (ui.item == null || typeof (ui.item) == "undefined")
                        {
                            console.log("dsfdsf");
                            $('#loading').show();
                            $('#vendor_id').val("");
                            $('#loading').hide();
                        }
                    },
                source: <?php echo $dataClient; ?>,
                minLength:1,
                });



        $('#createforms').on('submit', function (event) {
            $('.fclient_id').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            
         });
        $('#createforms').validate({
              errorPlacement: function(error, element) {
                        if (element.hasClass('fprimary_contact_type'))
                        {
                        var pos = $('.fprimary_contact_type button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else
                        {
                        error.insertAfter(element);
                        }
                    }
           
        });


            countcontacts = 0;
            $(document).on("click",".addmorecontact",function (e) {
                    $('#loading').show();
                    countcontacts = countcontacts+1;
                    if(countcontacts == 0) { countcontacts = 1; }
                    e.preventDefault();
                var str  =  '<tr id="tbtr-'+countcontacts+'" style="border-bottom: 1px solid #ccc;height: 39px;"><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="name-'+countcontacts+'" name="clientContact[name]['+countcontacts+']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="personal_contact-'+countcontacts+'" name="clientContact[personal_contact]['+countcontacts+']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="cell_number-'+countcontacts+'" onkeypress="return isNumber(event)" name="clientContact[cell_number]['+countcontacts+']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="direct_line-'+countcontacts+'" onkeypress="return isNumber(event)" name="clientContact[direct_line]['+countcontacts+']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="work-'+countcontacts+'" onkeypress="return isNumber(event)" name="clientContact[work]['+countcontacts+']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="email-'+countcontacts+'" name="clientContact[email]['+countcontacts+']" type="email" value=""></td><td style="text-align: center;"><a href="javascript:void(0)" class="btn btn-success btn-xs addmorecontact" style="margin-right:5px">+</a><a style="" href="javascript:void(0)" class="btn btn-danger btn-xs removecontact" id="'+countcontacts+'">-</a></td></tr>';
                    $('table.tableexpense tbody').append(str);
                    $('#loading').hide();
                });

             $(document).on("click",".removecontact",function (e) {
                $('#loading').show();
                e.preventDefault();
                var id = $(this).attr('id');
                $("table.tableexpense tbody tr#tbtr-"+id).remove();
                $('#loading').hide();
            });

        

        });
        
</script>
@stop
