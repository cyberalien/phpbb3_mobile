
var blocks = ['page-header-start', 'page-header-menu', 'page-footer-menu'];
window.onload = function()
{
    window.onscroll = hidePopups; 
    window.onresize = hidePopups; 
    updateContent();
    hidePopups();
}

/* Hide popups */
function hidePopups()
{
    var i, list;
    list = document.getElementsByClassName('sub-hover');
    for(i=0; i<list.length; i++) list[i].className = 'sub';
    list = document.getElementsByClassName('li-hover');
    for(i=0; i<list.length; i++) list[i].className = list[i].className.replace(/li\-hover/g, '');
}

/* Popups */
function updateItem(item, getChildren)
{
    var i, list = item.getElementsByClassName('sub');
    for(i=0; i<list.length; i++)
    {
        list[i].className = 'sub';
    }
    if(getChildren)
    {
        list = item.childNodes;
        for(i=0; i<list.length; i++)
        {
            if(typeof(list[i].className) == 'undefined') list[i].className = '';
            list[i].onmouseover = menuOver;
            list[i].onmouseout = menuOut;
        }
    }
    else
    {
        if(typeof(item.className) == 'undefined') item.className = '';
        item.onmouseover = menuOver;
        item.onmouseout = menuOut;
    }
}

/* Add events to popups, resize images */
function updateContent()
{
    var i, list;
    for(i=0; i<blocks.length; i++)
    {
        updateItem(document.getElementById(blocks[i]), true);
    }
    list = document.getElementsByClassName('post-author');
    for(i=0; i<list.length; i++)
    {
        updateItem(list[i], false);
    }
    document.body.className = document.body.className + ' js';
    list = document.getElementsByTagName('img');
    for(i=0; i<list.length; i++)
    if(list[i].className == '')
    {
        if(list[i].complete) checkImage(list[i]);
        else list[i].onload = function() { checkImage(this); }
    }
    list = document.getElementsByClassName('post-image');
    for(i=0; i<list.length; i++)
    {
        if(list[i].complete) resizeImage(list[i], true);
        else list[i].onload = function() { resizeImage(this, true); };
    }
}

/* Events */
function menuOver()
{
    this.className += ' li-hover';
    this.getElementsByClassName('sub')[0].className = 'sub sub-hover';
}
function menuOut()
{
    this.className = this.className.replace(/li\-hover/g, '');
    this.getElementsByClassName('sub')[0].className = 'sub';
}
function checkImage(img)
{
    var max = Math.floor(img.parentNode.clientWidth - 10);
    if(img.width > max) resizeImage(img, true);
}
function resizeImage(img, setEvents)
{
    var max = Math.floor(img.parentNode.clientWidth - 10);
    if(img.width > max && img.width > 150)
    {
        img.style.maxWidth = max + 'px';
        if(setEvents)
        {
            img.style.cursor = 'pointer';
            img.onclick = clickedImage;
        }
    }
}
function clickedImage()
{
    if(this.className == 'post-image clicked')
    {
        this.className = 'post-image';
        resizeImage(this, false);
        return;
    }
    this.className = 'post-image clicked';
    this.style.maxWidth = '';
}

