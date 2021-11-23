LINK: https://github.com/bisqwit/cysex21
You need: 
– An Apache web server
— PHP configured in it (PHP 7 will do)
– SQLITE3 enabled in PHP
– This repository extracted to some path under the webroot.
– The default database installed
– Database readable and writable by PHP (and also the directory that it resides in!)
On Debian Linux, you could do this all of these with:
– apt-get install libapache2-mod-php php-sqlite3
– cd /var/www/html
— git clone <url>
– cd cysex12
– sqlite3 data/db.db < init/db.sql
– chmod a+rwx data/ data/db.db
you can use http://localhost/cysex21/ to access this demo.

You also need to edit apache2.conf, which is found under /etc/apache2, /etc/httpd, or in a similar location, and find a line that says <Directory /var/www>. Directly under that, locate a line that says "AllowOverride None", and change the None into All and restart the server.
Without this change, the .htaccess file will have no effect.

Instructions for other operating systems follow the same idea, with differing details.

FLAW 1:
https://github.com/bisqwit/cysex21/blob/master/index.php#L18
All passwords of all users are stored in plain text in the database.
The consequences of this problem are that if anyone acquires a copy of the database file, or otherwise gains access to the database through another security hole, they can immediately gain access to the login credentials of all users.
This is a violation of:
  A02:2021-Cryptographic Failures
  A07:2021-Identification_and_Authentication_Failures
To fix it, comment out the line, enabling salted and encrypted passwords.

FLAW 2:
https://github.com/bisqwit/cysex21/blob/master/index.php#L78
https://github.com/bisqwit/cysex21/blob/master/index.php#L97
https://github.com/bisqwit/cysex21/blob/master/index.php#L98
Posts posted by users are shown without proper escapes. This enables users to post arbitrary HTML on the page, causing XSS vulnerabilities among others.
For example, one can create a blog post that says <script language=javascript>alert("Your system is compromised! Call Microsoft immediately at +1-400-7005445 or your files may be already deleted!")</script>, and that alert will be shown as a popup in the user’s web browser.

This is a violation of:
  A03:2021-Injection
To fix it,
  replace $post['title'] with htmlspecialchars($post['title']) on line 97
  replace $post['content'] with htmlspecialchars($post['content']) on line 98
  replace $title with ".htmlspecialchars($title)." on line 78

FLAW 3:
https://github.com/bisqwit/cysex21/blob/master/index.php#L87
Users can delete posts in other users’ blogs. The system does not check if the deletion request is being performed by the user who owns that blog, or whether, in fact, the user is even logged in at all.
This is a violation of:
  A01:2021-Broken Access Control 
  A07:2021-Identification and Authentication Failures
To fix this, add these two lines below line 86:
  if(!@$_SESSION['userid'])                  { header('HTTP/1.0 403 ACCESS DENIED'); exit; }
  if($_SESSION['userid'] != $post['userid']) { header('HTTP/1.0 403 ACCESS DENIED'); exit; }

FLAW 4:
https://github.com/bisqwit/cysex21/blob/master/.htaccess#L4
The source code of the entire website can be read from backup files left by the editor.
For example, you can read change the URL to /index.php~ and it will show the site source code.
This is a violation of:
  A05:2021-Security Misconfiguration
The biggest problem is not that these source code files exist, but that the server allows users to request and to read them.
This can be fixed by changing line 4 in .htaccess to say "Deny from all".

FLAW 5:
https://github.com/bisqwit/cysex21/blob/master/.htaccess#L8
The database file can be read by anyone.
For example, you can read change the URL to /data/db.db, and it lets anyone download the database file.
This is a serious data breach problem. Combined with FLAW 1, this allows leaking of user credentials. If someone has used the same password at multiple websites, their login credentials for other sites would be breached too.
This is a violation of:
  A05:2021-Security Misconfiguration
This can be fixed by changing line 8 in .htaccess to say "Deny from all".

FLAW 6:
https://github.com/bisqwit/cysex21/blob/master/inc/data.php#L12
The system has SQL injection vulnerability at the login page.
This is a violation of:
  A03:2021-Injection
It is possible for anyone to log in to the system as admin, by entering anything arbitrary as the username, and entering the following string as password: ' or 1=1 and isadmin=1 and ''=' including the quotation marks. The log-in will be successful, and the system will treat the user as if they logged in as the first admin account.

This can be fixed by replacing line 12 in inc/data.php with this code:
  return dbquery("select * from users where name=? and password=?, [$u,$p])->fetchArray();

FLAW 7:
https://github.com/bisqwit/cysex21/blob/master/index.php#L54
The system does not maintain any sort of a log of login attempts.
This is a violation of:
  A09:2021-Security Logging and Monitoring Failures

Fixing this would be more complicated. One could add a table in init/db.sql, such as:
  create table login_attempts
  (
    id           integer primary key autoincrement,
    attempt_time datetime not null,
    attempt_name text,
    attempt_pass text,
    ipaddress    text,
    useragent    text    
  );
  create index a on login_attempts(attempt_time);
And then add lines before line 54 in index.php such as:
  dbquery('insert into login_attempts(attempt_time,attempt_name,attempt_pass,ipaddress,useragent)values(?,?,?,?,?)',
    [date('Y-m-d H:i:s',$_POST['u'],$_POST['p'], $_REQUEST['REMOTE_ADDR'],$_REQUEST['HTTP_USER_AGENT']]);
However, the log would also have to be monitored, and alerts generated.

Because this is a tiny side project created just to pass this course, this would be a disproportional challenge to provide a fix for.

There is also the fact that the site does not provide means to change passwords, so the admin’s password always remains the default, ”admin”.
This is a glaring oversight of course, but again, it’s a 1cr course. One has to draw the line somewhere.

