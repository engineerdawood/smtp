<?php $__env->startSection('title', "Login"); ?>

<?php $__env->startSection('content'); ?>
    <div class="animate form ">
        <section class="login_content">
            <form class="" role="form" method="POST" action="<?php echo e(url('/login')); ?>">
                <?php echo csrf_field(); ?>

                <h1>Login</h1>
                <div>
                    <input type="email" class="form-control" placeholder="Email" name="email" value="<?php echo e(old('email')); ?>" required autofocus />
                </div>
                <div>
                    <input type="password" class="form-control" placeholder="Password" name="password" required="" />
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember"> Remember Me
                    </label>
                </div>
                <div>
                    <input type="submit" class="btn btn-default submit" value="Log In" />
                    <a class="reset_pass" href="<?php echo e(url('/password/reset')); ?>">Forgot Your Password?</a>
                </div>

                <div class="clearfix"></div>

                <div class="separator">
                    <?php if(helper()->getSettings('enable_register') == 1): ?>
                        <p class="change_link">New to site?
                            <a href="<?php echo e(url('/register')); ?>" class="to_register">Create new account</a>
                        </p>
                        <div class="clearfix"></div>
                        <br />
                    <?php endif; ?>
                    <div>
                        <?php echo $__env->make('layouts.copyright', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                    </div>
                </div>
            </form>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminlogin', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>