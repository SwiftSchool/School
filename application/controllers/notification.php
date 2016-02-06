<?php

/**
 * Notification controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;
use Shared\Services\Teacher as TeacherService;

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

		$template = new Framework\View(array(
            "file" => APP_PATH . "/application/views/notification/templates/newAssignment.html"
        ));
        $template->set("title", $assignment->title);
        $content = $template->render();

        $classroom = TeacherService::$_classes[$assignment->classroom_id];
        $service = new Shared\Services\Classroom();
        $users = $service->enrollments($classroom, array('only_user' => true));

        $this->_save(array('type' => 'assignment', 'type_id' => $assignment->id, 'url' => '/student/assignments/'.$assignment->course_id), $content, $users);

        Registry::get("session")->set('$redirectMessage', "Notification sent to students");
        self::redirect($_SERVER['HTTP_REFERER']);
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
	 * Sends a notification to all the students studying the course
	 * @param int $course_id Id of the course teacher is teaching
	 * @param int $classroom_id Id of the classroom in which the teacher is teaching
	 */
	public function students($course_id, $classroom_id) {
		$this->JSONView();
		$view = $this->getActionView();

		$found = false;
		$teaches = TeacherService::$_teaches;
		foreach ($teaches as $t) {
			if ($t->course_id == $course_id && $t->classroom_id == $classroom_id) {
				$found = true;
			}
		}
		if (!$found) {
			self::redirect("/404");
		}

		if (RequestMethods::post("action") == "notifyStudents") {
			$classroom = TeacherService::$_classes[$classroom_id];
	        $service = new Shared\Services\Classroom();
	        $users = $service->enrollments($classroom, array('only_user' => true));

	        $content = RequestMethods::post("message");
	        $this->_save(array('type' => 'course', 'type_id' => $course_id, 'url' => ''), $content, $users);

	        $view->set("message", "Notification sent to students!!");
		} else {
			$view->set("message", "Invalid Request! Something went wrong");
		}	
	}

	/**
	 * Fetch notifications for the APP
	 * @before _secure
	 */
	public function fetch() {
		$this->JSONView();
		$view = $this->getActionView();

		$notifications = Registry::get("MongoDB")->notifications;
		$records = $notifications->find(array('recipient' => 'user', 'recipient_id' => (int) $this->user->id));
		$records->sort(array('created' => -1, 'live' => 1));

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

	/**
	 * Updating the notification status
	 * @before _secure
	 */
	public function update($notification_id) {
		$this->JSONView();
		$view = $this->getActionView();

		$notifications = Registry::get("MongoDB")->notifications;
		$mongo_id = new \MongoId($notification_id);
		
		$record = $notifications->findOne(array('_id' => $mongo_id));
		if (!isset($record) || $record['recipient'] != 'user' || $record['recipient_id'] != (int) $this->user->id) {
			self::redirect("/404");
		}

		$status = RequestMethods::post("read", true);
		if ($status === 'true') {
			$status = true;
		} elseif ($status === 'false') {
			$status = false;
		}
		$notifications->update(array('_id' => $mongo_id), array('$set' => array('live' => $status)));
		$view->set("success", "Notification status updated!!");
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
	}
}