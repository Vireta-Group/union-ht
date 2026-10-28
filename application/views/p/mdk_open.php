<?php
defined('BASEPATH') OR exit('No direct script access needed');

/*
 * TEMPORARY — opens the Material Design Kit scope. See the long note on
 * Template::back() for why this exists, why it is a view rather than an
 * echo(), and when to delete it.
 *
 * Every rule in app.css and in vendor-material-icons.css is written as
 * `[dir=ltr] <selector>`, so a page only gets MDK at all when something
 * carries a dir attribute. This div is that something, and it wraps the
 * content view only — the Tailwind shell in p/n.php and p/f.php stays
 * outside it, which is what keeps MDK's !important utility classes from
 * reaching classes like rounded-xl, px-4 and flex in the header and
 * sidebar.
 *
 * This is a PHP comment, not an HTML one, so none of it reaches the browser.
 */
?>
<div dir="ltr" class="space-y-6">
