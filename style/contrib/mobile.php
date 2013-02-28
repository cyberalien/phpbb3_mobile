<?php

/**
* phpBB Mobile style mod for phpBB 3.0
*
* Created by Vjacheslav Trushkin (Arty) for use with one of mobile phpBB styles.
* See detect_mobile.xml for mod installation instructions.
* Check http://www.phpbbmobile.com/ for latest version.
*
* @version 3.4
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

if (!defined('IN_PHPBB'))
{
    exit;
}

/**
 * Mobile style class
 */
class phpbb_mobile
{
    /**
     * Mobile style path.
     * Change it to correct string to make script locate mobile style faster.
     * Alternatively you can define 'MOBILE_STYLE_PATH' in includes/constants.php or config.php
     *
     * @var string|bool
     */
    public static $mobile_style_path = '';

    /**
     * Mobile style ID, if style is installed.
     * Change it to correct string to make script locate mobile style faster.
     * Alternatively you can define 'MOBILE_STYLE_ID' in includes/constants.php or config.php
     *
     * If mobile style path is set, this variable will be ignored
     *
     * @var int
     */
    public static $mobile_style_id = 0;

    /**
     * True if mobile style should be used for search engines
     *
     * @var bool
     */
    public static $mobile_seo = true;
    
    /**
     * True if link to switch to mobile style should be shown for desktop browsers
     *
     * @var bool
     */
    public static $always_show_link = true;

    /**
     * @var bool
     */
    protected static $mobile_mode = false;
    protected static $mobile_var = 'mobile';
    protected static $cookie_var = false;
    protected static $is_bot = false;
    protected static $is_desktop = false;
    protected static $passed_mobile_path = false;

    /**
     * Start mobile style setup
     *
     * @param bool|string $style_path Path to mobile style, saved to $passed_mobile_path
     */
	public static function setup($style_path = false)
	{
		global $user;
	
		self::set_cookie_var();
		self::override_template();
		self::$is_bot = empty($user->data['is_bot']) ? false : $user->data['is_bot'];
		
		// Check mode only if it wasn't checked already
		if (self::$mobile_mode === false)
		{
			if (is_string($style_path) && strlen($style_path))
			{
				self::$passed_mobile_path = $style_path;
			}
			elseif (is_int($style_path) && $style_path > 0)
			{
				self::$mobile_style_id = $style_path;
			}
			self::$mobile_mode = self::get_mode();
		}

		if (self::is_desktop_mode(self::$mobile_mode))
		{
			// Force desktop style
			return;
		}

		if (!self::is_mobile_mode(self::$mobile_mode))
		{
			// Detect browser
			$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
			if (!self::is_mobile_browser($user_agent))
			{
				self::$is_desktop = true;
				return;
			}
		}

		// Locate mobile style
		$path = self::locate_mobile_style();
		if ($path === false)
		{
			// Mobile style was not found
			self::set_cookie('404', false);
			self::$mobile_mode = '404';
			return;
		}

		// Set mobile style data
		self::set_mobile_style();
	}
	
	/**
	* Set mobile style
	*/
	protected static function set_mobile_style()
	{
		global $user, $template;
		
		// Define MOBILE_STYLE if it is not defined yet
		if (!defined('MOBILE_STYLE'))
		{
			define('MOBILE_STYLE', true);
		}
		
		// Change user->theme data
		$user->theme = array_merge($user->theme, self::user_theme_data());
		$template->orig_tpl_inherits_id = $user->theme['template_inherits_id'];

		// Reset imageset
		$user->img_array = array();
		
		// Set template
		$template->set_template();
	}
	
	/**
	* Override global $template variable
	*/
	protected static function override_template()
	{
		if (defined('ADMIN_START'))
		{
			return;
		}

		global $template;
		if (is_a($template, 'mobile_template'))
		{
			return;
		}
		$tpl = new mobile_template();
		$tpl->clone_properties($template);
		$template = $tpl;
	}
	
	/**
	* @return array Data for $user->theme
	*/
	protected static function user_theme_data()
	{
		return array(
			'style_id'	=> self::$mobile_style_id,
			'template_storedb'	=> 0,
			'template_path' => self::$mobile_style_path,
			'template_id'	=> 0,
			'bbcode_bitfield'	=> 'lNg=',
			'template_inherits_id'	=> 1,
			'template_inherit_path'	=> 'prosilver',
			'theme_path'	=> self::$mobile_style_path,
			'theme_name'	=> self::$mobile_style_path,
			'theme_storedb'	=> 0,
			'theme_id'		=> 0,
			'imageset_path'	=> self::$mobile_style_path,
			'imageset_id'	=> 0,
			'imageset_name'	=> self::$mobile_style_path,
		);
	}

	/**
	* Set cookie variable name
	*/
	protected static function set_cookie_var()
	{
		if (self::$cookie_var !== false)
		{
			return;
		}
		global $config;
		self::$cookie_var = (isset($config['cookie_name']) ? $config['cookie_name'] . '_' : '') . self::$mobile_var;
	}
	
	/**
	* Set cookie value
	*
	* param string $value Cookie value
	* param bool $long If true, cookie will be set for full duration. If false, cookie will be set for browser session duration
	*/
	protected static function set_cookie($value, $long = true)
	{
		global $config;
		$cookietime = ($long && empty($_SERVER['HTTP_DNT'])) ? time() + (($config['max_autologin_time']) ? 86400 * (int) $config['max_autologin_time'] : 31536000) : 0;
		$name_data = rawurlencode(self::$cookie_var) . '=' . rawurlencode($value);
        $expire = ($cookietime) ? gmdate('D, d-M-Y H:i:s \\G\\M\\T', $cookietime) : '';
		$domain = (!$config['cookie_domain'] || $config['cookie_domain'] == 'localhost' || $config['cookie_domain'] == '127.0.0.1') ? '' : '; domain=' . $config['cookie_domain'];
		header('Set-Cookie: ' . $name_data . (($cookietime) ? '; expires=' . $expire : '') . '; path=' . $config['cookie_path'] . $domain . ((!$config['cookie_secure']) ? '' : '; secure') . '; HttpOnly', false);
	}
	
	/**
	* Check if mode triggers mobile style
	*
	* @param string $mode Browser mode
	*
	* @return bool
	*/
	protected static function is_mobile_mode($mode)
	{
		return ($mode == 'mobile');
	}
	
	/**
	* Check if mode triggers desktop style
	*
	* @param string $mode Browser mode
	*
	* @return bool
	*/
	protected static function is_desktop_mode($mode)
	{
		return ($mode == 'desktop' || $mode == '404');
	}
	
	/*
	* Get browser mode from cookie and $_GET data
	*
	* @return string|false Mobile style mode, false if default
	*/
	protected static function get_mode()
	{
		$value = request_var(self::$mobile_var, '');
		switch ($value)
		{
			case 'reset':
				// Reset to default mode
				self::set_cookie('', false);
				return false;

			case 'on': 
				// Mobile device detected by JavaScript
				$value = 'mobile';
				self::set_cookie($value, false);
				return $value;

			case 'off':
				// Desktop detected by JavaScript
				$value = 'desktop';
				self::set_cookie($value, false);
				return $value;

			case '404': // Mobile style detected, but not found
			case 'desktop': // Force desktop style
			case 'mobile': // Force mobile style
				self::set_cookie($value, $value != '404');
				return $value;
		}
		
		if (isset($_COOKIE[self::$cookie_var]))
		{
			switch ($_COOKIE[self::$cookie_var])
			{
				case 'mobile': // Force mobile style
				case 'desktop': // Force desktop style
					$value = $_COOKIE[self::$cookie_var];
					return $value;
			}
		}
		else
		{
			self::set_cookie('');
		}

		return false;
	}
	
	/**
	* Return path to styles directory
	*
	* @param bool $include_style_path If true, result will include mobile style path
	*
	* @return string Path to 'styles' or to 'styles/' + mobile style path
	*/
	protected static function styles_path($include_style_path = false)
	{
		global $phpbb_root_path;
		return $phpbb_root_path . 'styles' . ($include_style_path ? '/' . self::$mobile_style_path : '');
	}

	/**
	* Check user agent string for mobile browser id.
	*
	* @param string $user_agent User agent string
	*
	* @return bool True if mobile browser
	*/
	public static function is_mobile_browser($user_agent)
	{
		if (self::$mobile_seo && self::$is_bot)
		{
			return true;
		}
		if ((strpos($user_agent, 'Mobile') === false && 	// Generic mobile browser string, most browsers have it.
			strpos($user_agent, 'SymbianOS') === false &&	// Nokia device running Symbian OS.
			strpos($user_agent, 'Opera M') === false && // Opera Mini or Opera Mobile.
			strpos($user_agent, 'Android') === false && // Android devices that don't have 'Mobile' in UA string.
			stripos($user_agent, 'HTC_') === false &&	// HTC devices that don't have 'Mobile' nor 'Android' in UA string. Case insensitive.
			strpos($user_agent, 'Fennec/') === false && 	// Firefox mobile
			strpos($user_agent, 'Kindle') === false && 	// Kindle Fire tablet
			strpos($user_agent, 'BlackBerry') === false) ||	// BlackBerry
			strpos($user_agent, 'iPad') !== false)	// iPad should be excluded
		{
			// Not a mobile browser
			return false;
		}
		// Mobile browser
		return true;
	}
	
	/**
	* Check if mobile style exists
	*
	* @param string $path Directory name of mobile style
	*
	* @return bool True if mobile style exists
	*/
	protected static function check_style_path($path)
	{
		// Locate and read style.cfg
		$style_cfg = self::styles_path() . '/' . $path . '/style.cfg';
		if (!file_exists($style_cfg))
		{
			return false;
		}
		$style_cfg_data = @file_get_contents($style_cfg);
		if ($style_cfg_data === false || strpos($style_cfg_data, 'mobile') === false)
		{
			return false;
		}
		
		// Check style.cfg for "mobile = 1"
		foreach (explode("\n", $style_cfg_data) as $row)
		{
			$list = explode('=', $row);
			if (count($list) == 2 && trim($list[0]) == 'mobile' && trim($list[1]) == '1')
			{
				return true;
			}
		}

		return false;
	}

	/**
	* Check if mobile style exists
	*
	* @param int $id Style id
	*
	* @return string|bool Path to style if mobile style exists, false on error
	*/
	protected static function check_style_id($id)
	{
		global $db;
		$id = (int) $id;
		$sql = 'SELECT t.template_path
			FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . " t
			WHERE s.style_id = $id
				AND t.template_id = s.template_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row === false || !self::check_style_path($row['template_path']))
		{
			return false;
		}
		return $row['template_path'];
	}

	/**
	* Locate mobile style
	*
	* @param bool $check_db If true and mobile style id is set, script will check phpbb_styles table for mobile style
	* @param bool $check_dirs If true, script will search for mobile style in styles directory
	*
	* @return string|bool Mobile style path
	*/
	public static function locate_mobile_style($check_db = true, $check_dirs = true)
	{
		// Locate by style path
		if (defined('MOBILE_STYLE_PATH') && self::check_style_path(MOBILE_STYLE_PATH))
		{
			self::$mobile_style_path = MOBILE_STYLE_PATH;
			return self::$mobile_style_path;
		}
		if (!empty(self::$mobile_style_path) && self::check_style_path(self::$mobile_style_path))
		{
			return self::$mobile_style_path;
		}

		// Locate by style id
		if ($check_db && defined('MOBILE_STYLE_ID') && ($path = self::check_style_id(MOBILE_STYLE_ID)) !== false)
		{
			self::$mobile_style_path = $path;
			return $path;
		}
		if ($check_db && self::$mobile_style_id && (!defined('MOBILE_STYLE_ID') || MOBILE_STYLE_ID != self::$mobile_style_id) && ($path = self::check_style_id(self::$mobile_style_id)) !== false)
		{
			self::$mobile_style_path = $path;
			return $path;
		}

		// Default style path, passed in session.php
		if (!empty(self::$passed_mobile_path) && self::check_style_path(self::$passed_mobile_path))
		{
			self::$mobile_style_path = self::$passed_mobile_path;
			return self::$mobile_style_path;
		}

		// Search styles directory
		if (!$check_dirs)
		{
			return false;
		}
		$styles_dir = self::styles_path();
		$iterator = new DirectoryIterator($styles_dir);
		foreach ($iterator as $fileinfo)
		{
			if ($fileinfo->isDir() && self::check_style_path($fileinfo->getFilename()))
			{
				self::$mobile_style_path = $fileinfo->getFilename();
				return self::$mobile_style_path;
			}
		}

		return false;
	}
	
	/**
	* Create HTML code for mobile style link
	*
	* @param template $template Template class instance
	* @param string $mode Mode to add to URL
	* @param string $lang Language variable for link text
	*
	* @return string HTML code for link
	*/
	protected static function create_link($template, $mode, $lang)
	{
		global $user;
		$text = '';
		$lang_key = 'MOBILE_STYLE_' . strtoupper($lang);
		switch ($lang)
		{
			case 'switch_full':
				$text = isset($user->lang[$lang_key]) ? $user->lang[$lang_key] : 'Switch to full style';
				break;

			case 'switch_mobile':
				$text = isset($user->lang[$lang_key]) ? $user->lang[$lang_key] : 'Switch to mobile style';
				break;

			case 'not_found':
				$text = isset($user->lang[$lang_key]) ? $user->lang[$lang_key] : 'Error locating mobile style files';
				break;
				
			default:
				return '';
		}
		$link = $template->_rootref['U_INDEX'];
		if (!empty($template->_rootref['U_VIEW_TOPIC']))
		{
			$link = $template->_rootref['U_VIEW_TOPIC'];
		}
		elseif (!empty($template->_tpldata['navlinks']) && isset($template->_tpldata['navlinks'][count($template->_tpldata['navlinks']) - 1]['U_VIEW_FORUM']))
		{
			$link = $template->_tpldata['navlinks'][count($template->_tpldata['navlinks']) - 1]['U_VIEW_FORUM'];
		}
		$link .= (strpos($link, '?') === false ? '?' : '&amp;') . self::$mobile_var . '=' . $mode;
		return '<a href="' . $link . '">' . $text . '</a>';
	}
	
	/**
	* Add data to overall_footer.html
	*
	* @param mobile_template $template Template class instance
	*
	* @return string Empty string
	*/
	public static function template_footer($template)
	{
		if (defined('MOBILE_STYLE') && self::$is_bot)
		{
			return '';
		}

		$link = '';
		switch (self::$mobile_mode)
		{
			case '404':
				$link = self::create_link($template, 'mobile', 'not_found');
				break;
				
			case 'mobile':
				$link = self::create_link($template, 'desktop', 'switch_full');
				break;
				
			case 'desktop':
				$link = self::create_link($template, 'mobile', 'switch_mobile');
				break;
			
			case '':
				if (!defined('MOBILE_STYLE') && (self::$always_show_link || !self::$is_desktop))
				{
					// Detected desktop style
					$link = self::create_link($template, 'mobile', 'switch_mobile');
				}
				elseif (defined('MOBILE_STYLE'))
				{
					// Detected mobile style
					$link = self::create_link($template, 'desktop', 'switch_full');
				}
				break;
		}
		if (strlen($link))
		{
			echo '<div class="mobile-style-switch mobile-style-switch-footer" style="padding: 5px; text-align: center;">' . $link . '</div>';
		}
		return '';
	}

	/**
	* Add data to overall_header.html
	*
	* @param mobile_template $template Template class instance
	*
	* @return string HTML code to echo after overall_header.html
	*/
	public static function template_header($template)
	{
		if (defined('MOBILE_STYLE') && self::$is_bot)
		{
			return '';
		}

		$link = '';
		switch (self::$mobile_mode)
		{
			case 'mobile':
				if (isset($_GET[self::$mobile_var]))
				{
					// Show link below header only if style was just switched
					$link = self::create_link($template, 'desktop', 'switch_full');
				}
				break;
				
			case 'desktop':
				if (isset($_GET[self::$mobile_var]))
				{
					// Show link below header only if style was just switched
					$link = self::create_link($template, 'mobile', 'switch_mobile');
				}
				break;
			
			case '':
				self::include_js($template);
				if (defined('MOBILE_STYLE') && !isset($_COOKIE[self::$cookie_var]))
				{
					// Detected mobile style
					$link = self::create_link($template, 'desktop', 'switch_full');
				}
				break;
		}
		if (strlen($link))
		{
			return '<div class="mobile-style-switch mobile-style-switch-header" style="padding: 5px; text-align: center;">' . $link . '</div>';
		}
		return '';
	}
	
	/**
	* Attempt to include detect.js from mobile style
	*
	* @param mobile_template $template Template class instance
	*/
	protected static function include_js($template)
	{
		if (defined('MOBILE_STYLE') && self::$is_bot)
		{
			return;
		}

		if (count($_POST) || isset($_GET[self::$mobile_var]))
		{
			// Do not redirect on forms or when URL has mode
			return;
		}
	
		// Locate mobile style
		if (self::locate_mobile_style(false, false) === false)
		{
			return;
		}
		
		$script = self::styles_path(true) . '/template/detect.js';
		if (!@file_exists($script))
		{
			return;
		}

		$template->_rootref['META'] = (isset($template->_rootref['META']) ? $template->_rootref['META'] : '') . '<script type="text/javascript"> var phpBBMobileStyle = ' . (defined('MOBILE_STYLE') ? 'true' : 'false') . ', phpBBMobileVar = \'' . addslashes(self::$mobile_var) . '\'; </script><script type="text/javascript" src="' . htmlspecialchars($script) . '?t=' . @filemtime($script) . '"></script>';
	}
}

/**
* Extend template class to override _tpl_include()
*/
class mobile_template extends template
{
	/**
	* Override _tpl_include function
	*/
	function _tpl_include($filename, $include = true)
	{
		if ($include)
		{
			$to_echo = '';
			if ($filename == 'overall_footer.html')
			{
				$to_echo = phpbb_mobile::template_footer($this);
			}
			if ($filename == 'overall_header.html')
			{
				$to_echo = phpbb_mobile::template_header($this);
			}
		}
		parent::_tpl_include($filename, $include);
		if ($include)
		{
			echo $to_echo;
		}
	}
	
	/**
	* Clone template class properties
	*
	* @param template $template Template class instance to copy from
	*/
	public function clone_properties($template)
	{
		foreach (array_keys(get_class_vars(get_class($this))) as $var)
		{
			$this->$var = $template->$var;
		}
	}
}
