<?php
/*
**********************************************
**********************************************
***PHP cPanel API                          ***
***Copyright Brendan Donahue, 2006         ***
**********************************************
***Feature List:                           ***
***    Connect to cPanel via HTTP or SSL   ***
***    List bandwidth and disk space usage ***
***    Change contact settings/passwords   ***
***    List, create, modify, and delete:   ***
***        Databases and MySQL users       ***
***        FTP and email accounts, quotas  ***
***        Parked, addon, and subdomains   ***
***        Apache redirects                ***
***        Email autoresponders            ***
***        Forwarders and default addresses***
**********************************************
**********************************************
*/

/**
* @ignore
*/
class HTTP
{
	function HTTP($host, $username, $password, $port = 2082, $ssl = '', $theme = 'x')
	{
		$this->ssl = $ssl ? 'ssl://' : '';
		$this->username = $username;
		$this->password = $password;
		$this->theme = $theme;
		$this->auth = base64_encode($username . ':' . $password);
		$this->port = $port;
		$this->host = $host;
		$this->path = '/frontend/' . $theme . '/';
	}

	function getData($url, $data = '')
	{
		$url = $this->path . $url;
		if(is_array($data))
		{
			$url = $url . '?';
			foreach($data as $key=>$value)
			{
				$url .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			$url = substr($url, 0, -1);
		}
		$response = '';
		$fp = fsockopen($this->ssl . $this->host, $this->port);
		if(!$fp)
		{
			return false;
		}
		$out = 'GET ' . $url . ' HTTP/1.0' . "\r\n";
		$out .= 'Authorization: Basic ' . $this->auth . "\r\n";
		$out .= 'Connection: Close' . "\r\n\r\n";
		fwrite($fp, $out);
		while (!feof($fp))
		{
			$response .= @fgets($fp);
		}
		fclose($fp);
		return $response;
	}
}

/**
* Functions to manipulate domains in cPanel
*/
class Rasta_Domain
{
	/**
  * @ignore
  */
	function Rasta_Domain($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x', $domain)
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
		$this->domain = $domain;
	}

	/**
  * Get default address
  *
  * Retrieves the default email address for the domain.
  * @return string
  */
	function getDefaultAddress()
	{
		$default = explode('<b>' . $this->domain . '</b>', $this->HTTP->getData('mail/def.html'));
		if($default[1])
		{
			$default = explode('<td>', $default[1]);
			$default = explode('</td>', $default[1]);
			return trim($default[0]);
		}
	}

	/**
  * Modify default address
  *
  * Changes the default email address for the domain. Returns true on success or false on failure.
  * @param string $adderss new default address
  * @return bool
  */
	function setDefaultAddress($address)
	{
		$data['domain'] = $this->domain;
		$data['forward'] = $address;
		$response = $this->HTTP->getData('mail/dosetdef.html', $data);
		if(strpos($response, 'is now'))
		{
			return true;
		}
		return false;
	}

	/**
  * Park domain
  *
  * Returns true on success or false on failure.
  * @return bool
  */
													function parkDomain()
													{
														$data['domain'] = $this->domain;
														$response = $this->HTTP->getData('park/doaddparked.html', $data);
														//Error from park wrapper
														if(strpos($response, 'was successfully parked')) return true;
														return false;
//														if(strpos($response, 'success') && !strpos($response, 'Error'))
//														{
//															return true;
//														}
//														return false;
													}

	/**
  * Delete parked domain
  *
  * Returns true on success or false on failure.
  * @return bool
  */
													function unparkDomain()
													{
														//has been successfully removed
														//was successfully unparked
														$data['domain'] = $this->domain;
														$response = $this->HTTP->getData('park/dodelparked.html', $data);
														if(strpos($response, 'was successfully unparked')) return true;
														return false;
//														if(strpos($response, 'success') && !strpos($response, 'Error'))
//														{
//															return true;
//														}
//														return $response;
													}

	/**
  * Create addon domain
  *
  * Returns true on success or false on failure.
  * @param string $user username or directory
  * @param string $pass password
  * @return bool
  */
	function addonDomain($user, $pass)
	{
		$data['domain'] = $this->domain;
		$data['user'] = $user;
		$data['pass'] = $pass;
		$response = $this->HTTP->getData('addon/doadddomain.html', $data);
		if(strpos($response, 'added') && !strpos($response, 'Error'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete addon domain
  *
  * Returns true on success or false on failure.
  * @return bool
  */
	function delAddonDomain()
	{
		$data['domain'] = $this->domain;
		$response = $this->HTTP->getData('addon/dodeldomain.html', $data);
		if(strpos($response, 'success') && !strpos($response, 'Error'))
		{
			return true;
		}
		return false;
	}

	/**
  * Create subdomain
  *
  * Returns true on success or false on failure.
  * @param string $subdomain name of subdomain to create
  * @return bool
  */
	function addSubdomain($subdomain)
	{
		$data['domain'] = $subdomain;
		$data['rootdomain'] = $this->domain;
		$response = $this->HTTP->getData('subdomain/doadddomain.html', $data);
		if(strpos($response, 'added') && !strpos($response, 'Error'))
		{
			return true;
		}
		return false;
	}

	/**
  * Get subdomain redirection
  *
  * Returns the URL a subdomain is redirected to.
  * @return string
  */
	function getSubdomainRedirect($subdomain)
	{
		$redirect = array();
		$data['domain'] = $subdomain . '_' . $this->domain;	
		preg_match('/40 value="([^"]*)/', $this->HTTP->getData('subdomain/doredirectdomain.html', $data), $redirect);
		return $redirect[1];
	}

	/**
  * Redirect subdomain
  *
  * Redirects a subdomain of the current domain to another address.
  * @param string $subdomain name of subdomain
  * @param string $url url to redirect to
  * @return bool
  */
	function redirectSubdomain($subdomain, $url)
	{
		$data['domain'] = $subdomain . '_' . $this->domain;
		$data['url'] = $url;
		$response = $this->HTTP->getData('subdomain/saveredirect.html', $data);
		if(strpos($response, 'redirected') && !strpos($response, 'Disabled'))
		{
			return true;
		}
		return false;
	}

	/**
  * Remove subdomain redirection
  *
  * @param string $subdomain name of subdomain
  * @return bool
  */
	function delRedirectSubdomain($subdomain)
	{
		$data['domain'] = $subdomain . '_' . $this->domain;
		$response = $this->HTTP->getData('subdomain/donoredirect.html', $data);
		if(strpos($response, 'disabled'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete subdomain
  *
  * Returns true on success or false on failure.
  * @param string $subdomain name of subdomain to delete
  * @return bool
  */
	function delSubdomain($subdomain)
	{
		$data['domain'] = $subdomain . '_' . $this->domain;
		$response = $this->HTTP->getData('subdomain/dodeldomain.html', $data);
		if(strpos($response, 'Removed'))
		{
			return true;
		}
		return false;
	}
}


?>