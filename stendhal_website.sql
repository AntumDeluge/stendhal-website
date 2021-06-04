create table screenshots (
  id int auto_increment not null,
  url varchar(256),
  description text,
  created timestamp default CURRENT_TIMESTAMP,
  approved boolean default false,

  primary key(id)
);

insert into screenshots values(null, "screenshots/screenshot.jpg", "Test image", null, null);

create table movies (
  id int auto_increment not null,
  url varchar(256),
  description text,
  created timestamp default CURRENT_TIMESTAMP,
  approved boolean default false,

  primary key(id)
);



create table news (
  id int auto_increment not null,
  title varchar(256),
  shortDescription varchar(256),
  extendedDescription text,
  detailedDescription text,
  active int default 1,
  news_type_id int,
  created timestamp default CURRENT_TIMESTAMP,
  updateCount int default 0,
  primary key(id)
);

create table news_type (
  id int auto_increment not null,
  title varchar(255),
  image_url varchar(255),
  active int default 1,

  primary key(id)
);

insert into news_type(title, image_url) values ('Administrative', '/images/events/eventAdmin.png');
insert into news_type(title, image_url) values ('Gift', '/images/events/eventGift.png');
insert into news_type(title, image_url) values ('Meeting', '/images/events/eventMeeting.png');
insert into news_type(title, image_url) values ('Quiz', '/images/events/eventQuiz.png');
insert into news_type(title, image_url) values ('Raid', '/images/events/eventRaid.png');
insert into news_type(title, image_url) values ('Release', '/images/events/eventRelease.png');
insert into news_type(title, image_url) values ('Other', '/images/events/eventOther.png');

insert into news (title, extendedDescription, detailedDescription, news_type_id ) values ( 'Welcome to the Stendhal Website', '<p>Congratulations, you got the Stendhal website up and running. The next steps are:<p><ol><li>Read and understand the <a href="COPYING.txt">AGPL</a>.</li><li> Do the necessary steps to comply with the license. Hint: You can add entries to the navigation menus in the file content/frame/stendhal.php.</li><li>Login with your Stendhal admin account, and edit this news entry to replace it with some real content.</li></ol><p>If you implemented a feature, fixed a bug, or just have nice ideas, please let us know on the Arianne <a href="http://sourceforge.net/tracker/?group_id=1111&atid=101111">trackers</a> or <a href="irc.libera.chat">chat</a>.</p><p>Have fun!</p>', '', 1);

create table news_images (
  id int auto_increment not null,
  news_id int not null,
  url varchar(256),
  description text,
  created timestamp default CURRENT_TIMESTAMP,

  primary key(id, news_id)
);

create table remind_password (
  username varchar(32) not null,
  confirmhash varchar(32) not null,

  requested timestamp default CURRENT_TIMESTAMP,

  primary key(username, confirmhash)
);


create table page (
  id int auto_increment not null,
  language CHAR(2),
  title VARCHAR(255),
  primary key(id)
);

create table page_version (
  id int auto_increment not null,
  page_id int not null,
  account_id int not null,
  content text,
  commitcomment VARCHAR(255),
  displaytitle VARCHAR(255),
  timedate timestamp default CURRENT_TIMESTAMP,
  primary key(id)
);


create table members (
  id int auto_increment not null,
  realname varchar(255),
  street varchar(255),
  city varchar(255),
  country varchar(255),
  email varchar(255),
  visiblename varchar(15),
  visibleemail varchar(15),
  player_id int not null,
  primary key(id)
);

create table permission (
  id int auto_increment not null,
  account_id int not null,
  edit_page CHAR(1) DEFAULT '1',
  view_history CHAR(1) DEFAULT '1',
  view_documents CHAR(1) DEFAULT '1',
  management CHAR(1) DEFAULT '0',
  primary key(id)
);


create table content_security_policy_report (
  id int auto_increment not null,
  address varchar(255),
  useragent varchar(255),
  content text,
  timedate timestamp default CURRENT_TIMESTAMP,
  primary key(id)
);

