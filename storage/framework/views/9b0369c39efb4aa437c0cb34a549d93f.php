<?php
$containerFooter = !empty($containerNav) ? $containerNav : 'container-fluid';
?>

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="<?php echo e($containerFooter); ?>">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        © <script>document.write(new Date().getFullYear())</script> <a href="https://mcu.edu.ph/" target="_blank" class="footer-link">Manila Central University</a>
      </div>
      <div class="d-none d-lg-inline-block">
        <!-- <a href="<?php echo e(config('variables.licenseUrl') ? config('variables.licenseUrl') : '#'); ?>" class="footer-link me-4" target="_blank">License</a> -->
         <span>Information Technology Department</span>
      </div>
    </div>
  </div>
</footer>
<!--/ Footer-->
<?php /**PATH /var/www/postclasssurvey.mcu.edu.ph/resources/views/layouts/sections/footer/footer.blade.php ENDPATH**/ ?>