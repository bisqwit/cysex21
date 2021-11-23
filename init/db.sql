drop table settings;
drop table users;
drop table posts;
create table settings
(
  id         integer primary key autoincrement,
  name       varchar(16)  not null unique,
  value      varchar(255) not null
);
create table users
(
  id         integer primary key autoincrement,
  name       varchar(16)  not null unique,
  password   varchar(255) not null,
  isadmin    tinyint(1)
);
create table posts
(
  id         integer primary key autoincrement,
  userid     integer not null references users(id),
  posttime   datetime not null,
  title      text not null,
  content    text not null
);

create index n on users(name);
create index u on posts(userid);
create index p on posts(posttime);
create index t on posts(title);
create index s on settings(name);
