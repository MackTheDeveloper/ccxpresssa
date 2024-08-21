<?php
use yii\helpers\Html;
?>

    <?php
    if(count($data)>0){ ?>
        <input type="hidden" name="Permissions[flagsubmit]" value="1">
    
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title"><b>Permissions</b></h3><div class="box-tools pull-right"> <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button> </div>
                        </div>
                        <div class="box-body">
                            <div id="tbl1" class="grid-view table-responsive">
                                <table class="table table-striped table-bordered">
                                    <th style="width: 20%">Name</th>
                                    <th style="width: 80%">Permissions</th>
                                    <?php
                                        foreach($data as $key=>$value){
                                    ?>
                                        <tr>
                                            <td class=""> <?php echo $master[$key]; ?></td>
                                            <?php $checked = (isset($query[$key]))?'checked':''; ?>
                                            <td class="mainContainer"><input type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" <?php echo $checked; ?> class="btn btn-success parentSwitch" name="permissions[<?php echo $key; ?>]" >
                                                <?php if(count($value)>0){
                                                        $hide = (!empty($checked))?'hide':'';
                                                        $rvhide = (empty($checked))?'hide':''; ?>
                                                <a href="#" class="<?php echo $hide; ?> advance">Advance Options</a>
                                                    <div class="<?php echo $rvhide; ?> childList">
                                                        <?php
                                                        foreach($value as $k=>$v){
                                                            echo "<div class='childPair'>";
                                                            echo "<span>".$v."</span>";

                                                            $ckd = (isset($query[$key][$k]) && ($query[$key][$k] == 1))?'checked':'';
                                                            ?>
                                                            <input type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" <?php echo $ckd; ?> class="btn btn-success childSwitch" name="childmodule[<?php echo $key; ?>][<?php echo $k; ?>]" >
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php
    }
    ?>