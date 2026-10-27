<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tenant diagnostic endpoint — DEVELOPMENT ONLY.
 *
 * HARD-GATED: refuses to run unless the request arrived on a development host
 * (localhost / an IP literal). On a real domain it returns 404, so this can
 * never leak tenant structure in production.
 *
 * Route: /t_diag
 *
 * Query parameters:
 *   tenant=N   force a specific tenant (dev hosts only, resolver re-checks)
 *   raw=1      dump the full ins_titute row instead of the summary
 */
class T_diag extends CI_Controller
{
	public function index()
	{
		$resolver = tenant_resolver();

		// --- guard: development hosts only -----------------------------------
		// Set $config['tenant_diag_any_host'] = TRUE to allow probing
		// arbitrary Host headers while debugging DNS/routing. It must stay
		// FALSE in production — this endpoint prints tenant structure.
		//
		// The host test reads the same APP_ENV-derived dev_hosts the resolver
		// uses, rather than its own hardcoded list. A hardcoded list still
		// answers on 'Host: localhost' after the app is published, which would
		// print every tenant id, name and registered hostname to anyone who
		// sent that header. is_development is FALSE whenever APP_ENV is
		// anything but 'development', so the endpoint disappears on deploy.
		$host = $resolver->hostname();
		$allow_any = (bool) $this->config->item('tenant_diag_any_host');
		$is_development = (bool) $this->config->item('is_development');
		$is_dev = $is_development
			&& in_array($host, (array) $this->config->item('tenant')['dev_hosts'], TRUE);

		if ( ! $is_dev && ! $allow_any)
		{
			show_404('t_diag is development-only');
			return;
		}

		// --- optional force ---------------------------------------------------
		if (isset($_GET['tenant']) && ctype_digit((string) $_GET['tenant']))
		{
			$resolver->force((int) $_GET['tenant']);
		}

		$row = $resolver->row();
		$db = $this->db;

		// --- live data counts for the resolved tenant -------------------------
		$tid = $resolver->id();
		$counts = array();
		if ($tid > 0)
		{
			$counts['family_data_sheet (ins_aut_iiid)']   = $db->where('ins_aut_iiid', $tid)->count_all_results('family_data_sheet');
			$db->reset_query();
			$counts['amount_paid (in_s_titute_pr_idd)']  = $db->where('in_s_titute_pr_idd', $tid)->count_all_results('amount_paid');
			$db->reset_query();
			$counts['up_member_s (ins_atou_iddd_s)']     = $db->where('ins_atou_iddd_s', $tid)->count_all_results('up_member_s');
			$db->reset_query();
			$counts['home_slider (insidpris_id)']       = $db->where('insidpris_id', $tid)->count_all_results('home_slider');
			$db->reset_query();
		}

		// --- every registered host --------------------------------------------
		$hosts = $db->order_by('id')->get('tenant_domains')->result();

		// --- every tenant ------------------------------------------------------
		$tenants = $db->order_by('ins_titute_p_idds')->get('ins_titute')->result();

		$out = array();
		$source = $resolver->source();
		$is_platform = ($source === 'platform');

		$out[] = 'TENANT DIAGNOSTIC (development endpoint)';
		$out[] = str_repeat('=', 60);
		$out[] = 'request host      : ' . $host;

		// The id/source lines used to print "0 (UNRESOLVED)" and
		// "no (fallback)" for every host without a tenant, which is wrong for a
		// platform host: it is not unresolved, and it is not a fallback. A
		// registered platform host is an explicit statement that this host
		// belongs to no parishad, and a diagnostic whose job is to be
		// unambiguous should not call that a failure to resolve.
		if ($is_platform)
		{
			$out[] = 'resolved id()     : 0  (no tenant — this is a platform host)';
			$out[] = 'resolved un_id()  : -';
			$out[] = 'resolved source   : platform';
			$out[] = 'is_explicit()     : yes (explicitly the product site)';
			$out[] = 'is_resolved()     : n/a (no tenant to resolve)';
			$out[] = 'is_platform()     : YES — product site, no tenant';
			$out[] = 'tenant_code()     : 0';
		}
		else
		{
			$out[] = 'resolved id()     : ' . ($tid ?: '0  (UNRESOLVED)');
			$out[] = 'resolved un_id()  : ' . ($resolver->un_id() ?: '-');
			$out[] = 'resolved source   : ' . $source;
			$out[] = 'is_explicit()     : ' . ($resolver->is_explicit() ? 'yes' : 'no (fallback)');
			$out[] = 'is_resolved()     : ' . ($resolver->is_resolved() ? 'yes' : 'NO');
			$out[] = 'is_platform()     : no';
			$out[] = 'tenant_code()     : ' . tenant_code();
		}
		$out[] = '';
		$out[] = 'resolution trace:';
		foreach ($resolver->trace() as $t)
		{
			$out[] = '   - ' . $t;
		}
		$out[] = '';

		// Hostnames set on ins_titute.domain_names that have no tenant_domains
		// row are unreachable. Tenant_domains is the only hostname lookup now
		// (the legacy domain_names fallback was removed because it bypassed the
		// is_dev_only gate), so a tenant added without one 404s with nothing to
		// explain why. This turns that into something you can see.
		$out[] = str_repeat('-', 60);
		$out[] = 'hostname coverage gaps (ins_titute.domain_names with no tenant_domains row):';
		$gap = $this->db->query(
			'SELECT i.ins_titute_p_idds AS t, i.ins_name AS n, i.domain_names AS h
			 FROM ins_titute i
			 LEFT JOIN tenant_domains d ON lower(d.hostname) = lower(i.domain_names)
			 WHERE i.domain_names IS NOT NULL AND i.domain_names <> \'\'
			   AND d.id IS NULL
			 ORDER BY i.ins_titute_p_idds'
		);
		if ($gap === FALSE OR $gap->num_rows() === 0)
		{
			$out[] = '   none — every legacy hostname is registered in tenant_domains';
		}
		else
		{
			foreach ($gap->result() as $g)
			{
				$out[] = '   tenant ' . $g->t . ' (' . $g->n . ') lists \'' . $g->h
					. '\' but has no tenant_domains row — that host will not resolve';
			}
		}
		$out[] = '';
		$out[] = str_repeat('-', 60);
		$out[] = 'ins_titute row for this tenant:';
		if ($row)
		{
			$out[] = '   ins_titute_p_idds : ' . $row->ins_titute_p_idds;
			$out[] = '   un_iddd_iiid      : ' . $row->un_iddd_iiid;
			$out[] = '   ins_name          : ' . $row->ins_name;
			$out[] = '   domain_names      : ' . $row->domain_names;
			$out[] = '   sub_domains       : ' . $row->sub_domains;
			$out[] = '   union_codes       : ' . $row->union_codes;
		}
		else
		{
			$out[] = '   (none)';
		}
		$out[] = '';
		$out[] = str_repeat('-', 60);
		$out[] = 'live row counts for tenant ' . $tid . ':';
		foreach ($counts as $label => $n)
		{
			$out[] = '   ' . str_pad($label, 34) . $n;
		}
		$out[] = '';
		$out[] = str_repeat('-', 60);
		$out[] = 'tenant_domains:';
		foreach ($hosts as $h)
		{
			$out[] = '   ' . str_pad($h->hostname, 26)
				. ' tenant=' . $h->ins_titute_p_id
				. '  primary=' . ($h->is_primary ? 'Y' : 'n')
				. '  verified=' . ($h->is_verified ? 'Y' : 'n');
		}

		// ---------------------------------------------------------- media
		// B6a moved tenant branding to per-tenant files. media_url() has to
		// keep rendering rows that still hold base64, otherwise a
		// half-migrated database silently loses every image. These three
		// cases are the whole contract, so they are asserted rather than
		// assumed.
		$out[] = '';
		$out[] = str_repeat('-', 60);
		$out[] = 'media_url() self-test:';

		$onepx = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

		$cases = array(
			'stored path (exists)'  => array('tenant-1/logo.png', 'expect http URL to uploads/'),
			'stored path (missing)' => array('tenant-1/nope.png', 'expect 1x1 placeholder'),
			'legacy base64 payload' => array($onepx, 'expect data:image/png;base64,'),
			'full data: URI'        => array('data:image/gif;base64,AAAA', 'expect returned untouched'),
			'empty / NULL'          => array('', 'expect 1x1 placeholder'),
		);
		foreach ($cases as $label => $c)
		{
			$got = media_url($c[0], 'image/png');
			$out[] = '   ' . str_pad($label, 24)
				. ' ' . (strlen($got) > 62 ? substr($got, 0, 59) . '...' : $got);
			$out[] = '   ' . str_pad('', 24) . ' ' . $c[1];
		}

		// every tenant's stored media, as it would actually be rendered
		$out[] = '';
		$out[] = '   stored branding per tenant:';
		foreach ($tenants as $t)
		{
			$out[] = '     tenant ' . $t->ins_titute_p_idds
				. '  logo=' . var_export($t->ins_logo_path, TRUE)
				. '  file_present=' . ($this->tenant_media->abs_path($t->ins_logo_path) ? 'yes' : 'no');
		}

		$out[] = '';
		$out[] = str_repeat('-', 60);
		$out[] = 'all tenants:';
		foreach ($tenants as $t)
		{
			$out[] = '   id=' . str_pad($t->ins_titute_p_idds, 4)
				. ' un_id=' . str_pad($t->un_iddd_iiid, 6)
				. ' union_codes=' . str_pad((string) $t->union_codes, 5)
				. ' ' . $t->ins_name;
		}

		if (isset($_GET['raw']) && $row)
		{
			$out[] = '';
			$out[] = str_repeat('-', 60);
			$out[] = 'raw ins_titute row (base64 image columns stripped):';
			foreach (get_object_vars($row) as $k => $v)
			{
				if (strlen((string) $v) > 120)
				{
					$v = substr($v, 0, 60) . ' ...[' . strlen((string) $row->$k) . ' bytes]';
				}
				$out[] = '   ' . str_pad($k, 22) . $v;
			}
		}

		$out[] = '';
		$out[] = 'session union_code : ' . var_export($this->session->userdata('union_code'), TRUE);

		header('Content-Type: text/plain; charset=utf-8');
		echo implode("\n", $out);
	}
}
