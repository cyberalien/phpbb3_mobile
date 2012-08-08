var phpBBMobile = {
	
	// List of blocks with popup menus
	blocks: ['page-header-start', 'page-header-menu', 'page-footer-menu'],
	
	// Initialise stuff
	__construct: function() 
	{
	}
};

if (document.addEventListener)
{
	document.addEventListener('DOMContentLoaded', phpBBMobile.__construct, false);
}



