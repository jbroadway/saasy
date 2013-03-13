create table #prefix#saasy_org (
	id int not null auto_increment primary key,
	name char(72) not null,
	subdomain char(72) not null,
	level int not null,
	unique (subdomain),
	index (level)
);

create table #prefix#saasy_acct (
	id int not null auto_increment primary key,
	user int not null,
	org int not null,
	type char(12) not null,
	enabled int not null,
	unique (user, org),
	index (org)
);
