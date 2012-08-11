/*
	Mobile device detection JavaScript for "Artodia: Mobile" phpBB style.
	Created by Vjacheslav Trushkin (Arty)

	Check http://www.phpbbmobile.com/ for latest version.
*/
(function() {

	// Check if variables are set
	if (typeof(phpBBMobileStyle) != 'boolean' || typeof(phpBBMobileVar) != 'string')
	{
		return;
	}
	
	// Add CSS for test item
	document.write('<style> .mobileDeviceTest { float: left; } @media only screen and (max-device-width: 600px) { .mobileDeviceTest { float: right; } } </style>');

	// Execute function when document has loaded
	document.addEventListener('DOMContentLoaded', function() {
	
		function redirect(mode)
		{
			try {
				var url = document.location.href;
				url = url + ((url.indexOf('?') > 0) ? '&' : '?') + phpBBMobileVar + '=' + mode;
				document.location.href = url;
			}
			catch (e) {}
		}
	
		// Create test element
		var testItem = document.createElement('div');
		testItem.className = 'mobileDeviceTest';
		testItem.style.display = 'none';
		document.body.appendChild(testItem);
		
		// Get computed style
		if (testItem.currentStyle)
		{
			var style = testItem.currentStyle('float');
		}
		else if (window.getComputedStyle)
		{
			var style = document.defaultView.getComputedStyle(testItem, null).getPropertyValue('float');
		}
		else
		{
			return;
		}

		// Check if browser has applied desktop or mobile style
		switch (style)
		{
			case 'left':
				if (phpBBMobileStyle)
				{
					redirect('off');
				}
				break;
			case 'right':
				if (!phpBBMobileStyle)
				{
					redirect('on');
				}
		}

	}, false);

})();