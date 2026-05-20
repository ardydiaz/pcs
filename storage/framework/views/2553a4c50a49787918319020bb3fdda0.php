<?php $__env->startSection('title', 'Error - Pages'); ?>

<?php $__env->startSection('page-style'); ?>
<!-- Page -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/scss/pages/page-misc.scss']); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<!-- Error -->
<div class="container-xxl container-p-y">
  <div class="misc-wrapper">
    <h1 class="mb-2 mx-2" style="line-height: 6rem;font-size: 6rem;">404</h1>
    <h4 class="mb-2 mx-2">Page Not Found</h4>
    <p class="mb-6 mx-2">We couldn't find the page you are looking for.</p>
    <a href="<?php echo e(url('/')); ?>" class="btn btn-primary">Back to home</a>
    <div class="mt-6">
      <img src="<?php echo e(asset('assets/img/illustrations/page-not-found.svg')); ?>" alt="page not found" width="500" class="img-fluid">
    </div>
  </div>
</div>
<!-- /Error -->
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/blankLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/pages/pages-misc-error.blade.php ENDPATH**/ ?>