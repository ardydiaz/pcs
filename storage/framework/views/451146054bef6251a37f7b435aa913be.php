<?php
    $messages = collect($messages ?? [])
        ->filter(function ($toast) {
            return filled(data_get($toast, 'message'));
        })
        ->map(function ($toast) {
            return [
                'type' => data_get($toast, 'type', 'info'),
                'message' => data_get($toast, 'message', ''),
                'title' => data_get($toast, 'title'),
                'duration' => data_get($toast, 'duration'),
            ];
        })
        ->values();
    $containerId = 'dmToastRoot';
?>

<div id="<?php echo e($containerId); ?>" class="dm-toast-root" aria-live="polite" aria-atomic="true"></div>

<?php if (! $__env->hasRenderedOnce('46d25f87-77af-48f8-a7a2-8e74d7975c95')): $__env->markAsRenderedOnce('46d25f87-77af-48f8-a7a2-8e74d7975c95'); ?>
    <style>
        .dm-toast-root {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            z-index: 1090;
            max-width: min(360px, calc(100vw - 2.5rem));
            pointer-events: none;
        }

        .dm-toast {
            --dm-toast-icon-bg: rgba(59, 130, 246, 0.12);
            --dm-toast-icon-color: #1d4ed8;
            --dm-toast-accent: #60a5fa;
            --dm-toast-bg: #ffffff;
            background: var(--dm-toast-bg);
            border-radius: 0.45rem;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            box-shadow: rgba(0, 0, 0, 0.15) 0 2px 8px;
            min-width: 280px;
            pointer-events: auto;
            position: relative;
            overflow: hidden;
            animation: dmToastSlideIn 0.35s ease forwards;
        }

        .dm-toast.is-hiding {
            opacity: 0;
            transform: translateX(40px);
            transition: all 0.25s ease;
        }

        .dm-toast__icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--dm-toast-icon-bg);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .dm-toast__icon i {
            font-size: 1.2rem;
            color: var(--dm-toast-icon-color);
        }

        .dm-toast__content {
            flex: 1;
            color: #0f172a;
        }

        .dm-toast__title {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.15rem;
            color: #0f172a;
        }

        .dm-toast__message {
            font-size: 0.85rem;
            color: #475569;
        }

        .dm-toast__close {
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 1rem;
            margin-left: 0.5rem;
            cursor: pointer;
            padding: 0.15rem;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dm-toast__close:hover {
            color: #475569;
        }

        .dm-toast__accent {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 4px;
            width: 100%;
            background: var(--dm-toast-accent);
            transform-origin: left center;
            animation: dmToastProgress linear forwards;
        }

        .dm-toast--success {
            --dm-toast-icon-bg: rgba(16, 185, 129, 0.12);
            --dm-toast-icon-color: #047857;
            --dm-toast-accent: #22c55e;
        }

        .dm-toast--info {
            --dm-toast-icon-bg: rgba(59, 130, 246, 0.12);
            --dm-toast-icon-color: #1d4ed8;
            --dm-toast-accent: #3b82f6;
        }

        .dm-toast--warning {
            --dm-toast-icon-bg: rgba(251, 191, 36, 0.18);
            --dm-toast-icon-color: #b45309;
            --dm-toast-accent: #f97316;
        }

        .dm-toast--danger {
            --dm-toast-icon-bg: rgba(248, 113, 113, 0.18);
            --dm-toast-icon-color: #b91c1c;
            --dm-toast-accent: #ef4444;
        }

        @keyframes dmToastSlideIn {
            0% {
                opacity: 0;
                transform: translateX(40px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes dmToastProgress {
            from {
                transform: scaleX(1);
            }
            to {
                transform: scaleX(0);
            }
        }
    </style>
    <script>
        (function () {
            if (window.dmToast) {
                return;
            }

            const ROOT_ID = '<?php echo e($containerId); ?>';
            const DEFAULT_DURATION = 25000;
            const TYPE_META = {
                success: { slug: 'success', icon: 'bx bx-check-circle', title: 'Success' },
                info: { slug: 'info', icon: 'bx bx-info-circle', title: 'Heads up' },
                warning: { slug: 'warning', icon: 'bx bx-error', title: 'Please note' },
                danger: { slug: 'danger', icon: 'bx bx-x-circle', title: 'Something went wrong' },
            };

            function ensureRoot() {
                let root = document.getElementById(ROOT_ID);
                if (!root) {
                    root = document.createElement('div');
                    root.id = ROOT_ID;
                    root.className = 'dm-toast-root';
                    document.body.appendChild(root);
                }
                return root;
            }

            function removeToast(toast) {
                if (!toast || toast.dataset.dismissed === 'true') {
                    return;
                }

                toast.dataset.dismissed = 'true';

                if (toast.__hideTimer) {
                    clearTimeout(toast.__hideTimer);
                    toast.__hideTimer = null;
                }

                toast.classList.add('is-hiding');

                let removed = false;
                const handleRemove = () => {
                    if (removed) {
                        return;
                    }
                    removed = true;
                    toast.remove();
                };

                toast.addEventListener('transitionend', handleRemove, { once: true });
                toast.addEventListener('animationend', handleRemove, { once: true });
                setTimeout(handleRemove, 350);
            }

            function showToast({ message = '', title = '', type = 'info', duration = null } = {}) {
                const meta = TYPE_META[type] || TYPE_META.info;
                const toastDuration = Number(duration) > 0 ? Number(duration) : DEFAULT_DURATION;
                const toast = document.createElement('div');
                toast.className = `dm-toast dm-toast--${meta.slug}`;

                const iconWrap = document.createElement('div');
                iconWrap.className = 'dm-toast__icon';
                iconWrap.innerHTML = `<i class="${meta.icon}"></i>`;

                const content = document.createElement('div');
                content.className = 'dm-toast__content';

                const titleEl = document.createElement('div');
                titleEl.className = 'dm-toast__title';
                titleEl.textContent = title || meta.title;
                content.appendChild(titleEl);

                if (message) {
                    const messageEl = document.createElement('div');
                    messageEl.className = 'dm-toast__message';
                    messageEl.textContent = message;
                    content.appendChild(messageEl);
                }

                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'dm-toast__close';
                closeBtn.setAttribute('aria-label', 'Dismiss notification');
                closeBtn.innerHTML = '&times;';
                closeBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    removeToast(toast);
                });

                const accent = document.createElement('span');
                accent.className = 'dm-toast__accent';
                accent.style.animationDuration = `${toastDuration}ms`;

                toast.appendChild(iconWrap);
                toast.appendChild(content);
                toast.appendChild(closeBtn);
                toast.appendChild(accent);

                ensureRoot().prepend(toast);

                let remaining = toastDuration;
                let startTime = Date.now();
                let hideTimer = setTimeout(() => removeToast(toast), remaining);
                toast.__hideTimer = hideTimer;

                toast.addEventListener('mouseenter', () => {
                    if (toast.dataset.dismissed === 'true') {
                        return;
                    }
                    accent.style.animationPlayState = 'paused';
                    clearTimeout(hideTimer);
                    toast.__hideTimer = null;
                    remaining -= Date.now() - startTime;
                    remaining = Math.max(0, remaining);
                    if (remaining <= 0) {
                        removeToast(toast);
                    }
                });
                toast.addEventListener('mouseleave', () => {
                    if (toast.dataset.dismissed === 'true' || remaining <= 0) {
                        return;
                    }
                    accent.style.animationPlayState = 'running';
                    startTime = Date.now();
                    hideTimer = setTimeout(() => removeToast(toast), remaining);
                    toast.__hideTimer = hideTimer;
                });

                return toast;
            }

            window.dmToast = {
                show: showToast,
                dismiss: removeToast,
            };
        })();
    </script>
<?php endif; ?>

<?php if($messages->isNotEmpty()): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const flashToasts = <?php echo json_encode($messages, 15, 512) ?>;
            flashToasts.forEach((toast) => window.dmToast?.show(toast));
        });
    </script>
<?php endif; ?>
<?php /**PATH /var/www/postclasssurvey.mcu.edu.ph/resources/views/components/dm-toast.blade.php ENDPATH**/ ?>