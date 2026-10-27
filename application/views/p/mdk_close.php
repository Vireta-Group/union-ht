<?php
defined('BASEPATH') OR exit('No direct script access needed');

/*
 * TEMPORARY — closes the Material Design Kit scope opened by p/mdk_open.php.
 * See Template::back() for the full explanation. Delete both files together.
 *
 * Kept as its own view rather than a closing tag appended to the next view so
 * that the open/close pair stays symmetrical and greppable: a "convert back/ to
 * Tailwind" change is one grep for mdk_open, not an audit of every view.
 */
?>
</div>
