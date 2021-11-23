LINK: https://github.com/bisqwit/cysex21
You need: 
– An Apache web server
- PHP configured in it (PHP 7 will do)
– SQLITE3 enabled in PHP
– This repository extracted to some path under the webroot.
– The default database installed
– Database readable and writable by PHP (and also the directory that it resides in!)
On Debian Linux, you could do this all of these with:
– apt-get install libapache2-mod-php php-sqlite3
– cd /var/www/html
- git clone <url>
– cd cysex12
– sqlite3 data/db.db < init/db.sql
– chmod a+rwx data/ data/db.db
you can use http://localhost/cysex21/ to access this demo.

You also need to edit apache2.conf, which is found under /etc/apache2, /etc/httpd, or in a similar location, and find a line that says <Directory /var/www>. Directly under that, locate a line that says "AllowOverride None", and change the None into All and restart the server.
Without this change, the .htaccess file will have no effect.

Instructions for other operating systems follow the same idea, with differing details.

FLAW 1:
exact source link pinpointing flaw 1...
description of flaw 1...
how to fix it...

FLAW 2:
exact source link pinpointing flaw 2...
description of flaw 2...
how to fix it...

...

FLAW 5:
exact source link pinpointing flaw 5...
description of flaw 5...
how to fix it...
