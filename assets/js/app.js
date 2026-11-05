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
                // The wishlist badge is a dot: keep it empty, only toggle it.
                if (!badge.classList.contains('badge--dot')) {
                    badge.textContent = String(value);
                }

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
        var daysEl = timer.querySelector('[data-count="days"]');
        var hoursEl = timer.querySelector('[data-count="hours"]');
        var minutesEl = timer.querySelector('[data-count="minutes"]');
        var secondsEl = timer.querySelector('[data-count="seconds"]');

        function pad(value) {
            return value < 10 ? '0' + value : String(value);
        }

        function tick() {
            var remaining = Math.max(0, Math.floor((end - Date.now()) / 1000));
            var days = Math.floor(remaining / 86400);
            var hours = Math.floor((remaining % 86400) / 3600);
            var minutes = Math.floor((remaining % 3600) / 60);
            var seconds = remaining % 60;

            if (daysEl) {
                daysEl.textContent = pad(days);
            }

            if (hoursEl) {
                hoursEl.textContent = pad(hours);
            }

            if (minutesEl) {
                minutesEl.textContent = pad(minutes);
            }

            if (secondsEl) {
                secondsEl.textContent = pad(seconds);
            }

            if (remaining > 0) {
                window.setTimeout(tick, 1000);
            }
        }

        if (!isNaN(end)) {
            tick();
        }
    }
})();
/* ==================================================================
   Shoop Moda Style — refresh interactions
   Add to cart without leaving the page, toast, catalogue sorting.
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

    var toast = document.getElementById('smToast');
    var toastTimer = null;

    function hideToast() {
        if (toast) {
            toast.hidden = true;
        }
    }

    function showToast(message) {
        if (!toast) {
            return;
        }

        var text = toast.querySelector('[data-toast-text]');

        if (text) {
            text.textContent = message;
        }

        toast.hidden = false;

        if (toastTimer) {
            window.clearTimeout(toastTimer);
        }

        toastTimer = window.setTimeout(hideToast, 6500);
    }

    if (toast) {
        var toastClose = toast.querySelector('[data-toast-close]');

        if (toastClose) {
            toastClose.addEventListener('click', hideToast);
        }
    }

    /* ---------- Add to cart (stay on the page) ------------------- */
    var cartForm = document.getElementById('cartForm');

    if (cartForm && window.fetch) {
        cartForm.addEventListener('submit', function (event) {
            event.preventDefault();

            var button = document.querySelector('.cart-actions .btn--add-cart');
            var payload = {};

            new FormData(cartForm).forEach(function (value, key) {
                payload[key] = value;
            });

            var quantity = document.querySelector('.cart-actions .qty-input');

            if (quantity) {
                payload.quantity = quantity.value;
            }

            if (button) {
                button.disabled = true;
            }

            postJson(cartForm.action, payload).then(function (data) {
                if (!data || data.ok !== true) {
                    showToast((data && data.error) || 'Ajout impossible, réessayez.');
                    return;
                }

                if (window.SM && window.SM.setBadge) {
                    window.SM.setBadge('cart', data.cartCount || 0);
                }

                showToast(data.message || 'Article ajouté au panier');
            }).catch(function () {
                // No JS/AJAX available: fall back to a normal form post.
                cartForm.submit();
            }).finally(function () {
                if (button) {
                    button.disabled = false;
                }
            });
        });
    }

    /* ---------- Catalogue: sort on change ----------------------- */
    Array.prototype.forEach.call(document.querySelectorAll('[data-auto-submit]'), function (field) {
        field.addEventListener('change', function () {
            if (field.form) {
                field.form.submit();
            }
        });
    });
})();

/* ==================================================================
   Shoop Moda Style — search suggestions, history, admin helpers
   ================================================================== */
(function () {
    'use strict';

    /* ---------- Search page: live suggestions ------------------- */
    var form = document.getElementById('siteSearchForm');

    if (form) {
        var input = document.getElementById('siteSearchInput');
        var box = document.getElementById('searchSuggestions');
        var suggestUrl = form.getAttribute('data-suggest-url') || '';
        var historyWrap = document.getElementById('searchHistory');
        var historyList = document.querySelector('[data-history-list]');
        var historyClear = document.querySelector('[data-history-clear]');
        var timer = null;
        var controller = null;
        var lastQuery = '';

        var STORAGE_KEY = 'sm_search_history';
        var MAX_HISTORY = 8;

        function readHistory() {
            try {
                var raw = window.localStorage.getItem(STORAGE_KEY);
                var list = raw ? JSON.parse(raw) : [];

                if (!Array.isArray(list)) {
                    return [];
                }

                return list.filter(function (value) {
                    return typeof value === 'string' && value !== '';
                }).slice(0, MAX_HISTORY);
            } catch (error) {
                return [];
            }
        }

        function writeHistory(list) {
            try {
                window.localStorage.setItem(STORAGE_KEY, JSON.stringify(list.slice(0, MAX_HISTORY)));
            } catch (error) {
                // Private mode: history simply is not remembered.
            }
        }

        function remember(query) {
            query = query.trim();

            if (query.length < 2) {
                return;
            }

            var list = readHistory().filter(function (value) {
                return value.toLowerCase() !== query.toLowerCase();
            });

            list.unshift(query);
            writeHistory(list);
        }

        function renderHistory() {
            if (!historyWrap || !historyList) {
                return;
            }

            var list = readHistory();

            historyList.textContent = '';

            if (list.length === 0) {
                historyWrap.hidden = true;

                return;
            }

            historyWrap.hidden = false;

            list.forEach(function (query) {
                var chip = document.createElement('button');

                chip.type = 'button';
                chip.className = 'search-history-chip';
                chip.textContent = query;

                chip.addEventListener('click', function () {
                    input.value = query;
                    remember(query);
                    form.submit();
                });

                historyList.appendChild(chip);
            });
        }

        function closeSuggestions() {
            if (!box) {
                return;
            }

            box.textContent = '';
            box.hidden = true;
            input.setAttribute('aria-expanded', 'false');
        }

        function renderSuggestions(items, suggestion) {
            if (!box) {
                return;
            }

            box.textContent = '';

            if (!items.length) {
                if (!suggestion) {
                    closeSuggestions();

                    return;
                }

                // Misspelled query: offer the closest real word instead.
                var hint = document.createElement('button');
                hint.type = 'button';
                hint.className = 'suggestion suggestion--hint';
                hint.setAttribute('role', 'option');

                var hintMain = document.createElement('span');
                hintMain.className = 'suggestion-main';

                var hintTitle = document.createElement('span');
                hintTitle.className = 'suggestion-title';
                hintTitle.textContent = 'Voulez-vous dire « ' + suggestion + ' » ?';

                hintMain.appendChild(hintTitle);
                hint.appendChild(hintMain);

                hint.addEventListener('click', function () {
                    if (input) {
                        input.value = suggestion;
                        remember(suggestion);
                    }

                    form.submit();
                });

                box.appendChild(hint);
                box.hidden = false;
                input.setAttribute('aria-expanded', 'true');

                return;
            }

            items.forEach(function (item) {
                var link = document.createElement('a');
                link.className = 'suggestion';
                link.href = item.url;
                link.setAttribute('role', 'option');

                if (item.image) {
                    var img = document.createElement('img');
                    img.src = item.image;
                    img.alt = '';
                    img.width = 40;
                    img.height = 50;
                    img.loading = 'lazy';
                    link.appendChild(img);
                }

                var main = document.createElement('span');
                main.className = 'suggestion-main';

                var title = document.createElement('span');
                title.className = 'suggestion-title';
                title.textContent = item.title;
                main.appendChild(title);

                if (item.category) {
                    var category = document.createElement('span');
                    category.className = 'suggestion-cat';
                    category.textContent = item.category;
                    main.appendChild(category);
                }

                link.appendChild(main);

                var price = document.createElement('span');
                price.className = 'suggestion-price';
                price.textContent = item.price;
                link.appendChild(price);

                box.appendChild(link);
            });

            box.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function loadSuggestions(query) {
            if (!suggestUrl || query.length < 2) {
                closeSuggestions();

                return;
            }

            if (controller) {
                controller.abort();
            }

            controller = typeof AbortController === 'function' ? new AbortController() : null;

            var options = {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            };

            if (controller) {
                options.signal = controller.signal;
            }

            window.fetch(suggestUrl + '?q=' + encodeURIComponent(query), options)
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (lastQuery !== query) {
                        return;
                    }

                    renderSuggestions((data && data.items) || [], data && data.suggestion);
                })
                .catch(function () {
                    // Aborted or offline: keep the last visible state.
                });
        }

        if (input) {
            input.addEventListener('input', function () {
                lastQuery = input.value.trim();

                if (timer) {
                    window.clearTimeout(timer);
                }

                timer = window.setTimeout(function () {
                    loadSuggestions(lastQuery);
                }, 180);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeSuggestions();
                }
            });
        }

        form.addEventListener('submit', function () {
            if (input) {
                remember(input.value);
            }
        });

        if (historyClear) {
            historyClear.addEventListener('click', function () {
                writeHistory([]);
                renderHistory();

                if (input) {
                    input.focus();
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (!box || box.hidden) {
                return;
            }

            if (!form.contains(event.target) && !box.contains(event.target)) {
                closeSuggestions();
            }
        });

        renderHistory();
    }

    /* ---------- Admin: image preview + confirm deletes ---------- */
    var imageInput = document.querySelector('[data-image-input]');
    var imagePreview = document.querySelector('[data-image-preview]');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            var file = imageInput.files && imageInput.files[0];

            if (!file || !/^image\//.test(file.type)) {
                return;
            }

            var reader = new FileReader();

            reader.addEventListener('load', function () {
                imagePreview.src = String(reader.result);
            });

            reader.readAsDataURL(file);
        });
    }

    Array.prototype.forEach.call(document.querySelectorAll('form[data-confirm]'), function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm') || 'Confirmer cette action ?')) {
                event.preventDefault();
            }
        });
    });
})();
