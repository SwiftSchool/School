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
			->set("messages", $messages);
	}

	/**
	 * @before _secure, _teacher
	 */
	public function message($conversation_id) {

	}

	/**
	 * Students|Parents can only reply to the conversation
	 * @before _secure
	 */
	public function reply($conversation_id) {

	}

	protected function _reply($conversation) {

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
			$identifier = RequestMethods::post("username");

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

	protected function _message($conversation) {
		if (RequestMethods::post("action") == "sendMessage") {
			
		}
	}

	/**
	 * Check if the user is a part of conversation
	 */
	protected function _validUser($conversation_id) {
		$mongo = Registry::get("MongoDB");
		$conv_users = $mongo->conv_users;

		$usr = $conv_users->findOne(array('conversation_id' => $conversation_id, 'user_id' => (int) $this->user->id, 'live' => true));
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
		$users = array();

		// can query the db to find user - names etc
		foreach ($records as $r) {
			$data = $r;
			$data['created'] = date('Y-m-d H:i:s', $r['created']->sec);
			$users[] = $data;
		}
		return $users;
	}

	/**
	 * Finds the messages of given conversation
	 */
	protected function _findMessages($conversation_id) {
		$mongo = Registry::get("MongoDB");
		$m = $mongo->messages;

		$usr = $this->_validUser($conversation_id);
		$messages = $m->find(array('conversation_id' => $conversation_id, 'created' => array('$gte' => $usr['created'])), array('_id' => false, 'conversation_id' => false));
		
		$msg = array();
		foreach ($messages as $m) {
			$data = $m;
			$data['created'] = date('Y-m-d H:i:s', $m['created']->sec);
			$msg[] = $data;
		}
		return $msg;
	}

}
