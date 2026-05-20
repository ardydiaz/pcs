<?php
$container = 'container-xxl';
$containerNav = 'container-xxl';
?>



<?php $__env->startSection('title', 'Container - Layouts'); ?>

<?php $__env->startSection('content'); ?>
<!-- Layout Demo -->
<div class="layout-demo-wrapper">
  <div class="layout-demo-placeholder">
    <img src="<?php echo e(asset('assets/img/layouts/layout-container-light.png')); ?>" class="img-fluid" alt="Layout container">
  </div>
  <div class="layout-demo-info">
    <h4>Layout container</h4>
    <p>Container layout sets a <code>max-width</code> at each responsive breakpoint.</p>
  </div>
</div>
<!--/ Layout Demo -->


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/contentNavbarLayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\sneat\html-laravel-free-internal\full-version\resources\views/content/layouts-example/layouts-container.blade.php ENDPATH**/ ?>