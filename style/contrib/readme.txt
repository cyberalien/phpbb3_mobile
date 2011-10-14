How to add automatic mobile device detection to your forum:

1. Open detect_mobile.xml in a browser. If your browser can't open file, try a different browser. Safari or Chrome are recommended.

2. Edit files includes/session.php and includes/functions.php as written in instructions.

3. Install mobile style on your forum, make sure it is active. Change 0 to id of your style in the following line in includes/session.php:
 $mobile_style_id = 0; 

