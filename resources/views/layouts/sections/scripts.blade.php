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
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduceMotion) {
      return;
    }

    const animatePageSections = () => {
      const contentRoot = document.querySelector('.layout-page .content-wrapper > .container-xxl.container-p-y, .layout-page .content-wrapper > .container-fluid.container-p-y');

      if (!contentRoot) {
        return;
      }

      const isRevealCandidate = (element) => {
        if (!(element instanceof HTMLElement)) {
          return false;
        }

        if (element.matches('script, style, .modal, .offcanvas, .dropdown-menu, .visually-hidden, .container-fluid, .container-xxl, .row')) {
          return false;
        }

        if (element.querySelector('.modal, .offcanvas, .dropdown-menu')) {
          return false;
        }

        return true;
      };

      const directSections = Array.from(contentRoot.children).filter(isRevealCandidate);
      const nestedSections = Array.from(contentRoot.querySelectorAll([
        ':scope > .container-fluid > *',
        ':scope > .row > [class*="col-"]',
        ':scope > .row > [class*="col-"] > .card',
        ':scope > .card',
        ':scope > .modern-page-hero',
        ':scope > .reports-filter-panel',
        ':scope > .evaluation-card',
        ':scope > .faculty-directory-hero',
        ':scope > .data-table-shell',
      ].join(','))).filter(isRevealCandidate);

      const revealTargets = Array.from(new Set([...directSections, ...nestedSections]))
        .filter((element) => !element.closest('.modal, .offcanvas, .dropdown-menu'))
        .slice(0, 36);

      if (!revealTargets.length) {
        return;
      }

      revealTargets.forEach((element, index) => {
        if (element.classList.contains('ui-page-enter')) {
          return;
        }

        element.classList.add('ui-page-enter');
        element.style.setProperty('--ui-enter-delay', `${Math.min(index * 55, 360)}ms`);
      });

      if (!('IntersectionObserver' in window)) {
        window.requestAnimationFrame(() => {
          revealTargets.forEach((element) => element.classList.add('is-visible'));
        });
        return;
      }

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          window.requestAnimationFrame(() => {
            entry.target.classList.add('is-visible');
          });
          observer.unobserve(entry.target);
        });
      }, {
        root: null,
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.08,
      });

      revealTargets.forEach((element) => observer.observe(element));
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', animatePageSections, { once: true });
    } else {
      animatePageSections();
    }

    document.addEventListener('show.bs.modal', (event) => {
      const modal = event.target;
      if (!(modal instanceof HTMLElement)) {
        return;
      }

      modal.classList.remove('ui-page-enter', 'is-visible');
      modal.querySelectorAll('.ui-page-enter').forEach((element) => {
        element.classList.remove('ui-page-enter', 'is-visible');
        element.style.removeProperty('--ui-enter-delay');
      });
    });
  })();
</script>
@endauth

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
