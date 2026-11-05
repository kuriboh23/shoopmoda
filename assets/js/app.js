/* ==================================================================
   Shoop Moda Style — app.js
   Vanilla JS only. Loaded with `defer`, no inline scripts (strict CSP).
   ================================================================== */
(function () {
    'use strict';

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------------------------------------------------------------
       1. Left drawer
       --------------------------------------------------------------- */
    var drawer = document.getElementById('drawer');
    var overlay = document.getElementById('drawerOverlay');
    var toggle = document.getElementById('drawerToggle');
    var closeBtn = document.getElementById('drawerClose');

    if (drawer && overlay && toggle) {
        var isOpen = false;
        var lastFocused = null;
        var overlayTimer = null;

        var focusableSelector = [
            'a[href]',
            'button:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            '[tabindex]:not([tabindex="-1"])'
        ].join(',');

        function getFocusable() {
            return Array.prototype.filter.call(
                drawer.querySelectorAll(focusableSelector),
                function (el) {
                    return el.offsetParent !== null || el === drawer;
                }
            );
        }

        function openDrawer() {
            if (isOpen) {
                return;
            }

            isOpen = true;
            lastFocused = document.activeElement;

            if (overlayTimer) {
                window.clearTimeout(overlayTimer);
                overlayTimer = null;
            }

            drawer.classList.add('is-open');
            drawer.setAttribute('aria-hidden', 'false');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.classList.add('drawer-open');

            overlay.hidden = false;
            window.requestAnimationFrame(function () {
                overlay.classList.add('is-open');
            });

            // Focus once the drawer is visible (two frames also covers
            // browsers that apply the visibility change on the next paint).
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    if (closeBtn) {
                        closeBtn.focus({ preventScroll: true });
                    }
                });
            });
        }

        function closeDrawer(restoreFocus) {
            if (!isOpen) {
                return;
            }

            isOpen = false;
            drawer.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('drawer-open');
            overlay.classList.remove('is-open');

            overlayTimer = window.setTimeout(function () {
                if (!isOpen) {
                    overlay.hidden = true;
                }
            }, reducedMotion ? 0 : 260);

            if (restoreFocus !== false) {
                if (lastFocused && typeof lastFocused.focus === 'function') {
                    lastFocused.focus({ preventScroll: true });
                } else {
                    toggle.focus({ preventScroll: true });
                }
            }
        }

        toggle.addEventListener('click', openDrawer);

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                closeDrawer();
            });
        }

        overlay.addEventListener('click', function () {
            closeDrawer();
        });

        // Close after following an in-drawer link.
        drawer.addEventListener('click', function (event) {
            var link = event.target.closest ? event.target.closest('a[href]') : null;

            if (link) {
                closeDrawer(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (!isOpen) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closeDrawer();
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            var focusable = getFocusable();

            if (focusable.length === 0) {
                event.preventDefault();
                drawer.focus();
                return;
            }

            var first = focusable[0];
            var last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        // Desktop: never leave the drawer stuck open after a resize.
        var desktopQuery = window.matchMedia('(min-width: 768px)');

        function handleDesktopChange(event) {
            if (event.matches) {
                closeDrawer(false);
            }
        }

        if (typeof desktopQuery.addEventListener === 'function') {
            desktopQuery.addEventListener('change', handleDesktopChange);
        } else if (typeof desktopQuery.addListener === 'function') {
            desktopQuery.addListener(handleDesktopChange);
        }
    }

    /* ---------------------------------------------------------------
       2. Hero slider (CSS scroll-snap + dots)
       --------------------------------------------------------------- */
    var track = document.getElementById('heroTrack');

    if (track) {
        var slides = Array.prototype.slice.call(track.children);
        var dots = Array.prototype.slice.call(document.querySelectorAll('.hero-dot'));
        var ticking = false;

        function setActive(index) {
            dots.forEach(function (dot, i) {
                var active = i === index;
                dot.classList.toggle('is-active', active);

                if (active) {
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.removeAttribute('aria-current');
                }
            });
        }

        function activeIndex() {
            var center = track.scrollLeft + track.clientWidth / 2;
            var best = 0;
            var bestDistance = Infinity;

            slides.forEach(function (slide, i) {
                var slideCenter = (slide.offsetLeft - track.offsetLeft) + slide.clientWidth / 2;
                var distance = Math.abs(slideCenter - center);

                if (distance < bestDistance) {
                    bestDistance = distance;
                    best = i;
                }
            });

            return best;
        }

        track.addEventListener('scroll', function () {
            if (ticking) {
                return;
            }

            ticking = true;

            window.requestAnimationFrame(function () {
                setActive(activeIndex());
                ticking = false;
            });
        }, { passive: true });

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                var slide = slides[i];

                if (!slide) {
                    return;
                }

                track.scrollTo({
                    left: slide.offsetLeft - track.offsetLeft,
                    behavior: reducedMotion ? 'auto' : 'smooth'
                });

                setActive(i);
            });
        });
    }

    /* ---------------------------------------------------------------
       3. Public helper: badge counters (used by cart/wishlist later)
       --------------------------------------------------------------- */
    function setBadge(name, count) {
        var value = Number(count) || 0;

        Array.prototype.forEach.call(
            document.querySelectorAll('[data-badge="' + name + '"]'),
            function (badge) {
                badge.textContent = String(value);
                badge.hidden = value <= 0;
            }
        );
    }

    window.SM = window.SM || {};
    window.SM.setBadge = setBadge;
})();
