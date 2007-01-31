/*******************************************************************************************************************************
 * NB: Replace mail with real value!
 ******************************************************************************************************************************/

set names latin1;

drop table if exists `message_template`;
create table `message_template` (
	`template`		integer unsigned	not null auto_increment,
	`subject`		longtext		not null ,
	`message`		longtext		not null ,
	primary key		(`template`))
	engine innodb character set ucs2;

drop table if exists `message_envelope`;
create table `message_envelope` (
	`envelope`		integer unsigned	not null auto_increment,

	`template`		integer unsigned	not null references `message_template` (`template`),
	`recipient`		longtext		not null ,
	`priority`		tinyint signed		not null ,
	`status`		tinyint			not null ,/* 0 - for queued, +1 - for sent, -1 - for refused */
	`counter`		bigint unsigned		not null ,
	`injected`		datetime		not null ,

	`last_stamp`		datetime		    null ,
	`last_error`		longtext		    null ,
	`last_errno`		integer			    null ,

	index			(`status`),
	index			(`counter`, `priority`),
	primary key		(`envelope`))
	engine innodb character set ucs2;

/*******************************************************************************************************************************
 * FIN.
 ******************************************************************************************************************************/
