        <?php /* Script tags only. The floating support button and the support
                 modal are separate views loaded by Template::saas(), not nested
                 in here — CI3's view() appends to final_output rather than
                 returning markup, so <?= $this->load->view(...) ?> is a bug: it
                 asks the loader to become a string. */ ?>

    </div>

    <?php /* Two hand-written scripts, both dependency-free.
             ht-modal.js is shared with the parishad portal; saas.js carries the
             behaviour that used to live in React state. */ ?>
    <script src="inc/assets/js/ht-modal.js"></script>
    <script src="inc/assets/js/saas.js"></script>
</body>
</html>
