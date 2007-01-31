<?php defined('CORENINPAGE') or die('Hack!');
//??? просто комментарий и мысли:

// ≈ще надо сделать заброс комментариев (notes) из тех же артиклов в форум в качестве ответов на топики.
// »ли все же сделать разными модул€ми? articles2forum & notes2forum. „то гораздо правильнее по концепции, но
// не очень - по реализации (придетс€ копировать код и использовать общую таблицу в двух модул€х).

// ј еще нужно проверить можно ли по грантам совать эту новость/статью/событие в форум,
// основыва€сь на категории свежедобавленной записи. ќднако же, хоть мы и можем
// проконтроллировать текущего посетител€ на доступ к записи, но мы никак не можем
// проконтроллировать всех посетителей форума, куда мы отправить запись. ¬ообще не можем, даже по идее.
// » как тут обойтись - неизвестно. ѕровер€ть, наверное, что категори€ записи должна быть общей, иначе
// просто не отправл€ть в форум.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class event_articles2forum_0 extends module implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $source_module;
protected $target_module;
protected $target_forum;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->source_module = core::find_scalar(array($configs), array('source_module'), null);
	$this->target_module = core::find_scalar(array($configs), array('target_module'), null);
	if (!isset($this->source_module)) throw new exception('misconfig: source_module');
	if (!isset($this->target_module)) throw new exception('misconfig: target_module');

	$this->target_forum = core::find_scalar(array($configs), array('target_forum'), null);
	if (!isset($this->target_forum)) throw new exception('misconfig: target_forum');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function core_event ($args)
{
	if (($args['executor'] == 'articles_0') && ($args['module'] == $this->source_module))
	{
		$evid = $args['identifier'];
		$data   = $args['data'  ];
		$entity = $data['entity'];
		$action = $data['action'];

		if (($evid == 'exec_item_success') && ($entity == 'article') && ($action == 'append'))
		{
			$newitemid = null;
			$errors = core::call($this->target_module, 'exec_item', array(
				'entity' => 'topic' ,
				'action' => 'append',
				'filter' => array('forum' => $data['itemnew']['published'] ? $this->target_forum : 12345),
				'submit' => array(
					'forum'		=> $data['itemnew']['published'] ? $this->target_forum : 12345,
					'name'		=> $data['itemnew']['headline'],
					'comment'	=> '',
					'.text'		=> strip_tags($data['itemnew']['fulltext'] != '' ? $data['itemnew']['fulltext'] : $data['itemnew']['announce'])),
				'newitemid' => &$newitemid));
			core::db('sync_topic_article', array('topic'=>$newitemid, 'article'=>$data['itemid']));
		} else
		if (($evid == 'exec_item_success') && ($entity == 'article') && ($action == 'modify'))
		{
			$topicid = core::db('find_topic_by_article', array('article'=>$data['itemid']));
			if (is_null($topicid))
			{
			$newitemid = null;
			$errors = core::call($this->target_module, 'exec_item', array(
				'entity' => 'topic' ,
				'action' => 'append',
				'filter' => array('forum' => $data['itemnew']['published'] ? $this->target_forum : 12345),
				'submit' => array(
					'forum'		=> $data['itemnew']['published'] ? $this->target_forum : 12345,
					'name'		=> $data['itemnew']['headline'],
					'comment'	=> '',
					'.text'		=> strip_tags($data['itemnew']['fulltext'] != '' ? $data['itemnew']['fulltext'] : $data['itemnew']['announce'])),
				'newitemid' => &$newitemid));
			core::db('sync_topic_article', array('topic'=>$newitemid, 'article'=>$data['itemid']));
			} else
			{
			$errors = core::call($this->target_module, 'exec_item', array(
				'entity' => 'topic' ,
				'action' => 'modify',
				'itemid' => $topicid,
				'filter' => array('forum' => $data['itemnew']['published'] ? $this->target_forum : 12345),
				'submit' => array(
					'forum'		=> $data['itemnew']['published'] ? $this->target_forum : 12345,
					'name'		=> $data['itemnew']['headline'],
					'comment'	=> '',
					'.text'		=> strip_tags($data['itemnew']['fulltext'] != '' ? $data['itemnew']['fulltext'] : $data['itemnew']['announce']))));
			}
		} else
		if (($evid == 'exec_item_success') && ($entity == 'article') && ($action == 'remove'))
		{
			$topicid = core::db('find_topic_by_article', array('article'=>$data['itemid']));
			if (is_null($topicid)) return;

			$errors = core::call($this->target_module, 'exec_item', array(
				'entity' => 'topic' ,
				'action' => 'remove',
				'itemid' => $topicid));
			core::db('desync_topic_article', array('topic'=>$topicid, 'article'=>$data['itemid']));
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>