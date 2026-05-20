<?php
use Illuminate\Support\Facades\Vite;
?>
<!-- laravel style -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/js/helpers.js']); ?>

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/js/config.js']); ?>

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<?php /**PATH C:\laragon\www\faculty-evaluation\resources\views/layouts/sections/scriptsIncludes.blade.php ENDPATH**/ ?>