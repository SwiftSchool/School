<?php

/**
 * Notification controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Notification extends Teacher {
	/**
	 * Teacher can publish notification for the assignment which will be sent
	 * to all the students of the classroom
	 * @before _secure, _teacher
	 */
	public function assignment($assignment_id) {
		$redirect = $_SERVER['HTTP_REFERER'];
		$assignment = Assignment::first(array("id = ?" => $assignment_id));
		if (!$assignment) {
			self::redirect($redirect);
		}
		$this->JSONView();
		$view = $this->getActionView();

		$notifications = Registry::get("MongoDB")->notifications;
		$template = new Framework\View(array(
            "file" => APP_PATH . "/application/views/notification/templates/newAssignment.html"
        ));
        $template->set("title", $assignment->title);
        $content = $template->render();

        $classroom = Classroom::first(array("id = ?" => $assignment->classroom_id));
        $users = $this->_findEnrollments($classroom, array('only_user' => true));

        $this->_save(array('type' => 'assignment', 'type_id' => $assignment->id, 'url' => '/student/assignments/'.$assignment->course_id), $content, $users);
	}

	/**
	 * @before _secure, _student
	 */
	public function submission() {

	}

	/**
	 * @before _secure, _school
	 */
	public function examResult() {

	}

	/**
	 * @before _secure, _school
	 */
	public function fee() {

	}

	/**
	 * Fetch notifications for the APP
	 * @before _secure
	 */
	public function fetch() {
		$this->JSONView();
		$view = $this->getActionView();

		$notifications = Registry::get("MongoDB")->notifications;
		$records = $notifications->find(array('recipient' => 'user', 'recipient_id' => (int) $this->user->id, 'live' => false));

		$results = array();
		foreach ($records as $r) {
			$results[] = array(
				"id" => $r['_id']->{'$id'},
				"content" => $r['template'],
				"url" => $r['url'],
				"created" => date('Y-m-d H:i:s', $r['created']->sec),
				"read" => $r['live'],
				"type" => $r['type'],
				"type_id" => $r['type_id']
			);
		}
		$view->set("notifications", count($results) == 0 ? ArrayMethods::toObject(array()) : $results);

	}

	protected function _save($type, $content, $users) {
		$notifications = Registry::get("MongoDB")->notifications;
        $doc = array(
        	"type" => $type['type'],
        	"type_id" => (int) $type['type_id'],
        	"sender" => "user",
        	"sender_id" => (int) $this->user->id,
        	"template" => $content,
        	"live" => false,
        	"url" => $type['url'],
        	"created" => new MongoDate()
        );

        foreach ($users as $u) {
        	$receiver = array(
        		"recipient" => "user",
        		"recipient_id" => (int) $u->user_id
        	);
        	$document = array_merge($doc, $receiver);

        	$where = array('sender_id' => (int) $this->user->id, 'recipient_id' => (int) $u->user_id, 'type' => $type['type'], 'type_id' => (int) $type['id']);
        	$record = $notifications->findOne($where);
        	if (!isset($record)) {
        		$notifications->insert($document);
        	}
        	$notifications->update($where, array('$set' => $document));
        }

        Registry::get("session")->set('$redirectMessage', "Notification sent to students");
        self::redirect($_SERVER['HTTP_REFERER']);
	}
}