<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Template
{
    public static function front($view, $data = [])
    {
		$CI =& get_instance();
		$CI->load->model('SettingModel');
		$data['gl_info'] = $CI->SettingModel->getThisSetting();
		$data['ins_info'] = $CI->SettingModel->getThisInsName();

        $CI->load->view('p/h', $data);
        $CI->load->view($view, $data);
        $CI->load->view('p/f', $data);
    }

    public static function back($view, $data = [])
    {
		$CI =& get_instance();
		$CI->load->model('SettingModel');
		$CI->load->model('FamilyModel');
		$data['gl_info'] = $CI->SettingModel->getThisSetting();
		$data['ins_info'] = $CI->SettingModel->getThisInsName();
		$data['i_cut'] = $CI->FamilyModel->getAll_InactiveCutting_amounts();
		// p/h.php loads app.css + the Material Icons webfont only when this flag
		// is set, so no public page ever pays for the 3.1 MB.
		$data['ht_shell'] = 'back';


        $CI->load->view('p/h', $data);
        $CI->load->view('p/n', $data);
        $CI->load->view('p/mdk_open', $data);
        $CI->load->view($view, $data);
        $CI->load->view('p/mdk_close', $data);
        $CI->load->view('p/f', $data);
    }

    public static function sonod($view, $data = [])
    {
		$CI =& get_instance();
		$CI->load->model('SettingModel');
		$CI->load->model('FamilyModel');
		$data['gl_info'] = $CI->SettingModel->getThisSetting();
		$data['ins_info'] = $CI->SettingModel->getThisInsName();
		$data['i_cut'] = $CI->FamilyModel->getAll_InactiveCutting_amounts();
		// p/h.php loads app.css + the Material Icons webfont only when this flag
		// is set, so no public page ever pays for the 3.1 MB.
		$data['ht_shell'] = 'sonod';


        $CI->load->view('p/h', $data);
        $CI->load->view('p/n', $data);
        $CI->load->view('p/mdk_open', $data);
        $CI->load->view($view, $data);
        $CI->load->view('p/mdk_close', $data);
        $CI->load->view('p/f', $data);
    }


    /**
     * TEMPORARY — why back() and sonod() bracket the content view with
     * p/mdk_open and p/mdk_close.
     *
     * WHY THE WRAPPER EXISTS
     * Every rule in inc/assets/css/app.css is gated behind an attribute
     * selector — `[dir=ltr] .card`, `[dir=ltr] .form-control`,
     * `[dir=ltr] .text-muted` — 4,685 of them. The same is true of
     * inc/assets/css/vendor-material-icons.css, which is why back/dashboard.php's
     * 44 `<i class="material-icons">` drew nothing when the stylesheet was
     * loaded onto a page whose <html> carries no dir attribute. Loading app.css
     * was therefore never sufficient on its own; the wrapper is the half of the
     * fix that actually switches those rules on.
     *
     * WHY dir="ltr" AND NOT dir="rtl"
     * The content is Bangla, a left-to-right script, and both the Tailwind shell
     * and the MDK views are built LTR with no rtl variant anywhere. The attribute
     * is here to select MDK's rules, not to describe the writing direction.
     *
     * WHY A WRAPPER INSTEAD OF dir ON <html>
     * The shell (p/n, p/f) is already Tailwind and has to stay that way. MDK's
     * utilities all carry !important, and twenty of them collide by name with
     * classes the shell uses: bg-white, text-white, flex, flex-row, border,
     * border-2, rounded-lg, rounded-xl, mt-1, mt-2, mt-auto, mx-auto, my-1, p-2,
     * px-3, px-4, py-2, py-5, text-center, text-left. Several disagree on the
     * value — MDK's .rounded-xl is 0.25rem against Tailwind's --brand-radius-xl,
     * and its .flex is `flex: 1 1 0%` rather than `display: flex` — so
     * dir="ltr" on <html> would hand all twenty to MDK and visibly break the
     * header and sidebar.
     *
     * A wrapper avoids that without needing an override block, because app.css
     * contains ZERO unscoped rules for any of the 150 class names the shell
     * uses: outside a [dir=ltr] ancestor none of them can match. Inside the
     * wrapper MDK behaves exactly as it always did. The extra level breaks no
     * selector — app.css has no `main >`, `body >` or `.page >` child rule that
     * it could invalidate, and a wrapper cannot change the parent/child
     * relationship between any two existing elements.
     *
     * WHY TWO VIEW FILES AND NOT echo
     * CI_Loader::view() appends to CI_Output::$final_output and returns nothing
     * usable, and final_output is only flushed at the end of the request — so an
     * `echo '<div dir="ltr">'` between two load->view() calls does not land
     * between them. It reaches the browser first, ahead of <html>. Both halves
     * have to be views to keep the sequence intact.
     *
     * REMOVE these two views, their load->view() calls here, and the $ht_shell
     * block in p/h.php once the last MDK view in back/ and sonod/ is converted.
     */


    /**
     * Product-site (ViretaDev marketing site) shell.
     *
     * Concatenates seven views in sequence, the same way back()/sonod() do —
     * there is no layout inheritance anywhere in this codebase:
     *
     *   saas/head -> saas/partials/navbar -> <page> -> saas/partials/footer
     *              -> saas/partials/support_button
     *              -> saas/partials/support_modal -> saas/foot
     *
     * This replaced front/saas_landing.php, which was a React mount point
     * loading a ~700 kB Vite bundle and routing client-side. Every product page
     * is now a real URL with its own Front controller method, so this shell is
     * called from one of those methods rather than the other way round.
     *
     * The support button and modal are loaded here rather than nested inside
     * foot.php. CI3's $this->load->view() appends to CI_Output::$final_output
     * and does not return markup, so `<?= $this->load->view('...') ?>` fails
     * with "Object of class CI_Loader could not be converted to string" — and
     * because final_output is only echoed by CI_Output::_display() at the end of
     * the request, that fatal also swallows every view before it. A view that
     * loads another view works, but it has to be a statement, not an
     * expression; keeping the shell a flat sequence avoids the trap entirely.
     *
     * @param string $page  View name, e.g. 'saas/pages/about'.
     * @param array  $data  View data. Two keys are meaningful to the chrome:
     *                       $saas_path  active route for nav highlighting,
     *                                   without a leading slash for '/'
     *                       $saas_title / $saas_desc  <title> and meta.
     */
    public static function saas($page, $data = [])
    {
        $CI =& get_instance();

        // The navbar and footer both highlight the current page. Front's
        // controller methods already know which route they are serving, so each
        // passes $data['saas_path']; default to the landing page.
        $data['saas_path'] = $data['saas_path'] ?? '/';

        $CI->load->view('saas/head', $data);
        $CI->load->view('saas/partials/navbar', $data);
        $CI->load->view($page, $data);
        $CI->load->view('saas/partials/footer', $data);
        $CI->load->view('saas/partials/support_button', $data);
        $CI->load->view('saas/partials/support_modal', $data);
        $CI->load->view('saas/foot', $data);
    }

    /**
     * Super-admin panel shell.
     *
     * Concatenates three views in sequence, the same way every other shell
     * in this class does — there is no layout inheritance anywhere in this
     * codebase:
     *
     *   super_admin/sa_head -> super_admin/sa_nav -> <page> -> super_admin/sa_foot
     *
     * WHY IT IS NOT back()
     * This panel is not a parishad. It lists every tenant at once, so it
     * must not run the tenant shell's queries — back() loads SettingModel's
     * getThisInsName(), which joins un_list/up_list/dist_list/div_list and
     * scopes to tenant_code(). On a platform host tenant_code() is 0, so that
     * returns NULL and $ins_info carries nothing, and the sidebar it renders
     * (p/n.php) is the parishad's own navigation. Reusing it would mean a
     * panel that shows a tenant menu and null branding.
     *
     * It also must not run the MDK bridge. $ht_shell stays unset, so
     * p/h.php's gate never opens — and p/h.php is not loaded here at all.
     * That is deliberate on both counts: app.css is 3.1 MB of
     * [dir=ltr]-scoped Material Design Kit for admin views being converted,
     * none of which this panel uses, and loading it would cost every one of
     * the panel's pages a megabyte it has no use for.
     *
     * The panel is Tailwind + Phosphor only, on portal.css, exactly like the
     * standalone super_admin/index.php it replaces. That file carried its
     * own <head>, its own header and its own <body>; the chrome moved here so
     * six modules can share it and its content view can be a page rather than
     * a whole document.
     *
     * Note the deliberate absence of any auth check in this function. The
     * gate is require_sadmin() in the controller constructor, so a
     * controller that forgets to load the shell still cannot be reached
     * without passing the gate, and this function is a renderer, not a
     * security boundary. Putting the check here would make it depend on a
     * shell being loaded, which is exactly the kind of load-bearing
     * coincidence this codebase has been bitten by.
     *
     * @param string $view  View name, e.g. 'super_admin/subscriptions'.
     * @param array  $data  View data, plus two keys the chrome reads:
     *                      $sa_title  page heading
     *                      $sa_desc   one-line subtitle
     */
    public static function sadmin($view, $data = [])
    {
        $CI =& get_instance();

        $sa_seg = $CI->uri->segment(1) ?: '';
        $en_titles = [
            'super_admin'          => ['Overview', 'Central multi-tenant platform overview at a glance.'],
            'sadmin_tenants'       => ['Tenants & Portals', 'Manage parishad portals, custom domains, and activation.'],
            'sadmin_subscriptions' => ['Subscriptions & Billing', 'Track subscription plans, renewal cycles, and payments.'],
            'sadmin_users'         => ['User Management', 'Directory of parishad staff and platform administrators.'],
            'sadmin_support'       => ['Support Tickets', 'Resolve support queries and issues from union parishads.'],
            'sadmin_settings'      => ['Platform Settings', 'Global configuration and system preferences.'],
            'sadmin_audit'         => ['Audit Logs', 'Chronological security and action audit trail across platform.'],
        ];

        $data['sa_title']    = $data['sa_title'] ?? 'সুপার অ্যাডমিন';
        $data['sa_desc']     = $data['sa_desc'] ?? '';
        $data['sa_title_en'] = $data['sa_title_en'] ?? ($en_titles[$sa_seg][0] ?? 'Super Admin');
        $data['sa_desc_en']  = $data['sa_desc_en'] ?? ($en_titles[$sa_seg][1] ?? '');

        /*
         * The current module, resolved once here and handed to the chrome
         * as $sa_seg.
         *
         * CI_URI::segment(1), not a helper function. There is no
         * uri_segment() in CodeIgniter 3 — it is a CI2 name, and calling it
         * raises an uncaught Error, which CI3 turns into a 500 with a
         * ZERO-BYTE body (its shutdown handler cannot render an error page
         * from inside a shutdown handler). A shell that cannot tell which
         * page it is on is a shell that cannot highlight anything, so the
         * name looked plausible enough to survive review.
         *
         * Segment 1 and not the full uri_string() because
         * /sadmin_support/ticket/5 still belongs to the support module in
         * the sidebar.
         */
        $data['sa_seg']      = $CI->uri->segment(1) ?: '';
        $data['sa_nav']      = sadmin_nav_items($data['sa_seg']);
        $data['sa_badges']    = sadmin_nav_badges();
        $data['sa_user']     = $CI->ion_auth->user()->row();

        $CI->load->view('super_admin/sa_head', $data);
        $CI->load->view('super_admin/sa_nav', $data);
        $CI->load->view($view, $data);
        $CI->load->view('super_admin/sa_foot', $data);
    }

}

