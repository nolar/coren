/*******************************************************************************************************************************
 * 
 ******************************************************************************************************************************/

set names latin1;

drop table if exists `coren_module`;
create table `coren_module` (
	`module`		varchar(255)		not null ,/* contraint: != '' ??? */
	`implementer`		varchar(255)		not null ,
	`database`		varchar(255)		    null references `coren_module` (`module`),
	`package`		integer unsigned	    null ,
	`disabled`		tinyint			not null ,
	primary key		(`module`))
	engine innodb character set ucs2;

drop table if exists `coren_config`;
create table `coren_config` (
	`module`		varchar(255)		not null references `coren_module`,
	`config`		varchar(255)		not null ,
	`value`			longtext		not null ,
	primary key		(`module`, `config`))
	engine innodb character set ucs2;

drop table if exists `coren_handler`;
create table `coren_handler` (
	`handler`		integer unsigned	not null auto_increment,
	`order`			integer			not null ,
	`module`		varchar(255)		not null ,
	`method`		varchar(255)		not null ,
	`event`			varchar(255)		not null ,
	`map`			longtext		    null ,
	index			(`order`, `handler`),
	primary key		(`handler`))
	engine innodb character set ucs2;

drop table if exists `coren_parameter`;
create table `coren_parameter` (
	`parameter`		varchar(255)		not null ,
	`value`			longtext		not null ,
	primary key		(`parameter`))
	engine innodb character set ucs2;

drop table if exists `coren_package`;
create table `coren_package` (
	`package`		integer unsigned	not null auto_increment,
	`install_stamp`		datetime		not null ,
	`install_path`		longtext		not null ,
	`infofile`		mediumtext		not null ,
	primary key		(`package`))
	engine innodb character set ucs2;

drop table if exists `coren_file`;
create table `coren_file` (
	`file`			integer unsigned	not null auto_increment,
	`name`			mediumtext		not null ,
	`size`			bigint			not null ,
	`md5`			mediumtext		not null ,
	`package`		integer unsigned	    null ,
	`purpose`		tinyint			    null ,
	primary key		(`file`))
	engine innodb character set ucs2;



delete from `coren_module`;
insert into `coren_module` (`module`, `implementer`, `database`) values
	('php_timezone'			, 'php_timezone_0'					, default		),
	('php_locale'			, 'php_locale_0'					, default		),

	('freshness_manager'		, 'http_cache_control_0'				, default		),
	('autocontent'			, 'event_by_http_path_regex_0'				, default		),
	('autocontent404'		, 'event_unconditional_0'				, default		),
	('error404'			, 'http_status_code_0'					, default		),
/*	('/enter/'			, 'call_by_path_regex_0'				, default		),
	('/leave/'			, 'call_by_path_regex_0'				, default		),*/
	('xslt_from_files'		, 'xslt_filesystem_hierarchy_0'				, default		),
	('cache1'			, 'cache_directory_serialized_0'			, default		),
	('cache2'			, 'cache_memory_0'					, default		),
	('message_flusher'		, 'message_queue_flush_0'				, default		),
	('message_injecter'		, 'message_queue_inject_0'				, default		),
	('message_mailer'		, 'message_mail_php_0'					, default		),
	('cookie(sesss)'		, 'http_cookie_0'					, default		),

	('account_information'		, 'account_information_0'				, default		),
	('account_credentials'		, 'account_credentials_0'				, default		),
	('session_credo'		, 'requisites_request_direct_0'				, default		),
	('session_context'		, 'requisites_request_direct_0'				, default		),
	('session_genid'		, 'generator_random_0'					, default		),

	('session_start'		, 'identify_session_start_0'				, default		),
	('session_close'		, 'identify_session_close_0'				, default		),
	('session_detect'		, 'identify_session_detect_0'				, default		),
	('account_detect'		, 'identify_account_detect_0'				, default		),

	('account_privileges'		, 'identify_account_privileges_0'			, default		),
	('account_touch'		, 'identify_account_touch_0'				, default		),
	('session_expire'		, 'identify_session_expire_0'				, default		),
	('session_touch'		, 'identify_session_touch_0'				, default		),
	('session_get'			, 'requisites_request_mapped_0'				, default		);



delete from `coren_config`;
insert into `coren_config` (`module`, `config`, `value`) values
	('php_timezone'			, 'required'				, '1'					),
	('php_timezone'			, 'timezone'				, 'Asia/Krasnoyarsk'			),
	('php_locale'			, 'required'				, '1'					),
	('php_locale'			, 'category'				, 'all'					),
	('php_locale'			, 'locales'				, 'C'					),

	('xslt_from_files'		, 'dir_path'				, '.xslt'				),
	('xslt_from_files'		, 'dir_absolute'			, '0'					),
	('xslt_from_files'		, 'dir_required'			, '1'					),
	('xslt_from_files'		, 'dir_automake'			, '0'					),
	('autocontent'			, 'stop_slot'				, 'main'				),
	('autocontent'			, 'event_mask'				, 'content-/%s/'			),
	('autocontent'			, 'regex'				, '|^/(.+?)(/?)$|six'			),
	('autocontent'			, 'index'				, '1'					),
	('autocontent404'		, 'stop_slot'				, 'main'				),
	('autocontent404'		, 'event'				, 'content404'				),
	('error404'			, 'code'				, '404'					),
	('error404'			, 'text'				, 'Not found'				),
/*	('/enter/'			, 'regex'				, '|^/enter/$|six'				),
	('/enter/'			, 'event'				, '/enter/'				),
	('/enter/'			, 'slot'				, 'main'				),
	('/leave/'			, 'regex'				, '|^/leave/$|six'			),
	('/leave/'			, 'event'				, '/leave/'				),
	('/leave/'			, 'slot'				, 'main'				),*/

	('cache1'			, 'dir_path'				, 'ABC'					),
	('cache1'			, 'dir_absolute'			, '0'					),
	('cache1'			, 'dir_automake'			, '1'					),
	('cache1'			, 'dir_required'			, '1'					),
	('cache1'			, 'lock_needed'				, '1'					),
	('message_flusher'		, 'prefix'				, 'mq'					),
	('message_flusher'		, 'limit_per_call'			, '3'					),
	('message_injecter'		, 'prefix'				, 'mi'					),
	('message_mailer'		, 'prefix'				, 'ms'					),
	('cookie(sesss)'		, 'cookie_name'				, 'sesss'				),
	('cookie(sesss)'		, 'autolock'				, '1'					),

	('account_information'		, 'module_for_cache'			, 'cache1'				),
	('session_start'		, 'name_for_session_start_failed'	, 'identify-session-start-failure'	),

	('session_genid'		, 'minlength'				, '3'					),
	('session_genid'		, 'maxlength'				, '3'					),
	('session_genid'		, 'alphabet'				, 'abcdef'				),
	('session_genid'		, 'format'				, '[%s]'				),
	('session_start'		, 'trycount'				, '1'					),
	('session_credo'		, 'order'				, 'GPC'					),
	('session_context'		, 'order'				, 'G'					),

	('session_get'			, 'order'				, 'C'					),
	('session_get'			, 'map'					, 'identifier=sesss'			);



delete from `coren_handler`;
insert into `coren_handler` (`module`, `method`, `event`, `order`, `map`) values
	('php_timezone'			, 'set'			, 'coren!stage(init)'				,0, null),
	('php_locale'			, 'set'			, 'coren!stage(init)'				,0, null),

	('freshness_manager'		, 'cache_level_public'	, 'http.cache:level.public'			,0, null),
	('freshness_manager'		, 'cache_level_private'	, 'http.cache:level.private'			,0, null),
	('freshness_manager'		, 'cache_level_dynamic'	, 'http.cache:level.dynamic'			,0, null),
	('freshness_manager'		, 'cache_level_paranoid', 'http.cache:level.paranoid'			,0, null),
	('freshness_manager'		, 'cache_stamp_modified', 'http.cache:stamp.modified'			,0, null),
	('freshness_manager'		, 'cache_stamp_expires'	, 'http.cache:stamp.expires'			,0, null),
	('freshness_manager'		, 'cache_vary_header'	, 'http.cache:vary.header'			,0, null),
	('freshness_manager'		, 'send'		, 'coren!stage(epiwork)'			,0, null),
	('autocontent'			, 'check'		, 'coren!stage(content)'			,0, null),
	('autocontent404'		, 'check'		, 'coren!stage(content)'			,9, null),
	('error404'			, 'send'		, 'content404'					,0, null),
/*	('/enter/'			, 'work'		, 'coren!stage(content)'			,0, null),
	('/leave/'			, 'work'		, 'coren!stage(content)'			,0, null),*/
/*???	('xslt_from_files'		, 'on_new_data'		, 'coren:new-data-node'				,0, null),*/
	('cache1'			, 'get_item'		, 'account.cache.get'				,0, null),
	('cache1'			, 'set_item'		, 'account.cache.set'				,0, null),
	('message_injecter'		, 'inject'		, 'message.queue.inject'			,0, null),
	('message_mailer'		, 'send'		, 'message.queue.flush!send'			,0, null),
	('message_flusher'		, 'flush'		, 'content-/flush/'				,0, null),
	('xslt_from_files'		, 'on_populate_xslt'	, 'coren!stage(epiwork)'			,0, null),

	('account_information'		, 'information'		, 'identify.account.detect?information'		,0, null),
	('account_credentials'		, 'verify'		, 'identify.session.start?acknowledge'		,0, null),
	('session_genid'		, 'make_format'		, 'identify.session.start?identifier'		,0, null),
	('session_credo'		, 'retrieve'		, 'identify.session.start?credentials'		,0, null),
	('session_context'		, 'retrieve'		, 'identify.session.start?context'		,0, null),
	('session_context'		, 'retrieve'		, 'identify.session.close?context'		,0, null),
	('session_detect'		, 'get_identifier'	, 'identify.session.close?identifier'		,0, null),

	('session_expire'		, 'expire'		, 'coren!stage(prework)'			,0, null),
	('account_detect'		, 'identify'		, 'coren!stage(prework)'			,0, null),
	('session_detect'		, 'detect'		, 'identify.account.detect?identifier'		,0, null),
/*	('sslcert_detect'		, 'detect'		, 'identify.account.detect?identifier'		,0, null),*/
	('account_privileges'		, 'assign'		, 'identify.account.detect!successed'		,0, null),
/*	('account_touch'		, 'touch'		, 'identify.account.detect!successed'		,0, null),*/
	('session_touch'		, 'touch'		, 'identify.session.detect!successed'		,0, null),

	('session_start'		, 'start'		, 'content-/enter/'				,0, null),
	('session_close'		, 'close'		, 'content-/leave/'				,0, null),

	('session_get'			, 'retrieve'		, 'identify.session.detect?identifier'		,0, null),
	('cookie(sesss)'		, 'set'			, 'identify.session.start!successed'		,1, 'value=identifier'),
	('session_close'		, 'close'		, 'identify.session.start!successed'		,2, null),
	('cookie(sesss)'		, 'rst'			, 'identify.session.close!successed'		,0, null),
	('cookie(sesss)'		, 'rst'			, 'identify.session.detect!failed'		,0, null),
	('cookie(sesss)'		, 'rst'			, 'identify.account.detect!failed'		,0, null);



delete from `coren_parameter`;
insert into `coren_parameter` (`parameter`, `value`) values
	('title'		, 'Sample Site N1'									),
	('admin_email'		, 'nolar2006@mail.ru'									);

/*******************************************************************************************************************************
 * FIN.
 ******************************************************************************************************************************/
