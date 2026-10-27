<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tenant_resolver
 *
 * Resolves which Union Parishad (tenant) the current request belongs to.
 *
 * RESOLUTION ORDER (first match wins) — strategy B':
 *   1. dev override   ?tenant=N / X-Tenant: N   (localhost / IP hosts only)
 *   2. platform host  platform_domains.hostname  -> id 0, NO tenant
 *   3. exact hostname tenant_domains.hostname    (custom domain)
 *   4. subdomain      <slug>.<root domain>
 *   5. session        authenticated user's tenant — ONLY reached when the host
 *                     matched nothing, so a real hostname always wins and one
 *                     tenant's admin can never read another tenant's data
 *   6. primary row    tenant_domains WHERE is_primary  (DB-driven, no literal)
 *   7. unresolved     id() returns 0 -> queries match nothing -> safe empty
 *
 * There is deliberately NO hardcoded tenant id anywhere in this file. The
 * fallback comes from the database.
 *
 * WHY PLATFORM HOSTS ARE SEPARATE AND WHY THEY SIT AT STEP 2
 * The main domain serves the ViretaDev product site, which belongs to no
 * parishad. It is registered in platform_domains rather than being left to
 * fall through to the is_primary tenant, because that fall-through is what
 * made https://the-main-domain/ quietly render a live tenant's home page —
 * its logo, chairman's speech, council members and staff — with the marketing
 * page as a thin wrapper on top. A platform host short-circuits to id 0, so
 * every tenant-scoped query in a controller constructor matches nothing.
 *
 * Step 2 sits BELOW the dev override and ABOVE everything else on purpose:
 * below, so `?tenant=1` on localhost still previews a tenant; above, so no
 * later fallback can hand the main domain a tenant it does not own.
 *
 * Two tenant identifiers coexist in this schema and must not be confused:
 *   id()    -> ins_titute.ins_titute_p_idds (1)  what the legacy `union_code`
 *              constant meant; what 14 columns across ~20 tables store
 *   un_id() -> ins_titute.un_iddd_iiid      (645) internal PK, referenced by
 *              users.union_unq_idd_iii
 *
 * Lazy: no query runs until a tenant property is actually read. Cached per request.
 */
class Tenant_resolver
{
	/** @var CI_Controller */
	protected $CI;

	/** @var object|null */
	protected $row = NULL;

	/** @var int|null */
	protected $id = NULL;

	/** @var string */
	protected $source = 'unresolved';

	/** @var string|null */
	protected $hostname = NULL;

	/** @var array */
	protected $config = array();

	/** @var array */
	protected $trace = array();

	public function __construct()
	{
		$this->CI =& get_instance();

		$cfg = $this->CI->config->item('tenant');
		$cfg = is_array($cfg) ? $cfg : array();

		$this->config = array_merge(
			array(
				// no default_tenant_id — the fallback is the is_primary DB row
				'root_domains'       => array(),
				'allow_dev_override' => TRUE,
				'dev_hosts'          => array('localhost', '127.0.0.1', '::1'),
				'session_key'        => 'union_code',
			),
			$cfg
		);
	}

	// ---------------------------------------------------------------- public

	/**
	 * Current tenant's ins_titute.ins_titute_p_idds — the value the legacy
	 * `union_code` constant used to hardcode. Returns 0 when unresolved.
	 *
	 * @return int
	 */
	public function id()
	{
		$this->resolve();
		return (int) $this->id;
	}

	/**
	 * ins_titute.un_iddd_iiid
	 *
	 * @return int|null
	 */
	public function un_id()
	{
		$this->resolve();
		return $this->row ? (int) $this->row->un_iddd_iiid : NULL;
	}

	/**
	 * Full ins_titute row (branding, contact, geo codes, logos).
	 *
	 * @return object|null
	 */
	public function row()
	{
		$this->resolve();
		return $this->row;
	}

	/**
	 * Request hostname, port stripped, lowercased.
	 *
	 * @return string
	 */
	public function hostname()
	{
		if ($this->hostname === NULL)
		{
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
			$host = strtolower(trim($host));
			if (strpos($host, ']') !== FALSE)
			{
				$host = substr($host, 0, strpos($host, ']') + 1);   // IPv6 literal
			}
			elseif (($c = strrpos($host, ':')) !== FALSE)
			{
				$host = substr($host, 0, $c);                        // strip port
			}
			$this->hostname = $host;
		}
		return $this->hostname;
	}

	/**
	 * dev-query | dev-header | platform | domain | subdomain | session | primary | unresolved
	 *
	 * @return string
	 */
	public function source()
	{
		$this->resolve();
		return $this->source;
	}

	/**
	 * True when this request is for the ViretaDev product site rather than a
	 * union parishad portal.
	 *
	 * A platform host resolves to no tenant, so every tenant-scoped query in a
	 * controller matches nothing — which is the property that keeps one
	 * parishad's member list off the public marketing page.
	 *
	 * Narrower than "not a tenant" on purpose: an unresolved host is also not
	 * a tenant, but that means the hostname is simply unrecognised, which is a
	 * different thing to report and is deliberately left distinguishable.
	 *
	 * @return bool
	 */
	public function is_platform()
	{
		$this->resolve();
		return $this->source === 'platform';
	}

	/**
	 * True when a registered host decided the tenant (not a fallback).
	 *
	 * @return bool
	 */
	public function is_explicit()
	{
		$this->resolve();
		return in_array($this->source, array('domain', 'subdomain'), TRUE);
	}

	/**
	 * False when no tenant could be resolved. Callers can 404 on this instead of
	 * silently serving another tenant's data.
	 *
	 * @return bool
	 */
	public function is_resolved()
	{
		$this->resolve();
		return $this->id > 0;
	}

	/**
	 * @return array
	 */
	public function trace()
	{
		$this->resolve();
		return $this->trace;
	}

	/**
	 * Force a tenant. Dev hosts only — the resolver refuses otherwise, so this
	 * cannot be reached by a public visitor.
	 *
	 * @param  int $value
	 * @return $this
	 */
	public function force($value)
	{
		if ( ! $this->config['allow_dev_override'])
		{
			$this->trace[] = 'force() refused: dev override is disabled (APP_ENV is not development)';
			return $this;
		}
		if ( ! $this->is_dev_host($this->hostname()))
		{
			$this->trace[] = 'force() refused: not a development host';
			return $this;
		}
		$row = $this->load_titute((int) $value);
		if ($row !== NULL)
		{
			$this->id = (int) $row->ins_titute_p_idds;
			$this->row = $row;
			$this->source = 'forced';
			$this->trace[] = 'forced -> tenant ' . $this->id;
		}
		return $this;
	}

	// --------------------------------------------------------------- private

	/**
	 * Idempotent. Runs at most once per request.
	 *
	 * @return void
	 */
	protected function resolve()
	{
		if ($this->row !== NULL OR $this->id !== NULL)
		{
			return;
		}

		$host = $this->hostname();

		// 1 — development override
		if ($this->config['allow_dev_override'] && $this->is_dev_host($host))
		{
			$this->try_dev_override();
			if ($this->id !== NULL)
			{
				return;
			}
		}

		// 2 — platform host (the ViretaDev product site). Resolves to NO tenant.
		if ($this->find_platform_by_host($host))
		{
			$this->id = 0;
			$this->row = NULL;
			$this->source = 'platform';
			$this->trace[] = "host '{$host}' is a registered platform host — no tenant";
			return;
		}

		// 3 — exact hostname (custom domain)
		if (($row = $this->find_by_host($host)) !== NULL)
		{
			$this->adopt($row, 'domain', "host '{$host}' matched exactly");
			return;
		}

		// 4 — subdomain
		if (($row = $this->match_subdomain($host)) !== NULL)
		{
			$this->adopt($row, 'subdomain', "host '{$host}' matched as subdomain");
			return;
		}

		// 5 — session tenant (only reached when the host matched nothing)
		if (($row = $this->from_session()) !== NULL)
		{
			$this->adopt($row, 'session', "host '{$host}' unknown; used signed-in user's tenant");
			return;
		}

		// 6 — DB-driven primary
		if (($row = $this->primary_tenant()) !== NULL)
		{
			$this->adopt($row, 'primary', "host '{$host}' unknown and no session; used primary tenant");
			return;
		}

		// 7 — unresolved
		$this->id = 0;
		$this->row = NULL;
		$this->source = 'unresolved';
		$this->trace[] = "host '{$host}' resolved to nothing — no primary tenant configured";
	}

	/**
	 * @return void
	 */
	protected function try_dev_override()
	{
		$forced = NULL;

		if (isset($_GET['tenant']) && $this->looks_like_id($_GET['tenant']))
		{
			$forced = (int) $_GET['tenant'];
			$this->source = 'dev-query';
		}
		elseif (isset($_SERVER['HTTP_X_TENANT']) && $this->looks_like_id($_SERVER['HTTP_X_TENANT']))
		{
			$forced = (int) $_SERVER['HTTP_X_TENANT'];
			$this->source = 'dev-header';
		}

		if ($forced === NULL)
		{
			return;
		}

		$row = $this->load_titute($forced);
		if ($row === NULL)
		{
			$this->trace[] = "dev override {$forced}: no such tenant, ignored";
			$this->source = 'unresolved';
			return;
		}
		$this->id = (int) $row->ins_titute_p_idds;
		$this->row = $row;
		$this->trace[] = "dev override -> tenant {$this->id} (" . $this->source . ')';
	}

	/**
	 * @param  object $row    ins_titute row
	 * @param  string $source
	 * @param  string $note
	 * @return void
	 */
	protected function adopt($row, $source, $note)
	{
		$this->id = (int) $row->ins_titute_p_idds;
		$this->row = $row;
		$this->source = $source;
		$this->trace[] = $note . ' -> tenant ' . $this->id;
	}

	/**
	 * Exact hostname lookup, joined to ins_titute. If tenant_domains is missing
	 * (fresh deploy before migration) fall back to the legacy domain_names column.
	 *
	 * Rows flagged is_dev_only are honoured ONLY on a development host. Without
	 * that filter the flag is decorative: a published site would still match
	 * 'dhum.local', and the point of marking a row dev-only in the first place
	 * is that it dies the moment the app leaves the developer's machine.
	 *
	 * @param  string $host
	 * @return object|null
	 */
	protected function find_by_host($host)
	{
		if ($host === '')
		{
			return NULL;
		}

		$row = $this->run_sql(
			'SELECT t.* FROM tenant_domains d
			 JOIN ins_titute t ON t.ins_titute_p_idds = d.ins_titute_p_id
			 WHERE d.hostname = ?
			   AND (d.is_dev_only = FALSE OR ?) LIMIT 1',
			array($host, $this->allows_dev_domains() ? 'y' : 'n')
		);

		// The legacy ins_titute.domain_names fallback that used to sit here is
		// gone, and it is worth being explicit about why.
		//
		// It was a second hostname lookup against a plain text column with no
		// notion of development versus production. So any hostname a tenant
		// happened to have typed into that column was live in production, with
		// no way to mark it dev-only — including a hostname that the
		// tenant_domains row for the same tenant had deliberately gated off.
		// That is how `Host: localhost` rendered a live parishad's home page
		// with APP_ENV=production: the is_dev_only filter correctly rejected
		// the tenant_domains row, and the fallback then found the very same
		// hostname a second later with nothing left to reject it.
		//
		// Both tenants' domain_names values are already present in
		// tenant_domains, so the fallback added no reachability at all. It was
		// only a way around the gate. /t_diag now reports any hostname set in
		// domain_names that has no tenant_domains row, so a tenant added
		// without one is a visible gap rather than a silent 404.
		//
		// If the legacy column is ever needed for a real lookup again, it has to
		// be gated the same way, and the gate has to live on the data — not on
		// one query that happens to remember it.
		return $row;
	}

	/**
	 * Is this request coming from a development host?
	 *
	 * Deliberately stricter than is_dev_host(), which also accepts any bare
	 * IP. A dev-only tenant row should not become reachable just because
	 * somebody typed an IP address into the address bar, so only the
	 * configured dev_hosts names count here.
	 *
	 * @return bool
	 */
	protected function allows_dev_domains()
	{
		$host = $this->hostname();
		if (in_array($host, (array) $this->config['dev_hosts'], TRUE))
		{
			return TRUE;
		}
		if (ENVIRONMENT === 'development' && (str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.test')))
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Is this hostname registered to the ViretaDev product site?
	 *
	 * Separate from find_by_host() on purpose. A platform host has no tenant,
	 * so it cannot be a row in tenant_domains — that table's whole job is to
	 * name the parishad a host belongs to. Storing a platform host there with
	 * a null or invented tenant id would put a row in the one table every
	 * tenant lookup reads, which is a bad trade for a boolean.
	 *
	 * The is_dev_only filter is the same one find_by_host() applies, for the
	 * same reason: a row marked dev-only has to stop being reachable the
	 * moment APP_ENV leaves development, and that has to be a property of the
	 * data rather than a promise made in a config comment.
	 *
	 * @param  string $host
	 * @return bool
	 */
	protected function find_platform_by_host($host)
	{
		if ($host === '')
		{
			return FALSE;
		}

		$row = $this->run_sql(
			'SELECT id FROM platform_domains
			 WHERE lower(hostname) = ?
			   AND (is_dev_only = FALSE OR ?) LIMIT 1',
			array($host, $this->allows_dev_domains() ? 'y' : 'n')
		);

		return $row !== NULL;
	}

	/**
	 * <slug>.<root domain> — resolves <slug> as a tenant hostname.
	 *
	 * @param  string $host
	 * @return object|null
	 */
	protected function match_subdomain($host)
	{
		$roots = (array) $this->config['root_domains'];
		if (empty($roots) OR $host === '')
		{
			return NULL;
		}

		foreach ($roots as $root)
		{
			$root = ltrim(strtolower(trim($root)), '.');
			if ($root === '' OR $host === $root)
			{
				continue;
			}
			$suffix = '.' . $root;
			if (substr($host, -strlen($suffix)) !== $suffix)
			{
				continue;
			}

			$slug = substr($host, 0, -strlen($suffix));
			if ($slug === '' OR strpos($slug, '.') !== FALSE)
			{
				continue; // too many labels — not a tenant subdomain
			}
			$this->trace[] = "subdomain candidate '{$slug}' under root '{$root}'";
			return $this->find_by_host($slug);
		}

		return NULL;
	}

	/**
	 * Authenticated user's tenant, read through the session library.
	 * Value is validated against ins_titute, so a stale or tampered session
	 * cannot inject a tenant id.
	 *
	 * @return object|null
	 */
	protected function from_session()
	{
		$key = $this->config['session_key'];
		if (empty($key) OR ! isset($this->CI->session))
		{
			return NULL;
		}

		$value = $this->CI->session->userdata($key);
		if ( ! $this->looks_like_id($value))
		{
			return NULL;
		}

		$row = $this->load_titute((int) $value);
		if ($row === NULL)
		{
			$this->trace[] = "session '{$key}' pointed at tenant {$value} which no longer exists";
			return NULL;
		}
		return $row;
	}

	/**
	 * The row flagged is_primary — the DB-driven fallback. No hardcoded id.
	 *
	 * Carries the same is_dev_only filter as find_by_host(). It used not to,
	 * which was the bug this method's filter fixes: the only is_primary row
	 * in the database was `localhost`, which is flagged is_dev_only, so every
	 * unrecognised hostname in production resolved to a live tenant through
	 * the one row explicitly marked as not-for-production. Fixing
	 * find_by_host() alone left this path open, because a filter added to one
	 * query is not a property of the data — the same filter has to be stated
	 * on every query that reads the column.
	 *
	 * @return object|null
	 */
	protected function primary_tenant()
	{
		return $this->run_sql(
			'SELECT t.* FROM tenant_domains d
			 JOIN ins_titute t ON t.ins_titute_p_idds = d.ins_titute_p_id
			 WHERE d.is_primary = TRUE
			   AND (d.is_dev_only = FALSE OR ?)
			 ORDER BY d.id LIMIT 1',
			array($this->allows_dev_domains() ? 'y' : 'n')
		);
	}

	/**
	 * @param  int $id
	 * @return object|null
	 */
	protected function load_titute($id)
	{
		return $this->run_sql(
			'SELECT * FROM ins_titute WHERE ins_titute_p_idds = ? LIMIT 1',
			array($id)
		);
	}

	/**
	 * First row, or NULL on failure. Errors are swallowed and db_debug silenced
	 * so a missing table degrades to fallback rather than a white screen.
	 *
	 * Returns NULL rather than FALSE on purpose: callers test `!== NULL`, and
	 * `FALSE !== NULL` evaluates TRUE in PHP, which would make a failed lookup
	 * look like a successful match and resolve the tenant to 0.
	 *
	 * @param  string $sql
	 * @param  array  $bind
	 * @return object|null
	 */
	protected function run_sql($sql, array $bind = array())
	{
		if ( ! isset($this->CI->db) OR ! is_object($this->CI->db))
		{
			return NULL;
		}
		$this->CI->db->db_debug = FALSE;
		$query = $this->CI->db->query($sql, $bind);
		if ( ! $query OR ! $query->num_rows())
		{
			return NULL;
		}
		return $query->row();
	}

	/**
	 * @param  mixed $value
	 * @return bool
	 */
	protected function looks_like_id($value)
	{
		return (is_string($value) OR is_int($value))
			&& ctype_digit((string) $value)
			&& (int) $value > 0;
	}

	/**
	 * @param  string $host
	 * @return bool
	 */
	protected function is_dev_host($host)
	{
		$hosts = (array) $this->config['dev_hosts'];
		if (empty($hosts))
		{
			// APP_ENV is not 'development'. dev_hosts is empty by design at
			// that point, and that is what closes the bare-IP shortcut below.
			return FALSE;
		}
		if (in_array($host, $hosts, TRUE))
		{
			return TRUE;
		}
		if (str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.test'))
		{
			return TRUE;
		}
		return (bool) filter_var(trim($host, '[]'), FILTER_VALIDATE_IP);
	}
}
