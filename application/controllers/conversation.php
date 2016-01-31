<?php

/**
 * Conversation controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Conversation extends Teacher {
	
	/**
	 * Teacher can create a new conversation
	 * @before _secure, _teacher
	 */
	public function create() {
		$this->JSONView();
		$view = $this->getActionView();

		$classrooms = $this->_findClassrooms();

		$students = $this->_findEnrollments($classrooms, array('conversation' => true));
		$view->set("students", $students);
        $this->_create();
	}

	/**
	 * Teacher can add a user to the conversation
	 * @before _secure, _teacher
	 */
	public function addUser($conversation_id) {
		// $this->_addUser($id);
	}

	/**
	 * View the messages of conversation
	 * @before _secure
	 */
	public function view($conversation_id = null) {
		if (!$conversation_id) {
			self::redirect("/404");
		} else {
			$conversation_id = new MongoId($conversation_id);
		}
		$this->JSONView();
		$view = $this->getActionView();

		$conversation = $this->_findConv($conversation_id);
		$users = $this->_findConvUsers($conversation_id);
		$messages = $this->_findMessages($conversation_id);

		$view->set("users", $users)
			->set("conversation", $conversation)
			->set("messages", count($messages) == 0 ? ArrayMethods::toObject(array()) : $messages);
	}

	/**
	 * @before _secure
	 */
	public function message($conversation_id) {
		if (!$conversation_id) {
			self::redirect("/404");
		} else {
			$conversation_id = new MongoId($conversation_id);
		}
		$this->JSONView();
		$view = $this->getActionView();

		$conversation = $this->_findConv($conversation_id);
		$return = $this->_reply($this->user, $conversation_id);
		if (isset($return['error'])) {
			$view->set('error', $return['error']);
			return;
		}

		if (isset($return['success'])) {
			$view->set('message', $return['message']);
		}
	}

	/**
	 * List all the conversations of the teacher
	 * @before _secure, _teacher
	 */
	public function all() {
		$this->JSONView();
		$view = $this->getActionView();

		$conv = Registry::get("MongoDB")->conversation;
		$records = $conv->find(array('user_id' => (int) $this->user->id, 'live' => true));

		$conversations = $this->_fmtRecords($records);
		$view->set("conversations", $conversations);
	}

	/**
	 * Finds the conversations for the student
	 * @before _secure
	 */
	public function find() {
		$this->JSONView();
		$view = $this->getActionView();

		$mongo = Registry::get("MongoDB");
		$conv_users = $mongo->conv_users;
		$conv = $mongo->conversation;
		$records = $conv_users->find(array('user_id' => (int) $this->user->id, 'live' => true));

		$conversations = array();
		foreach ($records as $r) {
			$c = $conv->findOne(array('_id' => $r['conversation_id']));
			$usr = User::first(array("id = ?" => $c['user_id']), array("name"));
			$conversations[] = array(
				"teacher" => $usr->name,
				"id" => $c['_id']->{'$id'}
			);
		}
		$view->set("conversations", $conversations);
	}

	protected function _reply($user, $conversation) {
		$m = Registry::get("MongoDB")->messages;
		if (RequestMethods::post("action") == "sendMessage") {
			$message = RequestMethods::post("message");
			if (empty($message)) {
				return array("error" => "Message is required");
			}
			$doc = array(
				"conversation_id" => $conversation,
				"created" => new MongoDate(),
				"user_id" => (int) $user->id,
				"content" => $message,
				"live" => true
			);
			$m->insert($doc);
			
			$msg = $doc;
			return array(
				'success' => true,
				'message' => $this->_fmtRecords(array($msg))
			);
		}
	}

	/**
	 * Teacher will have to first create a conversation to
	 * start messaging
	 */
	protected function _create() {
		$errors = array();

		$conv = Registry::get("MongoDB")->conversation;
		if (RequestMethods::post("action") == "newConv") {
			$title = RequestMethods::post("display");
			$identifier = RequestMethods::post("identifier");

			$record = $conv->findOne(array('identifier' => $identifier, 'user_id' => (int) $this->user->id));
			if (!isset($record)) {
				$id = $this->_newConv();
			} else {
				$id = $record['_id']->{'$id'};
			}

			self::redirect("/conversation/view/$id");
		}
	}

	/**
	 * Creates a new conversation b/w two persons
	 * @return string Conversation ID of the new conversation
	 */
	protected function _newConv() {
		$doc = array(
			'title' => RequestMethods::post("title"),
			'identifier' => RequestMethods::post("identifier"),
			'user_id' => (int) $this->user->id,
			'live' => true,
			'archived' => false,
			'created' => new MongoDate()
		);
		$conv->insert($doc);

		$id = $doc['_id']->{'$id'};

		$users = array(
			array(
				'user_id' => (int) $this->user->id,
				'admin' => true
			),
			array(
				'user_id' => (int) RequestMethods::post("user_id"),
				'admin' => false
			)
		);
		$this->_addUser($doc, array('internalReq' => true, 'users' => $users));
		return $id;
	}

	/**
	 * Adds a user to given conversation
	 */
	protected function _addUser($conversation, $opts = array()) {
		$conv_users = Registry::get("MongoDB")->conv_users;
		if (RequestMethods::post("action") == "addUser") {
			
		} elseif (isset($opts['internalReq'])) {
			foreach ($opts['users'] as $u) {
				$common = array(
					'conversation_id' => $conversation['_id'],
					'live' => true,
					'created' => new MongoDate()
				);
				$doc = array_merge($u, $common);
				$conv_users->insert($doc);
			}
		}
	}

	/**
	 * Check if the user is a part of conversation
	 */
	protected function _validUser($conversation_id, $user = false) {
		$mongo = Registry::get("MongoDB");
		$conv_users = $mongo->conv_users;

		$user = ($user) ? $user : $this->user;
		$usr = $conv_users->findOne(array('conversation_id' => $conversation_id, 'user_id' => (int) $user->id, 'live' => true));
		if (!isset($usr)) {
			self::redirect("/404");
		}
		return $usr;
	}

	/**
	 * Finds the conversation from id
	 * If no conversation found then redirect
	 */
	protected function _findConv($conversation_id) {
		$mongo = Registry::get("MongoDB");
		$conv = $mongo->conversation;
		$record = $conv->findOne(array('_id' => $conversation_id));
		if (!isset($record)) {
			self::redirect("/404");
		} else {
			$data = array(
				'id' => $record['_id']->{'$id'},
				'title' => $record['title'],
				'identifier' => $record['identifier'],
				'user_id' => $record['user_id'],
				'created' => date('Y-m-d H:i:s', $record['created']->sec)
			);
			$record = $data;
		}
		return $record;
	}

	/**
	 * Find the users involved in the given conversation
	 */
	protected function _findConvUsers($conversation_id) {
		$mongo = Registry::get("MongoDB");
		$conv_users = $mongo->conv_users;

		// find conversation participants
		$records = $conv_users->find(array('conversation_id' => $conversation_id), array('_id' => false, 'conversation_id' => false));

		return $this->_fmtRecords($records);
	}

	protected function _fmtRecords($records) {
		$rows = array();
		foreach ($records as $r) {
			$data = $r;
			if (isset($r['_id'])) {
				unset($data['_id']);
				$data['id'] = $r['_id']->{'$id'};
			}

			if (isset($r['conversation_id'])) {
				unset($data['conversation_id']);
				$data['conversation_id'] = $r['conversation_id']->{'$id'};
			}
			$data['created'] = date('Y-m-d H:i:s', $r['created']->sec);
			$rows[] = $data;
		}
		return $rows;
	}

	/**
	 * Finds the messages of given conversation
	 */
	protected function _findMessages($conversation_id, $opts = array()) {
		$mongo = Registry::get("MongoDB");
		$m = $mongo->messages;

		$usr = $this->_validUser($conversation_id);
		if (isset($opts['start']) && isset($opts['end'])) {
			$user_created = strtotime(date('Y-m-d', $usr['created']->sec));
			
			$start = new MongoDate(($opts['start'] < $user_created) ? $user_created : $opts['start']);
			$end = new MongoDate($opts['end']);
			$created = array('$gte' => $start, '$lte' => $end);
		} else {
			$start = $usr['created'];
			$created = array('$gte' => $usr['created']);
		}
		$messages = $m->find(array('conversation_id' => $conversation_id, 'created' => $created), array('_id' => false, 'conversation_id' => false));
		
		return $this->_fmtRecords($messages);
	}

}
