<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tenant_media
 *
 * Per-tenant image storage. One directory per tenant, one file per asset:
 *
 *     uploads/tenant-1/logo.png
 *     uploads/tenant-1/chairman.png
 *     uploads/tenant-1/signature.png
 *     uploads/tenant-1/slider-2.jpg
 *
 * Why files and not base64 in the database:
 *
 *   - browsers can cache a file URL, but never an inline data: URI, so a
 *     base64 logo is re-downloaded on every single page view
 *   - inline megabytes block HTML parsing before anything can paint
 *   - a 2 MB logo blob is copied into every DB backup
 *   - the database grows without bound and every query touching that row
 *     drags the blob across the wire
 *
 * The database column keeps holding the same `text` it always did. It now
 * stores a relative path instead of a base64 payload, so no schema change
 * and no column rename was needed.
 *
 * BACKWARD COMPATIBILITY — url() accepts both shapes, so any row that was
 * never migrated still renders exactly as before:
 *
 *     'tenant-1/logo.png'            -> http://host/uploads/tenant-1/logo.png
 *     'iVBORw0KGgoAAAANSUhEUgAA...'  -> data:image/png;base64,iVBORw0KGgo...
 *     'data:image/png;base64,...'    -> returned untouched
 *
 * The previous code was `base64_encode(file_get_contents($_FILES[...]))`
 * with no validation of any kind, so the database accepted any file type at
 * all. Storing to disk turns that class of mistake into remote code
 * execution, so save_upload() re-validates: the extension comes from the
 * sniffed image type, never from the client's filename, and uploads/ has a
 * deny rule so nothing in it can ever execute.
 */
class Tenant_media
{
	/** @var CI_Controller */
	protected $CI;

	/** Directory inside the webroot that holds every tenant's files. */
	const DIR_NAME = 'uploads';

	/**
	 * Sniffed image type -> stored extension. Only these may be written.
	 * SVG is deliberately absent: an SVG is a script container.
	 */
	protected $allowed = array(
		IMAGETYPE_PNG  => 'png',
		IMAGETYPE_JPEG => 'jpg',
		IMAGETYPE_GIF  => 'gif',
		IMAGETYPE_WEBP => 'webp',
		IMAGETYPE_BMP  => 'bmp',
	);

	/** Reverse map, used to guess a mime type for a legacy base64 blob. */
	protected $mime_for_ext = array(
		'png'  => 'image/png',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'gif'  => 'image/gif',
		'webp' => 'image/webp',
		'bmp'  => 'image/bmp',
	);

	/**
	 * Served when a path is recorded in the database but the file is gone.
	 * A 1x1 transparent GIF: an empty src would make the browser re-request
	 * the current page, so never return an empty string from url().
	 */
	const BLANK = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	// ---------------------------------------------------------------- paths

	/**
	 * Absolute path of the uploads root, created on first use.
	 *
	 * @return string
	 */
	public function root()
	{
		$abs = FCPATH . self::DIR_NAME;
		if ( ! is_dir($abs))
		{
			@mkdir($abs, 0755, TRUE);
		}
		return $abs;
	}

	/**
	 * A tenant's directory, created on first use.
	 *
	 * @param  int $tenant_id ins_titute.ins_titute_p_idds
	 * @return string absolute path
	 */
	public function tenant_dir($tenant_id)
	{
		$dir = $this->root() . '/tenant-' . (int) $tenant_id;
		if ( ! is_dir($dir))
		{
			@mkdir($dir, 0755, TRUE);
		}
		return $dir;
	}

	/**
	 * Relative path for a tenant asset, safe to store in the database.
	 *
	 * @param  int    $tenant_id
	 * @param  string $filename
	 * @return string  e.g. tenant-1/logo.png
	 */
	public function rel($tenant_id, $filename)
	{
		return 'tenant-' . (int) $tenant_id . '/' . $filename;
	}

	// ------------------------------------------------------ platform assets

	/**
	 * The platform's own asset directory: uploads/platform/
	 *
	 * WHY THIS EXISTS AND WHY IT IS NOT A FAKE TENANT
	 * satting_infos.vLogoPath and vFaviconPath are the ViretaDev brand —
	 * the logo on the product site, not any parishad's logo. The obvious
	 * shortcut is to store them as tenant-0/logo.png and reuse
	 * save_upload(), but tenant 0 is the resolver's "no parishad" sentinel
	 * and store_bytes() rejects an id below 1 on purpose. Forcing it past
	 * that check would put a real file in a directory whose name asserts
	 * something false, and the first person to read
	 * "uploads/tenant-0/" would reasonably conclude tenant 0 exists.
	 *
	 * So platform assets get their own directory, and the relative path is
	 * still two segments with an extension — which is all media_url() and
	 * is_path() require. Nothing downstream needs to know it is not a
	 * tenant.
	 */
	public function platform_dir()
	{
		$abs = $this->root() . '/platform';
		if ( ! is_dir($abs))
		{
			@mkdir($abs, 0755, TRUE);
		}
		return $abs;
	}

	/**
	 * @param  string $filename
	 * @return string  e.g. platform/logo.png
	 */
	public function rel_platform($filename)
	{
		return 'platform/' . $filename;
	}

	/**
	 * Store an uploaded file in the platform directory.
	 *
	 * Identical validation and naming to the tenant path — sniffed
	 * extension, never the client's filename, SVG refused, uploads/ has a
	 * deny rule — because the two assets have exactly the same risk. The
	 * shared work is in store_bytes_to(); the difference is only which
	 * directory it writes to, which is not a reason to have a second copy
	 * of the rules.
	 *
	 * @param  array  $file  one $_FILES entry
	 * @param  string $slug  e.g. 'logo', 'favicon'
	 * @return string|null  relative path, or NULL if it was not a usable image
	 */
	public function save_platform_upload($file, $slug)
	{
		if ( ! is_array($file) OR empty($file['tmp_name']))
		{
			return NULL;
		}
		if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK)
		{
			return NULL;
		}
		if ( ! is_uploaded_file($file['tmp_name']))
		{
			return NULL;
		}

		$bytes = @file_get_contents($file['tmp_name']);
		if ($bytes === FALSE OR $bytes === '')
		{
			return NULL;
		}

		return $this->store_bytes_to($bytes, $slug, $this->platform_dir(), 'platform');
	}

	// ----------------------------------------------------------------- read

	/**
	 * Turn a stored value into something usable in an <img src>.
	 *
	 * Handles all three shapes so a partially-migrated database still
	 * renders: a relative path, a bare base64 payload, or a full data URI.
	 *
	 * @param  mixed  $value         column value
	 * @param  string $default_mime  used only for a legacy base64 payload
	 * @return string
	 */
	public function url($value, $default_mime = 'image/png')
	{
		if ($value === NULL OR is_bool($value) OR is_array($value) OR is_object($value))
		{
			return self::BLANK;
		}

		$value = trim((string) $value);
		if ($value === '')
		{
			return self::BLANK;
		}

		// already a full data: URI
		if (strncmp($value, 'data:', 5) === 0)
		{
			return $value;
		}

		// already an absolute or protocol-relative URL
		if (preg_match('#^(https?:)?//#i', $value))
		{
			return $value;
		}

		if ($this->is_path($value))
		{
			$abs = $this->root() . '/' . ltrim($value, '/');
			if ( ! is_file($abs))
			{
				// recorded in the database but the file is not there — the
				// bytes are unrecoverable, so show nothing rather than a
				// broken-image icon
				return self::BLANK;
			}
			return base_url(self::DIR_NAME . '/' . ltrim($value, '/'));
		}

		// legacy: a bare base64 payload still sitting in the column
		return 'data:' . $default_mime . ';base64,' . $value;
	}

	/**
	 * Is this value a stored file path rather than base64?
	 *
	 * Deliberately narrow. base64 uses A-Za-z0-9+/= and has no dots or
	 * forward slashes, so "one segment, slash, filename with an extension"
	 * cannot collide with an encoded payload.
	 *
	 * @param  string $value
	 * @return bool
	 */
	public function is_path($value)
	{
		if ( ! is_string($value) OR $value === '')
		{
			return FALSE;
		}
		// a 2 MB logo blob is 2.8 MB of base64; paths are never that long
		if (strlen($value) > 512)
		{
			return FALSE;
		}
		return (bool) preg_match(
			'#^[A-Za-z0-9_\-]+/[A-Za-z0-9_\-]+\.[A-Za-z0-9]{2,5}$#',
			$value
		);
	}

	/**
	 * Absolute path for a stored value, or NULL when it is not on disk.
	 *
	 * @param  string $value
	 * @return string|null
	 */
	public function abs_path($value)
	{
		if ( ! $this->is_path($value))
		{
			return NULL;
		}
		$abs = $this->root() . '/' . ltrim($value, '/');
		return is_file($abs) ? $abs : NULL;
	}

	// ---------------------------------------------------------------- write

	/**
	 * Store an uploaded file under the current tenant.
	 *
	 * Replaces the old `base64_encode(file_get_contents($_FILES[...]))` calls.
	 * Nothing from the client is trusted: the stored filename is
	 * <slug>.<sniffed extension>, so a "logo.php" upload lands on disk as
	 * something the image sniffer already rejected.
	 *
	 * @param  array  $file       one $_FILES entry
	 * @param  string $slug       e.g. 'logo', 'chairman', 'slider-2'
	 * @param  int    $tenant_id  defaults to the resolved tenant
	 * @return string|null        relative path on success
	 */
	public function save_upload($file, $slug, $tenant_id = NULL)
	{
		if ( ! is_array($file) OR empty($file['tmp_name']))
		{
			return NULL;
		}
		if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK)
		{
			return NULL;
		}
		if ( ! is_uploaded_file($file['tmp_name']))
		{
			return NULL;
		}

		$bytes = @file_get_contents($file['tmp_name']);
		if ($bytes === FALSE OR $bytes === '')
		{
			return NULL;
		}

		return $this->store_bytes($bytes, $slug, $tenant_id);
	}

	/**
	 * Write raw image bytes to the tenant's directory.
	 *
	 * Shared by save_upload() and the base64 migration, so both go through
	 * exactly the same sniffing and naming rules.
	 *
	 * @param  string $bytes
	 * @param  string $slug
	 * @param  int    $tenant_id
	 * @return string|null relative path
	 */
	public function store_bytes($bytes, $slug, $tenant_id = NULL)
	{
		if ($tenant_id === NULL)
		{
			$tenant_id = tenant_code();
		}
		$tenant_id = (int) $tenant_id;
		if ($tenant_id < 1)
		{
			return NULL;
		}

		$ext = $this->sniff_ext($bytes);
		if ($ext === NULL)
		{
			return NULL;   // not a real, permitted image
		}

		$slug = $this->safe_slug($slug);
		if ($slug === '')
		{
			return NULL;
		}

		$filename = $slug . '.' . $ext;
		$abs = $this->tenant_dir($tenant_id) . '/' . $filename;

		// a failed write must not leave a row pointing at a missing file
		if (@file_put_contents($abs, $bytes) === FALSE)
		{
			return NULL;
		}
		@chmod($abs, 0644);

		return $this->rel($tenant_id, $filename);
	}

	/**
	 * The write half of store_bytes(), with the directory and the returned
	 * prefix as parameters.
	 *
	 * Split out for save_platform_upload() rather than copied, so that a
	 * change to the naming or the sniffing rules cannot reach the platform
	 * path without reaching the tenant path. Two copies of an upload
	 * validator is two answers to "can this file be written", and the
	 * second one is the one nobody reads.
	 *
	 * @param  string $bytes
	 * @param  string $slug
	 * @param  string $dir   absolute, already created
	 * @param  string $prefix  the first segment of the stored path
	 * @return string|null
	 */
	protected function store_bytes_to($bytes, $slug, $dir, $prefix)
	{
		$ext = $this->sniff_ext($bytes);
		if ($ext === NULL)
		{
			return NULL;   // not a real, permitted image
		}

		$slug = $this->safe_slug($slug);
		if ($slug === '')
		{
			return NULL;
		}

		$filename = $slug . '.' . $ext;
		$abs = $dir . '/' . $filename;

		if (@file_put_contents($abs, $bytes) === FALSE)
		{
			return NULL;
		}
		@chmod($abs, 0644);

		return $prefix . '/' . $filename;
	}

	/**
	 * Convert a base64 column value into a stored file, byte for byte.
	 *
	 * Used by the one-off migration. The decoded bytes are written
	 * unmodified — no resize, no re-encode — so the image the browser draws
	 * is identical to what it was before.
	 *
	 * @param  string $b64
	 * @param  string $slug
	 * @param  int    $tenant_id
	 * @return string|null relative path
	 */
	public function import_base64($b64, $slug, $tenant_id)
	{
		if ( ! is_string($b64) OR trim($b64) === '')
		{
			return NULL;
		}

		$b64 = trim($b64);
		if (strncmp($b64, 'data:', 5) === 0)
		{
			$comma = strpos($b64, ',');
			if ($comma === FALSE)
			{
				return NULL;
			}
			$b64 = substr($b64, $comma + 1);
		}

		$bytes = base64_decode($b64, TRUE);
		if ($bytes === FALSE OR $bytes === '')
		{
			return NULL;
		}

		return $this->store_bytes($bytes, $slug, $tenant_id);
	}

	/**
	 * Delete a tenant's file. The database column is the caller's problem.
	 *
	 * @param  string $value stored column value
	 * @return bool
	 */
	public function delete($value)
	{
		$abs = $this->abs_path($value);
		return $abs ? @unlink($abs) : FALSE;
	}

	// -------------------------------------------------------------- helpers

	/**
	 * Image type of these bytes, as a permitted extension, or NULL.
	 *
	 * getimagesize() parses the file header rather than trusting the
	 * extension or the Content-Type, so a PHP script renamed to .png is
	 * rejected instead of being written to disk.
	 *
	 * @param  string $bytes
	 * @return string|null
	 */
	protected function sniff_ext($bytes)
	{
		$info = @getimagesizefromstring($bytes);
		if ($info === FALSE OR empty($info[2]) OR ! isset($this->allowed[$info[2]]))
		{
			return NULL;
		}
		return $this->allowed[$info[2]];
	}

	/**
	 * Reduce a slug to something safe to put in a filename.
	 *
	 * The slug is developer-supplied, never client-supplied, but this is the
	 * last line of defence against a traversal reaching the write.
	 *
	 * @param  string $slug
	 * @return string
	 */
	protected function safe_slug($slug)
	{
		$slug = strtolower(trim((string) $slug));
		$slug = preg_replace('/[^a-z0-9_-]+/', '-', $slug);
		$slug = trim((string) $slug, '-');
		return substr($slug, 0, 60);
	}

	/**
	 * Best-guess mime for a stored value, so callers can keep writing
	 * `data:image/png;base64,` style strings if they must.
	 *
	 * @param  string $value
	 * @return string
	 */
	public function mime_for($value)
	{
		if ($this->is_path($value))
		{
			$ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
			return isset($this->mime_for_ext[$ext])
				? $this->mime_for_ext[$ext]
				: 'application/octet-stream';
		}
		$info = @getimagesizefromstring(base64_decode((string) $value, TRUE) ?: '');
		if ($info !== FALSE && ! empty($info['mime']))
		{
			return $info['mime'];
		}
		return 'image/png';
	}
}
