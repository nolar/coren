/*******************************************************************************************************************************
 * Authorization structures.
 ******************************************************************************************************************************/

set names latin1;

drop table if exists `auth_account`;
create table `auth_account` (
	`account`		integer unsigned	not null auto_increment,

	`created`		datetime		    null ,
	`entered`		datetime		    null ,
	`touched`		datetime		    null ,

	`disabled`		tinyint			not null ,
	`reason`		longtext		not null ,
	`comment`		longtext		not null ,

	`email`			longtext		    null ,/* currently active email address, or null if no activated address yet */
	`agreement`		longtext		    null ,

	`logname`		varchar(255)		not null ,
	`password_plain`	varchar(255)		not null ,
	`password_md5`		varchar(255)		not null ,
	/* !!! ssl certs possible here, and other credentials */

	unique			(`logname`),
	primary key		(`account`))
	engine innodb character set ucs2;

drop table if exists `auth_account_membership`;
create table `auth_account_membership` (
	`account`		integer unsigned	not null references `auth_account`,
	`rolegroup`		integer unsigned	not null references `auth_rolegroup`,
	index			(`account`),
	index			(`rolegroup`  ),
	primary key		(`account`, `rolegroup`))
	engine innodb character set ucs2;

drop table if exists `auth_account_information`;
create table `auth_account_information` (
	`account`		integer unsigned	not null references `auth_account`,
	`field`			varchar(255)		not null /*references `auth_value`???*/,
	`value`			longtext		    null ,
	index			(`account`),
	index			(`field`  ),
	primary key		(`account`, `field`))
	engine innodb character set ucs2;



drop table if exists `auth_ipblock`;
create table `auth_ipblock` (
	`ipblock`		integer unsigned	not null auto_increment,

	`disabled`		tinyint			not null ,
	`reason`		longtext		not null ,
	`comment`		longtext		not null ,

	`type`			tinyint			not null , /* 0 - suffix of hostname; 1 - ipv4 cidr/len, 2 - ipv4 cidr/mask */

	`type0_domain`		varchar(255)		    null ,
	`type0_mode`		tinyint			    null , /* 0 - self and any subdomains, 1 - subdomains only excl self, 2 - only this domain */

	`type1_addr`		integer unsigned	    null ,
	`type1_mask`		integer unsigned	    null ,

	`type2_addr`		integer unsigned	    null ,
	`type2_mask`		integer unsigned	    null ,

	primary key		(`ipblock`))
	engine innodb character set ucs2;

drop table if exists `auth_ipblock_membership`;
create table `auth_ipblock_membership` (
	`ipblock`		integer unsigned	not null references `auth_ipblock`,
	`group`			integer unsigned	not null references `auth_group`,
	index			(`ipblock`),
	index			(`group`  ),
	primary key		(`ipblock`, `group`))
	engine innodb character set ucs2;

/*******************************************************************************************************************************
 * Authorization. Role-groups and privileges (access rights).
 ******************************************************************************************************************************/

drop table if exists `auth_privilege`;
create table `auth_privilege` (
	`privilege`		integer unsigned	not null auto_increment,
	`codename`		varchar(255)		not null ,
	`comment`		longtext		    null ,
	unique			(`codename`),
	primary key		(`privilege`))
	engine innodb character set ucs2;

drop table if exists `auth_rolegroup`;
create table `auth_rolegroup` (
	`rolegroup`		integer unsigned	not null auto_increment,
	`codename`		varchar(255)		not null ,
	`comment`		longtext		    null ,
	unique			(`codename`),
	primary key		(`rolegroup`))
	engine innodb character set ucs2;

drop table if exists `auth_assignment`;
create table `auth_assignment` (
	`privilege`		integer unsigned	not null references `auth_privilege`,
	`rolegroup`		integer unsigned	not null references `auth_rolegroup`,
	primary key		(`privilege`, `rolegroup`))
	engine innodb character set ucs2;

/*******************************************************************************************************************************
 * Authorization. Sessions' maintenance.
 ******************************************************************************************************************************/

drop table if exists `auth_session`;
create table `auth_session` (
	`session`		varchar(255)		not null ,
	`account`		integer unsigned	not null references `auth_account`,
	`remote`		varchar(255)		not null ,
	`secure`		tinyint			not null ,/* bool */
	`period`		integer unsigned	not null ,/* number of seconds to live since last touch */
	`status`		tinyint			not null ,/* enum: 0 - still open, 1 - expired, 2 - user closed, 3 - admin closed */
	`started`		datetime		not null ,
	`touched`		datetime		not null ,
	`closed`		datetime		    null ,
	/*todo: add indexes for quick expireing */
	primary key		(`session`))
	engine myisam character set ucs2;

/*******************************************************************************************************************************
 * Initial tables' content.
 ******************************************************************************************************************************/

replace into `auth_privilege` (`privilege`, `codename`) values (1, 'administering');
replace into `auth_rolegroup` (`rolegroup`, `codename`) values (1, 'administrators');
replace into `auth_assignment` (`privilege`, `rolegroup`) values (1,1);

replace into `auth_account` (`account`, `logname`, `password_plain`, `email`) values (1, 'administrator', 'password', '');
replace into `auth_account_membership` (`account`, `rolegroup`) values (1,1);

replace into `auth_session` (`session`, `account`, `remote`, `started`, `touched`) values ('abc', 1, '127.0.0.1', now(), now());

/*******************************************************************************************************************************
 * FIN.
 ******************************************************************************************************************************/
