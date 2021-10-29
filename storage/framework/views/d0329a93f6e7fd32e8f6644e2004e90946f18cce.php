<?php
$menubars = \App\Facades\CustomHelper::get_admin_menus();
$main_bar = $menubars['main'] ?? '';
$page_class = explode('.', $template);
$current_path = $menubars['active'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e(config('app.name')); ?></title>

    <!-- Bootstrap -->
    <link href="<?php echo e(url('vendors/bootstrap/dist/css/bootstrap.min.css')); ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?php echo e(url('vendors/font-awesome/css/font-awesome.min.css')); ?>" rel="stylesheet">
    <!-- NProgress -->
    <link href="<?php echo e(url('vendors/nprogress/nprogress.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('vendors/animate.css/animate.min.css')); ?>" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="<?php echo e(CustomHelper::custom_asset_url('css/custom.css')); ?>" rel="stylesheet">
</head>

<body class="page-<?php echo e($page_class[0]); ?> <?php echo e(@$page_class[2] . (@$page_class[2] == 'register') ? ' login' : ''); ?>">
    <h1 class="text-center"><a href="<?php echo e(url('/')); ?>"><?php echo e(config('app.name')); ?></a></h1>
    <div class="login_wrapper">
        <div class="row">
            <div class="col-sm-12">
                <?php echo $__env->make('errors.flash', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php $__env->startSection('content'); ?><?php echo $__env->yieldSection(); ?>
            </div>
        </div>
    </div>
</body>
</html>
