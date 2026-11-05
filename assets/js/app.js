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
/* ==================================================================
   Shoop Moda Style — storefront interactions
   Product cards, wishlist, gallery, options, cart, countdown.
   ================================================================== */
(function () {
    'use strict';

    function postJson(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            });
        });
    }

    /* ---------- Wishlist toggle (cards + product page) ---------- */
    Array.prototype.forEach.call(document.querySelectorAll('[data-wish-slug]'), function (button) {
        button.addEventListener('click', function () {
            var slug = button.getAttribute('data-wish-slug');
            var url = button.getAttribute('data-wishlist-url');

            if (!slug || !url) {
                return;
            }

            button.disabled = true;

            postJson(url, { slug: slug }).then(function (data) {
                if (!data || data.ok !== true) {
                    return;
                }

                button.classList.toggle('is-active', !!data.active);
                button.setAttribute('aria-pressed', data.active ? 'true' : 'false');

                var label = button.querySelector('span');
                if (label) {
                    label.textContent = data.active ? 'Dans mes favoris' : 'Ajouter aux favoris';
                }

                button.setAttribute(
                    'aria-label',
                    data.active ? 'Retirer des favoris' : 'Ajouter aux favoris'
                );

                if (window.SM && window.SM.setBadge) {
                    window.SM.setBadge('wishlist', data.wishCount || 0);
                }
            }).finally(function () {
                button.disabled = false;
            });
        });
    });

    /* ---------- Product gallery thumbnails ---------------------- */
    var mainImage = document.getElementById('galleryMainImage');
    var thumbs = document.querySelectorAll('.gallery-thumb');

    if (mainImage && thumbs.length > 0) {
        Array.prototype.forEach.call(thumbs, function (thumb) {
            thumb.addEventListener('click', function () {
                var source = thumb.getAttribute('data-img');

                if (!source) {
                    return;
                }

                mainImage.src = source;

                Array.prototype.forEach.call(thumbs, function (item) {
                    item.classList.toggle('is-active', item === thumb);
                });
            });
        });
    }

    /* ---------- Size / colour selection ------------------------- */
    function bindOptions(buttons, hiddenId, attribute) {
        Array.prototype.forEach.call(buttons, function (button) {
            button.addEventListener('click', function () {
                Array.prototype.forEach.call(buttons, function (item) {
                    item.setAttribute('aria-checked', item === button ? 'true' : 'false');
                });

                var hidden = document.getElementById(hiddenId);
                if (hidden) {
                    hidden.value = button.getAttribute(attribute) || '';
                }
            });
        });
    }

    bindOptions(document.querySelectorAll('.size-btn'), 'selectedSize', 'data-size');
    bindOptions(document.querySelectorAll('.color-btn'), 'selectedColor', 'data-color');

    /* ---------- Quantity steppers ------------------------------- */
    Array.prototype.forEach.call(document.querySelectorAll('.qty-selector'), function (selector) {
        var input = selector.querySelector('.qty-input');

        if (!input) {
            return;
        }

        var minus = selector.querySelector('.qty-btn--minus');
        var plus = selector.querySelector('.qty-btn--plus');

        function apply(delta) {
            var value = parseInt(input.value, 10) || 1;
            value = Math.min(99, Math.max(1, value + delta));
            input.value = String(value);

            if (minus) {
                minus.disabled = value <= 1;
            }
        }

        if (minus) {
            minus.addEventListener('click', function () {
                apply(-1);
            });
        }

        if (plus) {
            plus.addEventListener('click', function () {
                apply(1);
            });
        }

        input.addEventListener('change', function () {
            var value = parseInt(input.value, 10) || 1;
            input.value = String(Math.min(99, Math.max(1, value)));
        });
    });

    /* ---------- Cart actions ------------------------------------ */
    var cartPage = document.querySelector('.cart-page');

    if (cartPage && cartPage.getAttribute('data-cart-update')) {
        cartPage.addEventListener('click', function (event) {
            var trigger = event.target.closest ? event.target.closest('[data-action]') : null;

            if (!trigger || !cartPage.contains(trigger)) {
                return;
            }

            var action = trigger.getAttribute('data-action');
            var item = trigger.closest('.cart-item');
            var slug = item ? item.getAttribute('data-slug') : '';
            var request = null;

            if (action === 'update' && slug) {
                request = postJson(cartPage.getAttribute('data-cart-update'), {
                    slug: slug,
                    quantity: parseInt(trigger.getAttribute('data-qty'), 10) || 1
                });
            } else if (action === 'remove' && slug) {
                request = postJson(cartPage.getAttribute('data-cart-remove'), { slug: slug });
            } else if (action === 'empty') {
                request = postJson(cartPage.getAttribute('data-cart-empty'), {});
            }

            if (!request) {
                return;
            }

            trigger.disabled = true;

            request.then(function (data) {
                if (data && data.ok) {
                    window.location.reload();
                }
            }).finally(function () {
                trigger.disabled = false;
            });
        });
    }

    /* ---------- Flash sale countdown ---------------------------- */
    var timer = document.getElementById('countdownTimer');

    if (timer) {
        var end = Date.parse(String(timer.getAttribute('data-end') || '').replace(' ', 'T'));

        function pad(value) {
            return value < 10 ? '0' + value : String(value);
        }

        function tick() {
            var remaining = Math.max(0, Math.floor((end - Date.now()) / 1000));
            var hours = Math.floor(remaining / 3600);
            var minutes = Math.floor((remaining % 3600) / 60);
            var seconds = remaining % 60;

            timer.textContent = pad(hours) + ' : ' + pad(minutes) + ' : ' + pad(seconds);

            if (remaining > 0) {
                window.setTimeout(tick, 1000);
            }
        }

        if (!isNaN(end)) {
            tick();
        }
    }
})();
