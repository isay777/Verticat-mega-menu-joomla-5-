/**
 * mod_vertical_megamenu — Vanilla JS
 * Joomla 5 | No jQuery
 *
 * Panel positioning logic:
 *  - Panel top aligns with the hovered menu item
 *  - If panel would overflow below viewport/nav, it's shifted up
 *  - Panel never goes above the nav top
 *  - Mouse can travel from sidebar item into panel without gap
 */
(function () {
    'use strict';

    var OPEN_CLASS = 'mega-open';
    var L3_CLASS   = 'l3-open';
    var DELAY      = 80;

    function isMobile() {
        return window.innerWidth <= 768;
    }

    /* ----------------------------------------------------------------
       Position the mega panel so it aligns with the hovered item
       but never overflows the viewport bottom or nav bottom.
    ---------------------------------------------------------------- */
    function positionPanel(nav, item, panel) {
        if (isMobile()) return;

        // Reset any previous inline top
        panel.style.top = '';

        var navRect   = nav.getBoundingClientRect();
        var itemRect  = item.getBoundingClientRect();
        var panelH    = panel.offsetHeight;
        var vpBottom  = window.innerHeight;

        // Ideal top: align panel top with item top (relative to nav)
        var idealTop  = itemRect.top - navRect.top;

        // Bottom edge of panel if we use idealTop
        var panelBottom = navRect.top + idealTop + panelH;

        // How much it overflows below viewport
        var overflow = panelBottom - vpBottom + 12; // 12px breathing room

        var finalTop = idealTop;

        if (overflow > 0) {
            // Shift panel up by the overflow amount
            finalTop = idealTop - overflow;
        }

        // Never go above the nav container
        if (finalTop < 0) finalTop = 0;

        panel.style.top = finalTop + 'px';
    }

    /* ----------------------------------------------------------------
       Level-3 flyout: CSS :hover handles desktop.
       Mobile needs click-toggle.
    ---------------------------------------------------------------- */
    function initL3Mobile(nav) {
        nav.querySelectorAll('.vmm-sub-item.has-l3').forEach(function (item) {
            var link = item.querySelector(':scope > .vmm-sub-link');
            if (!link) return;
            link.addEventListener('click', function (e) {
                if (!isMobile()) return;
                e.preventDefault();
                var isOpen = item.classList.contains(L3_CLASS);
                var list   = item.closest('.vmm-sub-list');
                if (list) {
                    list.querySelectorAll('.vmm-sub-item.has-l3').forEach(function (s) {
                        s.classList.remove(L3_CLASS);
                    });
                }
                if (!isOpen) item.classList.add(L3_CLASS);
            });
        });
    }

    /* ----------------------------------------------------------------
       Main menu
    ---------------------------------------------------------------- */
    function initMenu(nav) {
        var trigger  = nav.getAttribute('data-trigger') || 'hover';
        var items    = Array.from(
            nav.querySelectorAll(':scope > .vmm-list > .vmm-item.has-mega')
        );
        var timer    = null;
        var openItem = null;

        function openOne(item) {
            if (openItem && openItem !== item) closeOne(openItem);

            var panel = item.querySelector(':scope > .vmm-mega-panel');
            if (panel) {
                // Show first so we can measure height, then position
                item.classList.add(OPEN_CLASS);
                item.setAttribute('aria-expanded', 'true');
                positionPanel(nav, item, panel);
            } else {
                item.classList.add(OPEN_CLASS);
                item.setAttribute('aria-expanded', 'true');
            }
            openItem = item;
        }

        function closeOne(item) {
            item.classList.remove(OPEN_CLASS);
            item.setAttribute('aria-expanded', 'false');
            var panel = item.querySelector(':scope > .vmm-mega-panel');
            if (panel) panel.style.top = '';
            if (openItem === item) openItem = null;
        }

        function toggle(item) {
            item.classList.contains(OPEN_CLASS) ? closeOne(item) : openOne(item);
        }

        /* --- Hover --- */
        function attachHover(item) {
            item.addEventListener('mouseenter', function () {
                clearTimeout(timer);
                timer = setTimeout(function () { openOne(item); }, DELAY);
            });
            item.addEventListener('mouseleave', function () {
                clearTimeout(timer);
                timer = setTimeout(function () { closeOne(item); }, DELAY);
            });
            var panel = item.querySelector(':scope > .vmm-mega-panel');
            if (panel) {
                panel.addEventListener('mouseenter', function () {
                    clearTimeout(timer);
                });
                panel.addEventListener('mouseleave', function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () { closeOne(item); }, DELAY);
                });
            }
        }

        /* --- Click --- */
        function attachClick(item) {
            var link = item.querySelector(':scope > .vmm-link');
            if (!link) return;
            link.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggle(item);
            });
        }

        /* --- Keyboard --- */
        function attachKeyboard(item) {
            var link = item.querySelector(':scope > .vmm-link');
            if (!link) return;
            link.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle(item);
                } else if (e.key === 'Escape') {
                    closeOne(item);
                    link.focus();
                } else if (e.key === 'ArrowRight' && !isMobile()) {
                    e.preventDefault();
                    openOne(item);
                    var first = item.querySelector('.vmm-sub-link');
                    if (first) first.focus();
                }
            });
            var panel = item.querySelector(':scope > .vmm-mega-panel');
            if (panel) {
                panel.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') { closeOne(item); link.focus(); }
                });
            }
        }

        function attach() {
            items.forEach(function (item) {
                item.setAttribute('aria-expanded', 'false');
                attachKeyboard(item);
                if (isMobile() || trigger === 'click') {
                    attachClick(item);
                } else {
                    attachHover(item);
                }
            });
        }

        /* Click outside */
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target)) items.forEach(closeOne);
        });

        /* Resize: close all + reposition on next open */
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                items.forEach(closeOne);
            }, 150);
        });

        attach();
        initL3Mobile(nav);
    }

    /* ----------------------------------------------------------------
       Bootstrap
    ---------------------------------------------------------------- */
    function bootstrap() {
        document.querySelectorAll('.vertical-megamenu').forEach(initMenu);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }

    if (typeof Joomla !== 'undefined' && Joomla.Event) {
        document.addEventListener('joomla:updated', bootstrap);
    }

}());
