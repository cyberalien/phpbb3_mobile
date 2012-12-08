/*
	JavaScript for "Artodia: Mobile" phpBB style.
	Created by Vjacheslav Trushkin (Arty)

	This script resizes large images and adds JS events to drop down menus.

	Check http://www.phpbbmobile.com/ for latest version.
*/
var phpBBMobile = {

	/*
		Configuration
	*/
	
	// Resize images
	opResizeImages: true,
	
	// Resize images inside links (ignored if previous option is false)
	opResizeImagesInLinks: true,
	
	/*
		Functions
	*/
	
	// Apply function to each item
	each: function(selector, callback)
	{
		var items = document.querySelectorAll(selector),
			total = items.length;
		for (var i=0; i<total; i++)
		{
			callback.call(items[i], i);
		}
		return items;
	},

	// Check if element has certain class
	hasClass: function(element, className)
	{
		var match = ' ' + className + ' ';
		return (element.className && (' ' + element.className + ' ').indexOf(match) > -1);
	},

	// Add class to element
	addClass: function(element, className)
	{
		if (phpBBMobile.hasClass(element, className))
		{
			return;
		}
		element.className += ((element.className.length > 0) ? ' ' : '') + className;
		return element.className;
	},

	// Remove class from element
	removeClass: function(element, className)
	{
		if (!element.className.length)
		{
			return;
		}
		element.className = (element.className == className) ? '' : (' ' + element.className + ' ').replace(' ' + className + ' ', ' ').replace(/^\s+/, '').replace(/\s+$/, '');
		return element.className;
	},

	// Toggle class
	toggleClass: function(element, className)
	{
		if (phpBBMobile.hasClass(element, className))
		{
			return phpBBMobile.removeClass(element, className);
		}
		return phpBBMobile.addClass(element, className);
	},
	
	// Get computed style
	getStyle: function(item, prop)
	{
		if (typeof (item.currentStyle) == 'function')
		{
			return item.currentStyle(prop);
		}
		else if (window.getComputedStyle)
		{
			return document.defaultView.getComputedStyle(item, null).getPropertyValue(prop);
		}
		return false;
	},
	
	// Get left+right sides
	getSidesStyle: function(item, prop)
	{
		var left = phpBBMobile.getStyle(item, prop + '-left'),
			right = phpBBMobile.getStyle(item, prop + '-right');
		if (left === false || right === false)
		{
			return false;
		}

		left = parseInt(left);
		right = parseInt(right);
		
		return (isNaN(left) || isNaN(right)) ? false : left + right;
	},
	
	// Get element width
	getClientWidth: function(item)
	{
		var diff = 0;

		// Get display mode
		switch (phpBBMobile.getStyle(item, 'display'))
		{
			case 'block':
				var width = parseInt(item.clientWidth);
				return (isNaN(width) || !width) ? false : width;
				
			case 'inline-block':
				var margin = phpBBMobile.getSidesStyle(item, 'margin');
				if (margin === false)
				{
					return false;
				}
				diff += margin;

			case 'inline':
				var padding = phpBBMobile.getSidesStyle(item, 'padding');
				if (padding === false)
				{
					return false;
				}
				diff += padding;
				
				if (!item.parentNode)
				{
					return false;
				}
				
				var width = phpBBMobile.getClientWidth(item.parentNode);
				if (!width)
				{
					return false;
				}
				width += diff;
				
				return width;
				
			default:
				return false;
		}
	},

	// Check image size
	checkImage: function()
	{
		var maxWidth = phpBBMobile.getClientWidth(this.parentNode);
		if (maxWidth < 10)
		{
			return;
		}
		maxWidth -= 10;
		if (this.width > maxWidth)
		{
			phpBBMobile.resizeImage.call(this, maxWidth);
		}
	},

	// Resize image
	resizeImage: function(width)
	{
		var wrapper = document.createElement('span');
		wrapper.className = 'zoom-container';
		wrapper.appendChild(this.cloneNode(true));
		this.parentNode.replaceChild(wrapper, this);

		var img = wrapper.firstChild;
		img.setAttribute('data-max-width', width);
		img.style.maxWidth = width + 'px';
		img.style.cursor = 'pointer';
		phpBBMobile.addClass(img, 'zoom');
		img.addEventListener('click', phpBBMobile.imageClicked);

		var span = document.createElement('span');
		span.className = 'zoom-image';
		wrapper.appendChild(span);
		span.addEventListener('click', phpBBMobile.zoomClicked);
	},

	// Image was clicked
	imageClicked: function()
	{
		if (phpBBMobile.hasClass(this, 'zoomed-in'))
		{
			phpBBMobile.removeClass(this, 'zoomed-in');
			this.style.maxWidth = this.getAttribute('data-max-width') + 'px';
			return;
		}
		phpBBMobile.addClass(this, 'zoomed-in');
		this.style.maxWidth = '';
	},

	// Zoom icon near image was clicked
	zoomClicked: function(event)
	{
		phpBBMobile.imageClicked.apply(this.parentNode.querySelector('img'), arguments);
		event.stopPropagation();
	},

	// Hide all popup menus
	hideMenus: function()
	{
		phpBBMobile.each('.sub-hover', function() {
			phpBBMobile.removeClass(this, 'sub-hover');
		});
		phpBBMobile.each('.menu-hover', function() {
			phpBBMobile.removeClass(this, 'menu-hover');
		});
	},

	// Popup menu
	setupMenu: function(element, menuTrigger, menuItem)
	{
		menuTrigger.addEventListener('click', function(event) {
			event.stopPropagation();
			var hasClass = phpBBMobile.hasClass(menuItem, 'sub-hover');
			phpBBMobile.hideMenus();
			if (!hasClass)
			{
				phpBBMobile.addClass(menuItem, 'sub-hover');
				phpBBMobile.addClass(element, 'menu-hover');
			}
			return false;
		}, false);
		var listItem = document.createElement('li'),
			closeLink = document.createElement('a');
		closeLink.addEventListener('click', phpBBMobile.hideMenus, false);
		closeLink.style.textAlign = 'right';
		closeLink.innerHTML = 'X';
		listItem.appendChild(closeLink);
		menuItem.appendChild(listItem);
	},

	// Initialise stuff
	__construct: function() 
	{
		// Swap .nojs for .hasjs for html element
		phpBBMobile.each('html', function() {
			phpBBMobile.addClass(this, 'hasjs');
			phpBBMobile.removeClass(this, 'nojs');
			if (navigator && navigator.userAgent.indexOf('Opera Mini') > 0)
			{
				phpBBMobile.addClass(this, 'operaMini');
			}
		});

		if (phpBBMobile.opResizeImages)
		{
			if (!phpBBMobile.opResizeImagesInLinks)
			{
				// Mark all images inside links as non-resizable
				phpBBMobile.each('a img', function() {
					phpBBMobile.addClass(this, 'non-resizable');
				});
			}
	
			// Resize all images inside posts
			phpBBMobile.each('.postbody img', function() {
				if (phpBBMobile.hasClass(this, 'non-resizable'))
				{
					return;
				}
				if (this.complete)
				{
					phpBBMobile.checkImage.call(this);
				}
				else
				{
					this.addEventListener('load', phpBBMobile.checkImage, false);
				}
			});
		}

		// Set up header/footer popups
		phpBBMobile.each('#page-header-start > li, #page-header-menu > li, #page-footer-menu > li', function() {
			var element = this,
				menuTrigger = element.querySelector('.menu-link'),
				menuItem = element.querySelector('.sub');
			if (!menuTrigger || !menuItem)
			{
				return;
			}
			phpBBMobile.setupMenu(element, menuTrigger, menuItem);
		});

		// Set up tabs and user profile popups
		phpBBMobile.each('.post-author, .tabs-list', function() {
			var element = this,
				menuTrigger = element.querySelector('a'),
				menuItem = element.querySelector('.sub');
			if (!menuTrigger || !menuItem)
			{
				return;
			}
			menuTrigger.setAttribute('href', 'javascript:void(0);');
			if (this.className.indexOf('settings') < 0)
			{
				menuTrigger.innerHTML += '<span class="arrow-up">&uarr;</span><span class="arrow-down">&darr;</span>';
			}
			phpBBMobile.setupMenu(element, menuTrigger, menuItem);
		});
	}
};

if (document.addEventListener && document.querySelectorAll)
{
	document.addEventListener('DOMContentLoaded', phpBBMobile.__construct, false);
}
