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
    const keepAliveUrl = @json(route('session.keep-alive'));
    const maintenanceStatusUrl = @json(route('maintenance.status'));
    const currentUserRole = @json(strtolower((string) auth()->user()?->role));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const keepAliveMs = Math.min(Math.max(Math.floor(timeoutMs / 2), 30000), 300000);
    let timeoutId;
    let lastActivityAt = Date.now();

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
      lastActivityAt = Date.now();
      window.clearTimeout(timeoutId);
      timeoutId = window.setTimeout(logoutAfterIdle, timeoutMs);
    };

    const keepSessionAlive = () => {
      if (!csrfToken || Date.now() - lastActivityAt >= timeoutMs) {
        return;
      }

      window.fetch(keepAliveUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
      });
    };

    const checkMaintenanceMode = () => {
      if (currentUserRole === 'admin') {
        return;
      }

      window.fetch(maintenanceStatusUrl, {
        headers: {
          'Accept': 'application/json',
        },
        credentials: 'same-origin',
      })
        .then((response) => response.ok ? response.json() : null)
        .then((payload) => {
          if (!payload || !payload.enabled || payload.is_admin) {
            return;
          }

          if (window.location.pathname !== new URL(payload.redirect_url, window.location.origin).pathname) {
            window.location.assign(payload.redirect_url);
          }
        })
        .catch(() => {});
    };

    ['click', 'keydown', 'input', 'change', 'scroll', 'touchstart'].forEach((eventName) => {
      window.addEventListener(eventName, resetIdleTimer, { passive: true });
    });

    resetIdleTimer();
    window.setInterval(keepSessionAlive, keepAliveMs);
    window.setTimeout(checkMaintenanceMode, 3000);
    window.setInterval(checkMaintenanceMode, 10000);
  })();
</script>
@endauth
