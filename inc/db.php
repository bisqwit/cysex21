<?php

$db = new SQlite3('data/db.db', SQLITE3_OPEN_READWRITE);

/* A helper function for DB queries to provide a similar interface as Python. */
function dbquery($sql, $binds = Array())
{
  global $db;
  $q = $db->prepare($sql);
  if(is_array($binds))
    foreach($binds as $n=>$value) $q->bindValue(1 + $n, $value);
  else
    $q->bindValue(1, $binds);
  return $q->execute();
}
