create table #prefix#saasy_org (
	id integer primary key,
	name char(72) not null,
	subdomain char(72) not null,
	status int not null
);

create unique index #prefix#saasy_org_subdomain on #prefix#saasy_org (subdomain);
create index #prefix#saasy_org_status on #prefix#saasy_org (status);

create table #prefix#saasy_acct (
	id integer primary key,
	user int not null,
	org int not null,
	type char(12) not null,
	enabled int not null
);

create unique index #prefix#saasy_acct_user on #prefix#saasy_acct (user, org);
create index #prefix#saasy_acct_org on #prefix#saasy_acct (org);
