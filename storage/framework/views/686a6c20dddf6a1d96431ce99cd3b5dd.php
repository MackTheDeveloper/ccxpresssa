<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Login</div>

                <div class="panel-body">
                    <form id="createForm" class="form-horizontal" method="POST" action="<?php echo e(route('login')); ?>">
                        <?php echo e(csrf_field()); ?>


                        <div class="form-group<?php echo e($errors->has('email') ? ' has-error' : ''); ?>">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="<?php echo e(old('email')); ?>" required autofocus>

                                <?php if($errors->has('email')): ?>
                                <span class="help-block">
                                    <strong><?php echo e($errors->first('email')); ?></strong>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group<?php echo e($errors->has('password') ? ' has-error' : ''); ?>">
                            <label for="password" class="col-md-4 control-label">Password</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>

                                <?php if($errors->has('password')): ?>
                                <span class="help-block">
                                    <strong><?php echo e($errors->first('password')); ?></strong>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group" style="display: block">
                        <label class="col-md-4 control-label"></label>
                            <div class="col-md-6">
                                <div class="field-recaptcha required has-error" aria-required="true">
                                    <div class="g-recaptcha" data-callback="recaptchaCallback" data-sitekey="<?php echo Config::get('app.googleReCaptchaKeys')['siteKey']; ?>"></div>
                                    <div style="display: none;color:#a94442" class="help-block-recaptcha help-block">Please confirm that you are not a bot.</div>
                                </div>
                                <?php //echo $form->field($model, 'recaptcha')->textInput(['maxlength' => true,'placeholder'=>'Your Email','data-sitekey'=>Yii::$app->params['googleReCaptchaKeys']['siteKey'],'class'=>'g-recaptcha'])->label(false); 
                                ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>> Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>

                                <a class="btn btn-link" href="<?php echo e(route('password.request')); ?>">
                                    Forgot Your Password?
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<script src="https://code.jquery.com/jquery-3.6.0.slim.min.js" integrity="sha256-u7e5khyithlIdTpu22PHhENmPcRdFiHRjhAuHcs05RI=" crossorigin="anonymous"></script>
<script src="https://www.google.com/recaptcha/api.js"></script>
<script type="text/javascript">
    function recaptchaCallback() {
        $('.help-block-recaptcha').hide();
    };  
    $(document).ready(function() {
        $('#contactus-captcha-image').click();
        $("#createForm").submit(function(e) {
            //e.preventDefault();
            if (!grecaptcha.getResponse()) {
                    $('.help-block-recaptcha').show();
                    return false;
                } else {
                    /* $('.field-recaptcha').removeClass('has-error');
                    $('.help-block-recaptcha').hide();
                    var form1 = $('#createForm');
                    if (form1.find('.has-error').length) {
                        return false;
                    } else {
                        form1.submit();
                    } */
                    return true;
                }
        })


    });
</script>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/auth/login.blade.php ENDPATH**/ ?>