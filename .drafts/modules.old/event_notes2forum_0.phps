<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class event_notes2forum_0 extends module implements dbaware
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
	if (($args['executor'] == 'notes_0') && ($args['module'] == $this->source_module))
	{
		$evid = $args['identifier'];
		$data   = $args['data'  ];
		$entity = $data['entity'];
		$action = $data['action'];

		if (($evid == 'exec_item_success') && ($entity == 'note') && (($action == 'append') || ($action == 'post')))
		{
			$article = $data['itemnew']['parent'];
			$topic = core::db('find_topic_by_article', array('article'=>$article));
			if (is_null($topic)) return;

			$newitemid = null;
			$errors = core::call($this->target_module, 'exec_item', array(
				'entity' => 'message' ,
				'action' => 'append',
//???				'filter' => array('topic' => $topic),
				'submit' => array(
					'topic'		=> $topic,
					'text'		=> $data['itemnew']['text']),
				'newitemid' => &$newitemid));
			core::db('sync_message_note', array('message'=>$newitemid, 'note'=>$data['itemid']));
		} else
		if (($evid == 'exec_item_success') && ($entity == 'note') && ($action == 'modify'))
		{
			$article = $data['itemnew']['parent'];
			$topic = core::db('find_topic_by_article', array('article'=>$article));
			if (is_null($topic)) return;

			$message = core::db('find_message_by_note', array('note'=>$data['itemid']));
			if (is_null($message)) return;

			$errors = core::call($this->target_module, 'exec_item', array(
				'entity' => 'message' ,
				'action' => 'modify',
				'itemid' => $message,
//???				'filter' => array('forum' => $data['itemnew']['published'] ? $this->target_forum : 12345),
				'submit' => array(
					'topic'		=> $topic,
					'text'		=> $data['itemnew']['text'])));
		} else
		if (($evid == 'exec_item_success') && ($entity == 'note') && ($action == 'remove'))
		{
			//!!!fix
			// удаление пока работает лишь частично, когда мессага удаляется индивидуально.
			// при массовом удалении не работает или что-то там не туда клеится.
			$message = core::db('find_message_by_note', array('note'=>$data['itemid']));
			if (is_null($message)) return;

			$errors = core::call($this->target_module, 'exec_item', array(
				'entity' => 'message' ,
				'action' => 'remove',
				'itemid' => $message));
			core::db('desync_message_note', array('message'=>$message, 'note'=>$data['itemid']));
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>