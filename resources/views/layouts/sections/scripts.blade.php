<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/js/menu.js'])

@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

@auth
<script>
  (() => {
    const timeoutMs = {{ (int) config('session.lifetime', 120) * 60 * 1000 }};
    const loginUrl = @json(route('login'));
    const logoutUrl = @json(route('logout'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let timeoutId;

    const logoutAfterIdle = () => {
      if (!csrfToken) {
        window.location.assign(loginUrl);
        return;
      }

      window.fetch(logoutUrl, {
        method: 'POST',
        headers: {
          'Accept': 'text/html',
          'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
      }).finally(() => {
        window.location.assign(loginUrl);
      });
    };

    const resetIdleTimer = () => {
      window.clearTimeout(timeoutId);
      timeoutId = window.setTimeout(logoutAfterIdle, timeoutMs);
    };

    ['click', 'keydown', 'scroll', 'touchstart'].forEach((eventName) => {
      window.addEventListener(eventName, resetIdleTimer, { passive: true });
    });

    resetIdleTimer();
  })();
</script>
@endauth
