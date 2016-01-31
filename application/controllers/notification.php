<?php

/**
 * Notification controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

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

        // notification doc
        $doc = array(
        	"type" => "assignment",
        	"type_id" => (int) $assignment->id,
        	"sender" => "user",
        	"sender_id" => (int) $this->user->id,
        	"template" => $content,
        	"live" => false
        );

        foreach ($users as $u) {
        	$receiver = array(
        		"recipient" => "user",
        		"recipient_id" => (int) $u->user_id
        	);
        	$document = array_merge($doc, $receiver);

        	$where = array('sender_id' => (int) $this->user->id, 'recipient_id' => (int) $u->user_id, 'type' => 'assignment', 'type_id' => (int) $assignment->id);
        	$record = $notifications->findOne($where);
        	if (!isset($record)) {
        		$notifications->insert($document);
        	}
        	$notifications->update($where, array('$set' => $document));
        }

        Registry::get("session")->set('$redirectMessage', "Notification sent to students");
        self::redirect($redirect);
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
}