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

<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\faculty-evaluation\resources\views/content/layouts-example/layouts-without-menu.blade.php ENDPATH**/ ?>