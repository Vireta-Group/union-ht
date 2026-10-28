<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Media helpers
 *
 * Thin wrappers so views never touch Tenant_media directly.
 *
 * CodeIgniter autoloads helpers before libraries, so the library is looked
 * up lazily inside the function body rather than at include time.
 */

if ( ! function_exists('media_url'))
{
	/**
	 * Stored image value -> a usable src for an <img>/<link>.
	 *
	 * Drop-in for the old inline form:
	 *
	 *   before:  src="data:image/png;base64,<?= $setting->ins_logo_path; ?>"
	 *   after:   src="<?= media_url($setting->ins_logo_path, 'image/png'); ?>"
	 *
	 * Returns a file URL for values that have been migrated, an inline data
	 * URI for rows that still hold base64, and a 1x1 transparent GIF when
	 * there is nothing at all — never an empty string, which would make the
	 * browser re-request the current page.
	 *
	 * @param  mixed  $value
	 * @param  string $default_mime only used for a legacy base64 payload
	 * @return string
	 */
	function media_url($value, $default_mime = 'image/png')
	{
		$CI =& get_instance();
		if ( ! isset($CI->tenant_media))
		{
			return Tenant_media::BLANK;
		}
		return $CI->tenant_media->url($value, $default_mime);
	}
}

if ( ! function_exists('media_save'))
{
	/**
	 * Store a $_FILES entry for the current tenant and return the relative
	 * path to put in the database, or NULL if it was not a valid image.
	 *
	 * Replaces:
	 *     'ins_logo_path' => base64_encode(file_get_contents($_FILES['x']['tmp_name']))
	 *
	 * @param  array  $file      one $_FILES entry
	 * @param  string $slug      e.g. 'logo', 'chairman', 'signature'
	 * @param  int    $tenant_id defaults to the resolved tenant
	 * @return string|null
	 */
	function media_save($file, $slug, $tenant_id = NULL)
	{
		$CI =& get_instance();
		if ( ! isset($CI->tenant_media))
		{
			return NULL;
		}
		return $CI->tenant_media->save_upload($file, $slug, $tenant_id);
	}
}

if ( ! function_exists('media_is_path'))
{
	/**
	 * Has this column value already been migrated to a stored file?
	 *
	 * @param  mixed $value
	 * @return bool
	 */
	function media_is_path($value)
	{
		$CI =& get_instance();
		return isset($CI->tenant_media) ? $CI->tenant_media->is_path($value) : FALSE;
	}
}

if ( ! function_exists('validate_secure_image_upload'))
{
	/**
	 * Validates, cleanses and secures an uploaded image against polyglots & malware.
	 * 
	 * 1. Verifies is_uploaded_file and upload status.
	 * 2. Enforces strict file size limits (default 2MB).
	 * 3. Inspects binary magic-bytes via finfo_file.
	 * 4. Scans binary payload for embedded PHP web-shells or script execution tags.
	 * 5. Verifies image dimensions via getimagesizefromstring.
	 * 
	 * @param  array     $file       one $_FILES entry
	 * @param  int       $max_bytes  maximum size allowed (default 2MB)
	 * @return string|false          Sanitized image binary string on success, FALSE on violation.
	 */
	function validate_secure_image_upload($file, $max_bytes = 2097152)
	{
		if (empty($file) || !is_array($file) || empty($file['tmp_name'])) {
			return false;
		}
		if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
			return false;
		}
		if (!is_uploaded_file($file['tmp_name'])) {
			return false;
		}
		if (!empty($file['size']) && $file['size'] > $max_bytes) {
			return false;
		}

		$tmp_path = $file['tmp_name'];

		// Inspect real binary MIME
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $tmp_path);
		finfo_close($finfo);

		$allowed_mimes = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
		if (!in_array($mime, $allowed_mimes, true)) {
			return false;
		}

		$bytes = @file_get_contents($tmp_path);
		if ($bytes === false || strlen($bytes) < 12) {
			return false;
		}

		// Anti-Polyglot: Scan for hidden PHP execution tags or script wrappers inside image
		if (preg_match('/(<\?php|<\?=|<script\b|__halt_compiler|eval\s*\(|base64_decode\s*\(|system\s*\(|shell_exec\s*\()/i', $bytes)) {
			if (function_exists('log_message')) {
				log_message('error', 'Security Alert: Malicious PHP payload detected inside uploaded image from IP ' . ($_SERVER['REMOTE_ADDR'] ?? '-'));
			}
			return false;
		}

		// Ensure getimagesize acknowledges real dimensions
		$img_info = @getimagesizefromstring($bytes);
		if ($img_info === false || empty($img_info[0]) || empty($img_info[1])) {
			return false;
		}

		return $bytes;
	}
}
