<?php
$container = 'container-fluid';
$containerNav = 'container-fluid';
?>



<?php $__env->startSection('title', 'Fluid - Layouts'); ?>

<?php $__env->startSection('content'); ?>
<!-- Layout Demo -->
<div class="layout-demo-wrapper">
  <div class="layout-demo-placeholder">
    <img src="<?php echo e(asset('assets/img/layouts/layout-fluid-light.png')); ?>" class="img-fluid" alt="Layout fluid">
  </div>
  <div class="layout-demo-info">
    <h4>Layout fluid</h4>
    <p>Fluid layout sets a <code>100% width</code> at each responsive breakpoint.</p>
  </div>
</div>
<!--/ Layout Demo -->
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/contentNavbarLayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\sneat\html-laravel-free-internal\full-version\resources\views/content/layouts-example/layouts-fluid.blade.php ENDPATH**/ ?>