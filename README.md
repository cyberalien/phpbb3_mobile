## ABOUT

* phpBB mobile style
* phpBB versions: 3.0.x
* Author: [Vjacheslav Trushkin](http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=5926)
* Website: [artodia.com](http://www.artodia.com/)

## HOW TO USE IT

* Install style on your forum
* Open contrib/detect_mobile.xml in browser, follow instructions to add automatic mobile device detection
* In code that you have added to includes/session.php find this line:

        phpbb_mobile::setup('art_mobile');
and replace art_mobile with correct directory name of this style

## BRANCHES

There are several branches that include different versions of mobile style:

* master: prosilver style
* iphone: iPhone layout (slightly modified prosilver)
* elegant: "Elegant Mobile" style

## LICENSE

[GNU General Public License v2](http://opensource.org/licenses/gpl-2.0.php)
