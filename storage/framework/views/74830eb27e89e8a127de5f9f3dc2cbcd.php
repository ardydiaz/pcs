<?php $__env->startSection('title', 'Under Maintenance - Pages'); ?>

<?php $__env->startSection('page-style'); ?>
<!-- Page -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/scss/pages/page-misc.scss']); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!--Under Maintenance -->
<div class="container-xxl container-p-y">
  <div class="misc-wrapper">
    <h3 class="mb-2 mx-2">Under Maintenance! 🚧</h3>
    <p class="mb-6 mx-2">
      Sorry for the inconvenience but we're performing some maintenance at the moment
    </p>
    <a href="<?php echo e(url('/')); ?>" class="btn btn-primary">Back to home</a>
    <div class="mt-6">
      <img src="<?php echo e(asset('assets/img/illustrations/girl-doing-yoga-light.png')); ?>" alt="girl-doing-yoga-light" width="500" class="img-fluid">
    </div>
  </div>
</div>
<!-- /Under Maintenance -->
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/blankLayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\sneat\html-laravel-free-internal\full-version\resources\views/content/pages/pages-misc-under-maintenance.blade.php ENDPATH**/ ?>