<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Phosphor icon helper.
 *
 * The product site (application/views/saas/) used to be a React SPA that
 * imported SVG components from @phosphor-icons/react, bundled by Vite. Those
 * views are now server-rendered PHP, so the icons need a font-based equivalent.
 *
 * Phosphor ships a webfont with the same icon names in kebab-case, loaded from
 * unpkg in saas/head.php — the same three stylesheets front/partials/header.php
 * already loads for the parishad portal. So <Headset size={26} weight="fill" />
 * becomes icon('headset', 26, 'fill').
 *
 * Why a helper and not inline markup: the product site uses ~150 icons across
 * nine pages. Hand-writing <i class="..."> at each site drifts, and a typo in a
 * class name renders an invisible box rather than failing.
 */
if (!function_exists('icon'))
{
	/**
	 * Render a Phosphor icon.
	 *
	 * @param string $name   Icon name in kebab-case, e.g. 'whatsapp-logo'.
	 *                       The PascalCase form used by @phosphor-icons/react
	 *                       ('WhatsappLogo') is accepted too — see below.
	 * @param int    $size   Pixel size, mapped to a rem-based font-size.
	 * @param string $weight 'regular' | 'bold' | 'fill'
	 * @param string $class  Extra classes on the <i> element.
	 * @return string
	 */
	function icon($name, $size = 16, $weight = 'regular', $class = '')
	{
		// Accept the PascalCase spelling the React components used so the
		// ported markup reads the same as the source it came from.
		if (preg_match('/[A-Z]/', $name)) {
			$name = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
		}

		static $classes = array(
			'regular' => 'ph',
			'bold'    => 'ph-bold',
			'fill'    => 'ph-fill',
		);

		$cls = isset($classes[$weight]) ? $classes[$weight] : $classes['regular'];

		return '<i class="' . $cls . ' ph-' . html_escape($name) . ' ' . html_escape($class) . '"'
			. ' style="font-size:' . (int) $size . 'px" aria-hidden="true"></i>';
	}
}
