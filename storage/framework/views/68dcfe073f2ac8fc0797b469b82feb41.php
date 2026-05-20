<?php
$isMenu = false;
$navbarHideToggle = false;
?>



<?php $__env->startSection('title', 'Without menu - Layouts'); ?>

<?php $__env->startSection('content'); ?>

<!-- Layout Demo -->
<div class="layout-demo-wrapper">
  <div class="layout-demo-placeholder">
    <img src="<?php echo e(asset('assets/img/layouts/layout-without-menu-light.png')); ?>" class="img-fluid" alt="Layout without menu">
  </div>
  <div class="layout-demo-info">
    <h4>Layout without Menu (Navigation)</h4>
    <button class="btn btn-primary" type="button" onclick="history.back()">Go Back</button>
  </div>
</div>
<!--/ Layout Demo -->

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/contentNavbarLayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\sneat\html-laravel-free-internal\full-version\resources\views/content/layouts-example/layouts-without-menu.blade.php ENDPATH**/ ?>