<?php
$isNavbar = false;
?>



<?php $__env->startSection('title', 'Without navbar - Layouts'); ?>

<?php $__env->startSection('content'); ?>

<!-- Layout Demo -->
<div class="layout-demo-wrapper">
  <div class="layout-demo-placeholder">
    <img src="<?php echo e(asset('assets/img/layouts/layout-without-navbar-light.png')); ?>" class="img-fluid" alt="Layout without navbar">
  </div>
  <div class="layout-demo-info">
    <h4>Layout without Navbar</h4>
    <p>Layout does not contain Navbar component.</p>
  </div>
</div>
<!--/ Layout Demo -->

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/contentNavbarLayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\sneat\html-laravel-free-internal\full-version\resources\views/content/layouts-example/layouts-without-navbar.blade.php ENDPATH**/ ?>