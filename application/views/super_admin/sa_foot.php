    </main>
</div><!-- /.max-w-6xl -->

<!-- jQuery + jQuery UI. super_admin was the only shell without them, which is
     why its <input type="date"> fields showed the browser's default picker
     while the admin shell (p/h.php) already used the branded one. Loaded here,
     before the inline script below, so the datepicker is defined by the time
     initialisation runs. -->
<script src="inc/assets/vendor/jquery.min.js"></script>
<script src="inc/assets/vendor/jquery-ui.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof gsap !== 'undefined') {
        // Subtle entrance stagger for dashboard cards & metrics
        gsap.from('.grid > div, .grid > a', {
            opacity: 0,
            y: 10,
            duration: 0.4,
            stagger: 0.05,
            ease: 'power2.out',
            clearProps: 'all'
        });
    }

    /* Branded date picker for super admin. Same options as the admin shell
       (p/f.php) so the two pickers behave identically.

       It has to be a FUNCTION, not a one-shot on DOMContentLoaded. Most of
       super admin's date fields are not in the served HTML at all: tenants.php
       injects them from openOnboardModal()/openEditModal() and
       subscriptions.php from saOpen()/saOpenDates(), both template literals
       evaluated on click. A DOMContentLoaded pass therefore finds nothing and
       those modals kept the browser's own picker. The observer below picks up
       whatever enters the DOM later, which covers every page without having to
       patch each modal function. */

    function saInitDatepickers(root) {
        if (typeof jQuery === 'undefined' || !jQuery.fn.datepicker) return;

        // root may be a DOM node, or a jQuery/element set to sweep.
        var $scope = (root && root.querySelectorAll)
            ? jQuery(root)
            : jQuery(root || document);

        // hasDatepicker is added by jQuery UI itself on init, so it is the
        // reliable "already done" marker — the observer sees its own popup div
        // and must not re-initialise on it.
        $scope.find('input[type="date"], input.sa-date').addBack('input[type="date"], input.sa-date')
            .not('.hasDatepicker')
            .each(function() {
                var el = this;
                var $el = jQuery(el);

                // Convert HTML5 type="date" to type="text" so modern browsers (Chromium/Firefox)
                // do NOT open their native datepicker popup on top of the jQuery UI datepicker.
                try {
                    el.type = 'text';
                } catch(e) {
                    $el.attr('type', 'text');
                }

                $el.attr('autocomplete', 'off')
                   .addClass('sa-date')   // marker for custom-datepicker.css
                   .datepicker({
                       dateFormat: 'yy-mm-dd',
                       changeMonth: true,
                       changeYear: true,
                       yearRange: "1920:+10",
                       showAnim: "fadeIn"
                   });
            });
    }

    // 1. server-rendered fields (audit.php's from/to filters)
    saInitDatepickers(document);

    // 2. fields injected later by any modal
    if (typeof MutationObserver !== 'undefined') {
        new MutationObserver(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var added = mutations[i].addedNodes;
                for (var j = 0; j < added.length; j++) {
                    var n = added[j];
                    if (n.nodeType !== 1) continue;          // skip text nodes
                    // Ignore jQuery UI's own appended calendar container
                    if (n.id === 'ui-datepicker-div' || (n.classList && n.classList.contains('ui-datepicker'))) continue;

                    if (n.nodeName === 'INPUT' && (n.type === 'date' || (n.classList && n.classList.contains('sa-date')))) {
                        saInitDatepickers(n);
                    } else if (n.querySelector) {
                        saInitDatepickers(n);
                    }
                }
            }
        }).observe(document.body, { childList: true, subtree: true });
    }
});
</script>

</body>
</html>
