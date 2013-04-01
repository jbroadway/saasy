create table #prefix#saasy_customer (
	id integer primary key,
	name char(72) not null,
	subdomain char(72) not null,
	level int not null
);

create unique index #prefix#saasy_customer_subdomain on #prefix#saasy_customer (subdomain);
create index #prefix#saasy_customer_level on #prefix#saasy_customer (level);

create table #prefix#saasy_acct (
	id integer primary key,
	user int not null,
	customer int not null,
	type char(12) not null,
	enabled int not null
);

create unique index #prefix#saasy_acct_user on #prefix#saasy_acct (user, customer);
create index #prefix#saasy_acct_customer on #prefix#saasy_acct (customer);
