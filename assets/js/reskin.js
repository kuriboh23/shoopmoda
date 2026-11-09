/* ==================================================================
   RESKIN — dashboard behaviour
   ------------------------------------------------------------------
   Vanilla JS, loaded with defer. No inline script, no framework.
   The schema lives in the DOM (data-rs-*), so this file works for any
   client website without being edited.
   ================================================================== */
(function () {
    'use strict';

    var app = document.getElementById('reskinApp');

    /* Swatch colours on the login page are the only thing needed outside
       the dashboard. */
    Array.prototype.forEach.call(
        document.querySelectorAll('[data-rs-swatch-color]'),
        function (swatch) {
            swatch.style.background = swatch.getAttribute('data-rs-swatch-color') || '#CCCCCC';
        }
    );

    if (!app) {
        return;
    }

    /* ---------------------------------------------------------------
       Setup
       --------------------------------------------------------------- */
    var preview = app.querySelector('[data-rs-preview]');
    var stage = app.querySelector('[data-rs-stage]');
    var inputs = Array.prototype.slice.call(app.querySelectorAll('[data-rs-input]'));
    var contentInputs = Array.prototype.slice.call(app.querySelectorAll('[data-rs-content]'));
    var statusEl = app.querySelector('[data-rs-status]');
    var statusText = app.querySelector('[data-rs-status-text]');
    var applyButton = app.querySelector('[data-rs-apply]');
    var applyLabel = app.querySelector('[data-rs-apply-label]');
    var applyState = app.querySelector('[data-rs-apply-state]');
    var undoButton = app.querySelector('[data-rs-undo]');
    var redoButton = app.querySelector('[data-rs-redo]');
    var themeNameInput = app.querySelector('[data-rs-theme-name]');
    var themeList = app.querySelector('[data-rs-theme-list]');
    var themeEmpty = app.querySelector('[data-rs-theme-empty]');
    var libraryCount = app.querySelector('[data-rs-library-count]');
    var csrf = app.getAttribute('data-csrf') || '';
    var frameReady = false;
    var dirty = false;
    var busy = false;
    var toastTimer = null;
    var frameTimer = null;
    var historyStates = [];
    var historyIndex = -1;
    var historyTimer = null;
    var restoringHistory = false;
    var HISTORY_LIMIT = 60;
    var HISTORY_DELAY = 350;

    var endpoints = {
        save: app.getAttribute('data-save-url'),
        reset: app.getAttribute('data-reset-url'),
        apply: app.getAttribute('data-apply-url'),
        disable: app.getAttribute('data-disable-url'),
        themeSave: app.getAttribute('data-theme-save-url'),
        themeLoad: app.getAttribute('data-theme-load-url'),
        themeDelete: app.getAttribute('data-theme-delete-url'),
        snapshot: app.getAttribute('data-snapshot-url')
    };

    /* ---------------------------------------------------------------
       Small helpers
       --------------------------------------------------------------- */
    function inputFor(key) {
        return app.querySelector('[data-rs-input="' + key + '"]');
    }

    function inputValue(input) {
        if (!input) {
            return '';
        }

        if (input.getAttribute('data-rs-type') === 'toggle') {
            return input.checked
                ? (input.getAttribute('data-rs-on') || 'on')
                : (input.getAttribute('data-rs-off') || 'off');
        }

        return input.value;
    }

    function cssValue(input) {
        if (input.getAttribute('data-rs-type') === 'range') {
            return input.value + (input.getAttribute('data-rs-unit') || '');
        }

        if (input.tagName === 'SELECT') {
            var option = input.options[input.selectedIndex];

            return option && option.getAttribute('data-rs-css-value')
                ? option.getAttribute('data-rs-css-value')
                : input.value;
        }

        return input.value;
    }

    function values() {
        var result = {};

        inputs.forEach(function (input) {
            result[input.getAttribute('data-rs-input')] = inputValue(input);
        });

        return result;
    }

    function contentValues() {
        var result = {};

        contentInputs.forEach(function (input) {
            result[input.getAttribute('data-rs-content')] = input.value;
        });

        return result;
    }

    function messagesFromInput(input) {
        return String(input.value || '').split(/\r\n|\r|\n/).map(function (line) {
            return line.trim();
        }).filter(function (line) {
            return line !== '';
        });
    }

    function applyContentPreview() {
        var doc = previewDocument();

        if (!doc) {
            return;
        }

        contentInputs.forEach(function (input) {
            var path = input.getAttribute('data-rs-content');

            if (path === 'announcement.messages') {
                var track = doc.querySelector('[data-rs-content-messages]');

                if (!track) {
                    return;
                }

                track.textContent = '';

                messagesFromInput(input).forEach(function (message) {
                    var span = doc.createElement('span');
                    span.className = 'announcement-item';
                    span.textContent = message;
                    track.appendChild(span);
                });

                return;
            }

            Array.prototype.forEach.call(
                doc.querySelectorAll('[data-rs-content="' + path + '"]'),
                function (element) {
                    if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                        element.value = input.value;
                    } else {
                        element.textContent = input.value;
                    }
                }
            );

            // A slide can switch between the light and dark layout live.
            if (/\.theme$/.test(path)) {
                var slideElement = doc.querySelector('[data-rs-content="' + path.replace(/\.theme$/, '.eyebrow') + '"]');
                var slide = slideElement ? slideElement.closest('.hero-slide') : null;

                if (slide) {
                    slide.classList.toggle('hero-slide--light', input.value !== 'dark');
                    slide.classList.toggle('hero-slide--dark', input.value === 'dark');
                }
            }
        });
    }

    function hexToRgba(hex, alpha) {
        var value = String(hex || '').replace('#', '');

        if (value.length === 3) {
            value = value[0] + value[0] + value[1] + value[1] + value[2] + value[2];
        }

        if (!/^[0-9a-f]{6}$/i.test(value)) {
            value = 'A9552F';
        }

        var r = parseInt(value.slice(0, 2), 16);
        var g = parseInt(value.slice(2, 4), 16);
        var b = parseInt(value.slice(4, 6), 16);

        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    function normalizeHex(input) {
        var raw = String(input.value || '').trim().replace(/^#/, '');

        if (/^[0-9a-f]{3}$/i.test(raw)) {
            raw = raw[0] + raw[0] + raw[1] + raw[1] + raw[2] + raw[2];
        }

        return /^[0-9a-f]{6}$/i.test(raw) ? '#' + raw.toUpperCase() : '';
    }

    function toast(message, isError) {
        var el = app.querySelector('[data-rs-toast]');
        var text = app.querySelector('[data-rs-toast-text]');

        if (!el || !text) {
            return;
        }

        text.textContent = message;
        el.classList.toggle('is-error', !!isError);
        el.hidden = false;

        if (toastTimer) {
            window.clearTimeout(toastTimer);
        }

        toastTimer = window.setTimeout(function () {
            el.hidden = true;
        }, 4200);
    }

    function setStatus(state, message) {
        if (statusEl) {
            statusEl.setAttribute('data-state', state);
        }

        if (statusText) {
            statusText.textContent = message;
        }
    }

    function markDirty() {
        dirty = true;
        setStatus('dirty', 'Non enregistré');
    }

    function markSaved() {
        dirty = false;
        setStatus('saved', 'Enregistré');
    }

    /* ---------------------------------------------------------------
       Undo / redo history (theme controls and page content together)
       --------------------------------------------------------------- */
    function snapshotState() {
        return {
            values: values(),
            content: contentValues()
        };
    }

    function sameState(first, second) {
        return JSON.stringify(first) === JSON.stringify(second);
    }

    function recordHistory() {
        if (restoringHistory) {
            return;
        }

        var state = snapshotState();
        var current = historyStates[historyIndex];

        if (current && sameState(current, state)) {
            return;
        }

        historyStates = historyStates.slice(0, historyIndex + 1);
        historyStates.push(state);

        if (historyStates.length > HISTORY_LIMIT) {
            historyStates.shift();
        }

        historyIndex = historyStates.length - 1;
        updateHistoryButtons();
    }

    function scheduleHistory() {
        if (restoringHistory) {
            return;
        }

        if (historyTimer) {
            window.clearTimeout(historyTimer);
        }

        historyTimer = window.setTimeout(function () {
            historyTimer = null;
            recordHistory();
        }, HISTORY_DELAY);
    }

    function flushHistory() {
        if (historyTimer) {
            window.clearTimeout(historyTimer);
            historyTimer = null;
        }

        recordHistory();
    }

    function resetHistory() {
        if (historyTimer) {
            window.clearTimeout(historyTimer);
            historyTimer = null;
        }

        historyStates = [snapshotState()];
        historyIndex = 0;
        updateHistoryButtons();
    }

    function updateHistoryButtons() {
        if (undoButton) {
            undoButton.disabled = busy || historyIndex <= 0;
        }

        if (redoButton) {
            redoButton.disabled = busy || historyIndex >= historyStates.length - 1;
        }
    }

    function restoreHistory(index) {
        if (index < 0 || index >= historyStates.length) {
            return;
        }

        historyIndex = index;
        restoringHistory = true;

        var state = historyStates[index];
        setValues(state.values);
        setContentValues(state.content);

        restoringHistory = false;

        markDirty();
        clearPresetSelection();
        applyPreview();
        applyContentPreview();
        updateHistoryButtons();
    }

    function undo() {
        if (busy) {
            return;
        }

        flushHistory();

        if (historyIndex > 0) {
            restoreHistory(historyIndex - 1);
        }
    }

    function redo() {
        if (busy) {
            return;
        }

        flushHistory();

        if (historyIndex < historyStates.length - 1) {
            restoreHistory(historyIndex + 1);
        }
    }

    function setBusy(state) {
        busy = state;

        Array.prototype.forEach.call(
            app.querySelectorAll('[data-rs-save], [data-rs-reset], [data-rs-apply], [data-rs-theme-save], [data-rs-snapshot], [data-rs-undo], [data-rs-redo], [data-rs-preset]'),
            function (button) {
                button.disabled = state;
            }
        );

        updateHistoryButtons();
    }

    function post(url, payload) {
        return window.fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload || {})
        }).then(function (response) {
            return response.json().catch(function () {
                return { ok: false, error: 'Réponse du serveur illisible.' };
            }).then(function (data) {
                data.status = response.status;

                return data;
            });
        });
    }

    /* ---------------------------------------------------------------
       Preview
       --------------------------------------------------------------- */
    function previewDocument() {
        if (!preview) {
            return null;
        }

        try {
            return preview.contentDocument || null;
        } catch (error) {
            return null;
        }
    }

    function applyDerived(input, root, body) {
        var raw = input.getAttribute('data-rs-derived');

        if (!raw) {
            return;
        }

        var list;

        try {
            list = JSON.parse(raw);
        } catch (error) {
            return;
        }

        if (!Array.isArray(list)) {
            return;
        }

        list.forEach(function (derived) {
            if (!derived || !derived.var) {
                return;
            }

            root.style.setProperty(
                String(derived.var),
                hexToRgba(input.value, Number(derived.alpha) || 0.1)
            );
        });
    }

    function applyPreview() {
        var doc = previewDocument();

        if (!doc || !doc.documentElement) {
            return;
        }

        var root = doc.documentElement;

        inputs.forEach(function (input) {
            var cssVar = input.getAttribute('data-rs-var');
            var attr = input.getAttribute('data-rs-attr');

            if (cssVar) {
                root.style.setProperty(cssVar, cssValue(input));
            }

            if (attr && doc.body) {
                doc.body.setAttribute('data-' + attr, inputValue(input));
            }

            if (input.getAttribute('data-rs-type') === 'color') {
                applyDerived(input, root, doc.body);
            }
        });
    }

    function schedulePreview() {
        if (frameTimer) {
            window.cancelAnimationFrame(frameTimer);
        }

        frameTimer = window.requestAnimationFrame(applyPreview);
    }

    if (preview) {
        preview.addEventListener('load', function () {
            frameReady = true;
            applyPreview();
            applyContentPreview();
        });
    }

    /* ---------------------------------------------------------------
       Control feedback
       --------------------------------------------------------------- */
    function updateOutput(input) {
        var key = input.getAttribute('data-rs-input');
        var output = app.querySelector('[data-rs-output="' + key + '"]');
        var type = input.getAttribute('data-rs-type');

        if (!output) {
            return;
        }

        if (type === 'range') {
            output.textContent = input.value + (input.getAttribute('data-rs-unit')
                ? ' ' + input.getAttribute('data-rs-unit')
                : '');
        } else if (type === 'color') {
            output.textContent = normalizeHex(input) || input.value;
        }
    }

    function updateSwitchText(input) {
        if (input.getAttribute('data-rs-type') !== 'toggle') {
            return;
        }

        var text = input.parentNode.querySelector('.rs-switch-text');

        if (text) {
            text.textContent = input.checked ? 'Activé' : 'Désactivé';
        }
    }

    function hexFieldFor(key) {
        return app.querySelector('[data-rs-hex="' + key + '"]');
    }

    function syncColorFromText(textInput) {
        var key = textInput.getAttribute('data-rs-hex');
        var colorInput = inputFor(key);
        var hex = normalizeHex(textInput);

        textInput.classList.toggle('is-invalid', hex === '');

        if (!colorInput || hex === '') {
            return;
        }

        colorInput.value = hex;

        var output = app.querySelector('[data-rs-output="' + key + '"]');

        if (output) {
            output.textContent = hex;
        }

        updateColorSwatch(textInput, hex);
        applyPreview();
    }

    function updateColorSwatch(textInput, hex) {
        var wrapper = textInput.parentNode;
        var colorInput = wrapper ? wrapper.querySelector('input[type="color"]') : null;

        if (colorInput) {
            colorInput.value = hex;
        }
    }

    inputs.forEach(function (input) {
        var type = input.getAttribute('data-rs-type');

        input.addEventListener('input', function () {
            updateOutput(input);
            updateSwitchText(input);
            markDirty();
            clearPresetSelection();
            scheduleHistory();
            schedulePreview();
        });

        input.addEventListener('change', function () {
            updateOutput(input);
            updateSwitchText(input);
            markDirty();
            clearPresetSelection();
            scheduleHistory();
            applyPreview();
        });

        if (type === 'color') {
            var key = input.getAttribute('data-rs-input');
            var hexField = hexFieldFor(key);

            input.addEventListener('input', function () {
                if (hexField) {
                    hexField.value = String(input.value).toUpperCase();
                    hexField.classList.remove('is-invalid');
                }
            });
        }
    });

    Array.prototype.forEach.call(app.querySelectorAll('[data-rs-hex]'), function (textInput) {
        textInput.addEventListener('input', function () {
            markDirty();
            clearPresetSelection();
            scheduleHistory();
            syncColorFromText(textInput);
        });

        textInput.addEventListener('blur', function () {
            var colorInput = inputFor(textInput.getAttribute('data-rs-hex'));

            if (normalizeHex(textInput) === '' && colorInput) {
                textInput.value = String(colorInput.value).toUpperCase();
                textInput.classList.remove('is-invalid');
            }
        });

        textInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                textInput.blur();
            }
        });
    });

    /* ---------------------------------------------------------------
       Sidebar categories
       --------------------------------------------------------------- */
    var tabButtons = Array.prototype.slice.call(app.querySelectorAll('[data-rs-tab]'));
    var panelTitle = app.querySelector('[data-rs-panel-title]');
    var panelDesc = app.querySelector('[data-rs-panel-desc]');
    var panelIcon = app.querySelector('[data-rs-panel-icon]');

    function activateTab(key) {
        tabButtons.forEach(function (button) {
            var active = button.getAttribute('data-rs-tab') === key;

            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        Array.prototype.forEach.call(app.querySelectorAll('[data-rs-panel]'), function (panel) {
            panel.hidden = panel.getAttribute('data-rs-panel') !== key;
        });

        var activeButton = tabButtons.filter(function (button) {
            return button.getAttribute('data-rs-tab') === key;
        })[0];

        var panel = app.querySelector('[data-rs-panel="' + key + '"]');

        if (panelTitle && panel) {
            panelTitle.textContent = panel.getAttribute('data-title') || '';
        }

        if (panelDesc && panel) {
            panelDesc.textContent = panel.getAttribute('data-desc') || '';
        }

        if (panelIcon && activeButton) {
            var iconSource = activeButton.querySelector('.icon');

            if (iconSource) {
                panelIcon.textContent = '';
                panelIcon.appendChild(iconSource.cloneNode(true));
            }
        }

        if (activeButton) {
            focusPreviewSection(activeButton.getAttribute('data-rs-target') || '');

            try {
                window.localStorage.setItem('rs_category', key);
            } catch (error) {
                // Private browsing: the choice simply is not remembered.
            }
        }
    }

    function focusPreviewSection(target) {
        var doc = previewDocument();

        if (!doc || !target) {
            return;
        }

        var section = doc.querySelector('[data-rs-section="' + target + '"]');

        if (!section) {
            return;
        }

        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        section.classList.add('rs-preview-focus');

        window.setTimeout(function () {
            section.classList.remove('rs-preview-focus');
        }, 1400);
    }

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activateTab(button.getAttribute('data-rs-tab'));
            closeSidebar();
        });
    });

    /* ---------------------------------------------------------------
       Mobile sidebar
       --------------------------------------------------------------- */
    var scrim = app.querySelector('[data-rs-scrim], .rs-scrim');

    function openSidebar() {
        app.classList.add('is-sidebar-open');

        if (scrim) {
            scrim.hidden = false;
        }
    }

    function closeSidebar() {
        app.classList.remove('is-sidebar-open');

        if (scrim) {
            scrim.hidden = true;
        }
    }

    Array.prototype.forEach.call(app.querySelectorAll('[data-rs-sidebar-open]'), function (button) {
        button.addEventListener('click', openSidebar);
    });

    Array.prototype.forEach.call(app.querySelectorAll('[data-rs-sidebar-close]'), function (button) {
        button.addEventListener('click', closeSidebar);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    /* ---------------------------------------------------------------
       Preview device
       --------------------------------------------------------------- */
    Array.prototype.forEach.call(app.querySelectorAll('[data-rs-device]'), function (button) {
        button.addEventListener('click', function () {
            var device = button.getAttribute('data-rs-device') || 'desktop';

            if (stage) {
                stage.setAttribute('data-device', device);
            }

            Array.prototype.forEach.call(app.querySelectorAll('[data-rs-device]'), function (item) {
                item.classList.toggle('is-active', item === button);
            });

            try {
                window.localStorage.setItem('rs_device', device);
            } catch (error) {
                // Ignore storage restrictions.
            }
        });
    });

    try {
        var storedDevice = window.localStorage.getItem('rs_device');

        if (storedDevice) {
            var matching = app.querySelector('[data-rs-device="' + storedDevice + '"]');

            if (matching) {
                matching.click();
            }
        }
    } catch (error) {
        // Ignore storage restrictions.
    }

    try {
        var storedTab = window.localStorage.getItem('rs_category');

        if (storedTab && app.querySelector('[data-rs-panel="' + storedTab + '"]')) {
            activateTab(storedTab);
        }
    } catch (error) {
        // Ignore storage restrictions.
    }

    /* ---------------------------------------------------------------
       Save / reset / apply
       --------------------------------------------------------------- */
    if (undoButton) {
        undoButton.addEventListener('click', undo);
    }

    if (redoButton) {
        redoButton.addEventListener('click', redo);
    }

    document.addEventListener('keydown', function (event) {
        if (!(event.ctrlKey || event.metaKey)) {
            return;
        }

        var key = String(event.key || '').toLowerCase();

        if (key !== 'z' && key !== 'y') {
            return;
        }

        var target = event.target;
        var editable = target && (
            target.isContentEditable ||
            target.tagName === 'INPUT' ||
            target.tagName === 'TEXTAREA' ||
            target.tagName === 'SELECT'
        );

        // Keep the browser's native text undo while typing in a field. The
        // toolbar buttons still undo the whole form at any time.
        if (editable) {
            return;
        }

        event.preventDefault();

        if (key === 'y' || event.shiftKey) {
            redo();
        } else {
            undo();
        }
    });

    function persist() {
        setStatus('saving', 'Enregistrement…');

        return post(endpoints.save, { values: values(), content: contentValues() }).then(function (data) {
            if (!data || data.ok !== true) {
                throw new Error((data && data.error) || 'Enregistrement impossible.');
            }

            if (data.values) {
                setValues(data.values);
                applyPreview();
            }

            if (data.content) {
                setContentValues(data.content);
                applyContentPreview();
            }

            if (typeof data.applied !== 'undefined') {
                setAppliedUi(!!data.applied);
            }

            markSaved();
            resetHistory();

            return data;
        });
    }

    var saveButton = app.querySelector('[data-rs-save]');

    if (saveButton) {
        saveButton.addEventListener('click', function () {
            if (busy) {
                return;
            }

            setBusy(true);
            persist().then(function (data) {
                toast(data.message || 'Thème enregistré.');
            }).catch(function (error) {
                setStatus('dirty', 'Non enregistré');
                toast(error.message || 'Enregistrement impossible.', true);
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    var resetButton = app.querySelector('[data-rs-reset]');

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            if (busy) {
                return;
            }

            if (!window.confirm('Restaurer le design d’origine ? Les styles actuels seront remplacés ; le contenu reste intact.')) {
                return;
            }

            setBusy(true);

            post(endpoints.reset, {}).then(function (data) {
                if (!data || data.ok !== true) {
                    throw new Error((data && data.error) || 'Réinitialisation impossible.');
                }

                setValues(data.values || {});
                setContentValues(data.content || {});

                if (typeof data.applied !== 'undefined') {
                    setAppliedUi(!!data.applied);
                }

                clearPresetSelection();
                markSaved();
                resetHistory();
                applyPreview();
                applyContentPreview();
                toast(data.message || 'Réglages d’origine restaurés.');
            }).catch(function (error) {
                toast(error.message || 'Réinitialisation impossible.', true);
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    function setAppliedUi(applied) {
        app.setAttribute('data-applied', applied ? '1' : '0');

        if (applyButton) {
            applyButton.classList.toggle('is-applied', applied);
        }

        if (applyLabel) {
            applyLabel.textContent = applied ? 'Retirer les styles' : 'Appliquer les styles';
        }

        if (applyState) {
            applyState.textContent = applied
                ? 'Styles appliqués sur la boutique'
                : 'Non appliqué — la boutique garde son design actuel';
        }
    }

    if (applyButton) {
        applyButton.addEventListener('click', function () {
            if (busy) {
                return;
            }

            var applied = app.getAttribute('data-applied') === '1';

            if (applied && !window.confirm('Retirer les styles ? La boutique retrouvera son design d’origine.')) {
                return;
            }

            setBusy(true);

            var task = dirty ? persist() : Promise.resolve();

            task.then(function () {
                return post(applied ? endpoints.disable : endpoints.apply, {});
            }).then(function (data) {
                if (!data || data.ok !== true) {
                    throw new Error((data && data.error) || 'Action impossible.');
                }

                setAppliedUi(!!data.applied);
                toast(data.message || (applied ? 'Styles retirés.' : 'Styles appliqués.'));
            }).catch(function (error) {
                toast(error.message || 'Action impossible.', true);
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    /* Permanent JSON copy: save pending edits first, then write the snapshot. */
    var snapshotButton = app.querySelector('[data-rs-snapshot]');

    if (snapshotButton) {
        snapshotButton.addEventListener('click', function () {
            if (busy) {
                return;
            }

            setBusy(true);

            var task = dirty ? persist() : Promise.resolve();

            task.then(function () {
                return post(endpoints.snapshot, {});
            }).then(function (data) {
                if (!data || data.ok !== true) {
                    throw new Error((data && data.error) || 'Sauvegarde impossible.');
                }

                toast(data.message || ('Copie enregistrée : ' + data.file));
            }).catch(function (error) {
                toast(error.message || 'Sauvegarde impossible.', true);
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    /* Export: save unsaved edits first so the downloaded file matches. */
    var exportLink = app.querySelector('[data-rs-export]');

    if (exportLink) {
        exportLink.addEventListener('click', function (event) {
            if (!dirty) {
                return;
            }

            event.preventDefault();
            var href = exportLink.getAttribute('href');

            setBusy(true);

            persist().then(function () {
                window.location.href = href;
            }).catch(function (error) {
                toast(error.message || 'Enregistrement impossible.', true);
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    /* ---------------------------------------------------------------
       Saved themes
       --------------------------------------------------------------- */
    function refreshThemeMeta() {
        if (!themeList) {
            return;
        }

        var count = themeList.querySelectorAll('.rs-theme-item').length;

        if (libraryCount) {
            libraryCount.textContent = String(count);
        }

        if (themeEmpty) {
            themeEmpty.hidden = count > 0;
        }
    }

    function themeSaveButton() {
        return app.querySelector('[data-rs-theme-save]');
    }

    if (themeSaveButton()) {
        themeSaveButton().addEventListener('click', function () {
            if (busy) {
                return;
            }

            var name = themeNameInput ? themeNameInput.value.trim() : '';

            if (name === '') {
                toast('Donnez un nom au thème.', true);

                if (themeNameInput) {
                    themeNameInput.focus();
                }

                return;
            }

            setBusy(true);

            var task = dirty ? persist() : Promise.resolve();

            task.then(function () {
                return post(endpoints.themeSave, { name: name, values: values() });
            }).then(function (data) {
                if (!data || data.ok !== true) {
                    throw new Error((data && data.error) || 'Enregistrement impossible.');
                }

                if (themeNameInput) {
                    themeNameInput.value = '';
                }

                toast(data.message || 'Thème enregistré.');
                window.setTimeout(function () {
                    window.location.reload();
                }, 500);
            }).catch(function (error) {
                toast(error.message || 'Enregistrement impossible.', true);
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    if (themeList) {
        themeList.addEventListener('click', function (event) {
            var loadButton = event.target.closest
                ? event.target.closest('[data-rs-theme-load]')
                : null;

            if (loadButton && !busy) {
                var name = loadButton.getAttribute('data-rs-theme-load') || '';

                setBusy(true);

                post(endpoints.themeLoad, { name: name }).then(function (data) {
                    if (!data || data.ok !== true) {
                        throw new Error((data && data.error) || 'Chargement impossible.');
                    }

                    setValues(data.values || {});
                    clearPresetSelection();
                    markDirty();
                    flushHistory();
                    applyPreview();
                    toast(data.message || 'Style chargé. Enregistrez pour publier le CSS.');
                }).catch(function (error) {
                    toast(error.message || 'Chargement impossible.', true);
                }).finally(function () {
                    setBusy(false);
                });

                return;
            }

            var deleteButton = event.target.closest
                ? event.target.closest('[data-rs-theme-delete]')
                : null;

            if (!deleteButton || busy) {
                return;
            }

            var themeName = deleteButton.getAttribute('data-rs-theme-delete') || '';

            if (!window.confirm('Supprimer le thème « ' + themeName + ' » ?')) {
                return;
            }

            setBusy(true);

            post(endpoints.themeDelete, { name: themeName }).then(function (data) {
                if (!data || data.ok !== true) {
                    throw new Error((data && data.error) || 'Suppression impossible.');
                }

                var item = deleteButton.closest('.rs-theme-item');

                if (item) {
                    item.remove();
                }

                refreshThemeMeta();
                toast(data.message || 'Thème supprimé.');
            }).catch(function (error) {
                toast(error.message || 'Suppression impossible.', true);
            }).finally(function () {
                setBusy(false);
            });
        });
    }

    /* ---------------------------------------------------------------
       Load server values into the controls
       --------------------------------------------------------------- */
    function setValues(next) {
        inputs.forEach(function (input) {
            var key = input.getAttribute('data-rs-input');
            var value = next[key];

            if (typeof value === 'undefined' || value === null) {
                return;
            }

            value = String(value);

            if (input.getAttribute('data-rs-type') === 'toggle') {
                input.checked = value === (input.getAttribute('data-rs-on') || 'on');
                updateSwitchText(input);

                return;
            }

            if (input.tagName === 'SELECT') {
                var exists = Array.prototype.some.call(input.options, function (option) {
                    return option.value === value;
                });

                if (exists) {
                    input.value = value;
                }

                return;
            }

            input.value = value;

            if (input.getAttribute('data-rs-type') === 'color') {
                var hexField = hexFieldFor(key);

                if (hexField) {
                    hexField.value = value.toUpperCase();
                    hexField.classList.remove('is-invalid');
                }
            }

            updateOutput(input);
        });
    }

    function setContentValues(next) {
        contentInputs.forEach(function (input) {
            var key = input.getAttribute('data-rs-content');
            var value = next[key];

            if (typeof value === 'undefined' || value === null) {
                return;
            }

            input.value = String(value);
        });
    }

    /* ---------------------------------------------------------------
       Ready-made style starters
       --------------------------------------------------------------- */
    var presetButtons = Array.prototype.slice.call(app.querySelectorAll('[data-rs-preset]'));

    function clearPresetSelection() {
        presetButtons.forEach(function (button) {
            button.classList.remove('is-active');
            button.setAttribute('aria-pressed', 'false');
        });
    }

    presetButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (busy) {
                return;
            }

            var values;

            try {
                values = JSON.parse(button.getAttribute('data-rs-preset-values') || '{}');
            } catch (error) {
                toast('Ce style n’a pas pu être chargé.', true);
                return;
            }

            if (!values || typeof values !== 'object' || Array.isArray(values)) {
                toast('Ce style n’a pas pu être chargé.', true);
                return;
            }

            setValues(values);
            markDirty();
            flushHistory();
            applyPreview();
            clearPresetSelection();
            button.classList.add('is-active');
            button.setAttribute('aria-pressed', 'true');

            var label = button.getAttribute('data-rs-preset-label') || 'Style';
            toast('Style « ' + label + ' » chargé. Enregistrez pour publier le CSS.');
        });
    });

    contentInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            markDirty();
            scheduleHistory();
            applyContentPreview();
        });

        input.addEventListener('change', function () {
            markDirty();
            scheduleHistory();
            applyContentPreview();
        });
    });

    /* ---------------------------------------------------------------
       Boot
       --------------------------------------------------------------- */
    setAppliedUi(app.getAttribute('data-applied') === '1');
    refreshThemeMeta();
    applyPreview();
    applyContentPreview();
    resetHistory();

    window.addEventListener('message', function (event) {
        if (event.source === preview) {
            frameReady = true;
            applyPreview();
            applyContentPreview();
        }
    });

    // The iframe may finish after this script; its load event covers that.
    if (frameReady) {
        applyPreview();
        applyContentPreview();
    }
})();
