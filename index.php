<?php
require_once 'inc/data.php';

/* A blog website deliberately designed to have vulnerabilities. */

# Planned vulnerabilities:
#    Passwords stored in plain text                                 DONE  A02:2021-Cryptographic Failures
#                                                                         A07_2021-Identification_and_Authentication_Failures/
#    Blog text is not html-escaped when viewed                      DONE  A03:2021-Injection
#    Users can delete posts in other users's blogs                  DONE  A01:2021-Broken Access Control 
#                                                                         A07:2021-Identification and Authentication Failures
#    Site source code can be read from backup file left by editor   DONE  A05:2021-Security Misconfiguration
#    Database file can be downloaded by anyone                      DONE  A05:2021-Security Misconfiguration
#    SQL injection possible at login                                DONE  A03:2021-Injection

function make_password($p)
{
  return $p; // Use plaintext passwords for ease of debugging
  global $db;
  $salt = dbquery('select value from settings where name=?', 'salt')->fetchArray()[0];
  $salt0 = 'xx';
  $salt1 = $salt;
  return hash('sha512', "{$salt0}$p{$salt1}");
}

session_start();
ob_start();
?><html><head><title>Blog</title></head><body>
<script language=Javascript>
function del(id,uid){
  if(!confirm('Are you sure?')) return;
  document.getElementById('delid').value = id
  document.getElementById('uid').value = uid
  document.getElementById('delf').submit()
}
</script><?php

switch(@$_GET['op'])
{
  case 'logout':
    $_SESSION['userid'] = null;
    header('Location: ?#');
    exit;
  case 'login':
    $u = FindUser($_POST['u'], make_password($_POST['p']));
    if($u)
    {
      $_SESSION['userid']   = $u['id'];
      $_SESSION['username'] = $u['name'];
      header('Location: ?#');
    }
    else
    {
      header('Location: ?op=loginfail#');
    }
    exit;
  case 'loginfail':
    print '<h2>Login failure</h2>';
    break;
  case 'register':
    if(!@$_SESSION['userid']) { header('Location: ?#'); exit; }
    if(FindUserByName($_POST['u'])) { print '<h2>User already exists</h2>'; break; }
    AddUser($_POST['u'], make_password($_POST['p']), false);
    print '<h2>User added</h2>';
    break;
  case 'list':
  listp:
    $u = FindUserById($_GET['uid']);
    if(!$u) { print '<h2>No such user</h2>'; break; }
    $posts = FindPosts($u['id']);
    if(empty($posts)) { print '<h2>No posts</h2>'; goto selus; }
    print 'Select post to read:<ul>';
    foreach($posts as $post)
    {
      $link  = "?op=read&id={$post['id']}";
      $title = $post['title'];
      print "<li>";
      print "<a href='$link'>$title</a>";
      if($u['id'] == $_SESSION['userid']) print " <button onclick='del({$post['id']},{$u['id']})'>Delete</button> ";
      print '</li>';
    }
    print '</ul>';
    break;
  case 'del':
    $post = FindPost($_POST['id']);
    if(!$post) { print '<h2>Post does not exist.</h2>'; break; }
    DeletePost($_POST['id']);
    print '<h2>Post deleted</h2>';
    $_GET['uid'] = $_POST['uid'];
    goto listp;
  case 'read':
    $post = FindPost($_GET['id']);
    if(!$post) { print '<h2>Post does not exist.</h2>'; break; }
    print '<section style="border:1px solid black">';
    echo 'Posted at: ', $post['posttime'], '<br>';
    print '<details>';
     echo '<summary>', $post['title'], '</summary>';
     echo '<p>',       $post['content'], '</p>';
    print '</details>';
    print '</section>';
    goto selus;
  case 'add':
    if(!@$_SESSION['userid']) { header('Location: ?#'); exit; }
    $id = CreatePost($_SESSION['userid'], $_POST['title'], $_POST['content']);
    header("Location: ?op=read&id=$id");
    exit;
  default: selus:
    $users = FindUsers();
    print 'Select user whose posts to read:';
    print '<ul>';
    foreach($users as $user)
     echo "<li><a href='?op=list&amp;uid={$user['id']}'>", htmlspecialchars($user['name']), '</a></li>';
    print '</ul>';
    break;
}

print '<hr>';
if(!@$_SESSION['userid'])
{
  print '<form method=post action="?op=login">';
   print '<legend>Log in (existing user)</legend>';
   print '<label for=u>Username:</label> <input type=text     id=u name=u><br>';
   print '<label for=p>Password:</label> <input type=password id=p name=p><br>';
   print '<input type=submit value="Log in">';
  print '</form>';
  #print '<form method=post action="?op=register">';
  # print '<legend>Register as a new user:</legend>';
  # print '<label for=u>Username:</label> <input type=text     id=u name=u><br>';
  # print '<label for=p>Password:</label> <input type=password id=p name=p><br>';
  # print '<input type=submit value="Log in">';
  #print '</form>';
}
else
{
  printf("Logged in as <u>%s</u>", htmlspecialchars($_SESSION['username']));
  print '<form method=post action="?op=add"><legend><b>Add post:</b></legend>';
   print '<label for t>Title:</label> <input type=text id=t name=title size=40><br>';
   print '<textarea name=content cols=40 rows=10></textarea><br>';
   print '<input type=submit value="Post">';
  print '</form>';

  $u = FindUserById($_SESSION['userid']);
  if(@$u['isadmin'])
  {
    print '<form method=post action="?op=register">';
     print '<legend>ADMIN-ONLY: Create new user:</legend>';
     print '<label for=u>Username:</label> <input type=text     id=u name=u><br>';
     print '<label for=p>Password:</label> <input type=password id=p name=p><br>';
     print '<input type=submit value="Create user">';
    print '</form>';
  }
  
  print '<hr>';
  print '<form method=post action="?op=logout">';
   print '<input type=submit value="Log out">';
  print '</form>';
}
print '<form method=post action="?op=del" style="visibility:hidden" id=delf>';
print '<input type=hidden name=uid id=uid value=0>';
print '<input type=hidden name=id id=delid value=0></form>';

ob_end_flush();
