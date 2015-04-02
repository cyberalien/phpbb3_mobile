/*
	Mobile device detection JavaScript for "Artodia: Mobile" phpBB style.
	Created by Vjacheslav Trushkin (Arty)

	Check http://www.artodia.com/phpbb-styles/mobile/ for latest version.
*/
(function() {

	// Check if variables are set
	if (typeof(phpBBMobileStyle) != 'boolean' || typeof(phpBBMobileVar) != 'string')
	{
		return;
	}

	alert('testing!!!');

	function redirect(mode)
	{
		try {
			var url = document.location.href;
			url = url + ((url.indexOf('?') > 0) ? '&' : '?') + phpBBMobileVar + '=' + mode;
			document.location.href = url;
		}
		catch (e) {}
	}

	if (window.matchMedia)
	{
		if (window.matchMedia('max-device-width: 720px').matches)
		{
			if (!phpBBMobileStyle)
			{
				redirect('on');
			}
		}
		else
		{
				if (phpBBMobileStyle)
				{
					redirect('off');
				}
		}
	}
})();