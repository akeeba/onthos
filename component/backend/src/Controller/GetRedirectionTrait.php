<?php
/*
 * @package   onthos
 * @copyright Copyright (c) 2024-2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\Onthos\Administrator\Controller;

defined('_JEXEC') || die;

use Joomla\CMS\Uri\Uri;

/**
 * A trait to implement the getRedirection() controller method.
 *
 * @since  1.0.0
 */
trait GetRedirectionTrait
{
	/**
	 * Get the redirection URL.
	 *
	 * A base64-encoded URL is read from the `redirect` URL parameter. If it's missing, invalid, or not an internal URL
	 * then the HTTP Referer is used. If that's empty, or not an internal URL a hardcoded URL to the component's
	 * default view is used instead.
	 *
	 * @return  string
	 * @since   1.0.0
	 */
	private function getRedirection(): string
	{
		$referrer = $this->input->server->getString('HTTP_REFERER');

		if (\is_null($referrer) || !Uri::isInternal($referrer))
		{
			$referrer = 'index.php?option=com_onthos';
		}

		$redirect = $this->input->getBase64('redirect', base64_encode($referrer));
		try
		{
			$redirect = @base64_decode($redirect);
		}
		catch (\Exception)
		{
			$redirect = $referrer;
		}

		if (!Uri::isInternal($redirect))
		{
			$redirect = $referrer;
		}

		return $redirect;
	}

}