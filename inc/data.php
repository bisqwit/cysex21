<?php

define('DEFAULT_PASSWORD', 'admin');
require_once 'inc/db.php';

function UserCount()
{
  return dbquery('select count(*)from users')->fetchArray()[0];
}
function FindUser($u,$p)
{
  return dbquery('select * from users where name=? and password=?', [$u,$p])->fetchArray();
}
function FindUserById($u)
{
  return dbquery("select * from users where id=?", $u)->fetchArray();
}
function FindUserByName($u)
{
  return dbquery("select * from users where name=?", $u)->fetchArray();
}
function FindUsers()
{
  $q = dbquery('select * from users order by name');
  $s = $q->fetchArray();
  $r = Array();
  while($s !== false)
  {
    $r[$s['id']] = $s;
    $s = $q->fetchArray();
  }
  return $r;
}
function AddUser($u,$p, $admin)
{
  @dbquery('insert into users(name,password,isadmin)values(?,?,?)', [$u,$p, (int)$admin]);
  return FindUser($u,$p);
}
function FindPosts($uid)
{
  $q = dbquery('select * from posts where userid=? order by posttime desc', $uid);
  $s = $q->fetchArray();
  $r = Array();
  while($s !== false)
  {
    $r[$s['id']] = $s;
    $s = $q->fetchArray();
  }
  return $r;
}
function FindPost($id)
{
  return dbquery('select * from posts where id=?', $id)->fetchArray();
}
function DeletePost($id)
{
  @dbquery('delete from posts where id=?', $id);
}
function CreatePost($userid, $title, $content)
{
  dbquery('begin');
  $q = dbquery('insert into posts(userid,posttime,title,content)values(?,?,?,?)',
               [$userid, date('Y-m-d H:i:s'), $title, $content]
              );
  $id = dbquery('select max(id)from posts')->fetchArray()[0];
  dbquery('commit');
  return $id;
}





/* Create default settings for database if none exist. */
dbquery('begin');
if(dbquery('select count(*)from settings')->fetchArray()[0] == 0)
  dbquery('insert into settings(id,name,value)values(1,?,?)', ['salt', random_bytes(16)]);

if(!UserCount())
{
  AddUser('admin', DEFAULT_PASSWORD, true);
}

@dbquery('commit');
