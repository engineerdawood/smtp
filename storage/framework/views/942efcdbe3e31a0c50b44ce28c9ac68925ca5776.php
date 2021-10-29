<div class="mainAlertContainer">
    <?php if(session()->has('custom_flash_notification')): ?>
        <?php $__currentLoopData = get_custom_flash('custom_flash_notification'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flash): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="alert alert-<?php echo e($flash['level']); ?>">
                <button type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-hidden="true">&times;</button>

                <?php echo $flash['message']; ?>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <?php if(isset($errors) && count($errors) > 0): ?>
        <div class="alert alert-danger">
            <div class="container">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <div class="container">
                    <ul class="errors-list">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
