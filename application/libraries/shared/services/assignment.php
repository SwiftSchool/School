<?php

/**
 * Assignment Service
 *
 * @author Hemant Mann
 */
namespace Shared\Services;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;
use Framework\RequestMethods as RequestMethods;

class Assignment extends \Auth {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->noview();
    }

	public function all($assignments, $courses) {
		$user = Registry::get("session")->get("user");
        $sub = Registry::get("MongoDB")->submission;
        $submissions = $sub->find(array("user_id" => (int) $user));

        $result = array();
        foreach ($assignments as $a) {
        	$course = $courses[$a->course_id];
            $submit = $this->_submission($submissions, $a);
            
            $data = array(
                "title" => $a->title,
                "description" => $a->description,
                "deadline" => $a->deadline,
                "id" => $a->id,
                "course" => $course->title,
                "submitted" => $submit["submission"],
                "filename" => ($a->attachment) ? $a->attachment : null,
                "marks" => $submit["grade"],
                "remarks" => $submit["remarks"],
                "status" => $submit["status"]
            );
            $data = ArrayMethods::toObject($data);
            $result[] = $data;
        }
        return $result;
	}

    public function total($classroom, $courses) {
        $user = Registry::get("session")->get("user");
        $sub = Registry::get("MongoDB")->submission;
        $assignments = \Assignment::count(array("classroom_id = ?" => $classroom->id));
        $submissions = $sub->find(array("user_id" => (int) $user));

        $return = array();
        $return['total'] = (int) $assignments;
        $return['submitted'] = 0;
        foreach ($submissions as $s) {
            if (array_key_exists($s['course_id'], $courses)) {
                $return['submitted']++;
            }
        }
        return $return;
    }

    public function submit($assignment) {
        $user = Registry::get("session")->get("user");
        $sub = Registry::get("MongoDB")->submission;
        $maxSize = "6291456";
        $return = array();

        $return["maxSize"] = $maxSize;
        $return["assignment"] = $assignment;

        $allowed = strtotime($assignment->deadline);
        $today = date('Y-m-d');
        if ($today > $allowed) {
            $return["error"] = "Last Date of submission is over";
            return $return;
        }

        $where = array("user_id" => (int) $user, "assignment_id" => (int) $assignment->id);
        $submission = $sub->findOne($where);
        if ($submission) {
            $return["success"] = "Assignment already submitted! Your response will be updated";
        }

        if (RequestMethods::post("action") == "submitAssignment") {
            if (RequestMethods::post("maxSize") != $maxSize) {
                $return["success"] = "Invalid Response";
                return $return;
            }

            $response = $this->_upload("response", array("type" => "assignments", "mimes" => "png|jpe?g|bmp|gif"));
            if (!$response) {
                $return["success"] = "File Upload failed!";
                return $return;
            }
            if (!$submission) {
                $sub->insert(array(
                    "user_id" => (int) $user,
                    "assignment_id" => (int) $assignment->id,
                    "course_id" => (int) $assignment->course_id,
                    "response" => $response,
                    "grade" => null,
                    "remarks" => null,
                    "modified" => new \MongoDate(),
                    "created" => new \MongoDate(),
                    "live" => true
                ));
            } else {
                $sub->update($where, array('$set' => array('response' => $response)));
                unlink(APP_PATH ."/public/assets/uploads/assignments/". $submission['response']);
            }

            $return["success"] = "You have successfully submitted the assignment!";
        }
        return $return;
    }

	protected function _submission($submissions, $a) {
        $submit = array("submission" => false, "file" => null, "status" => null, "remarks" => null, "grade" => null);
        
        foreach ($submissions as $s) {
            if ($s['assignment_id'] == $a->id) {
                $submit["file"] = $s['response'];
                $submit["submission"] = true;
                $submit["status"] = $s['live'] ? "Accepted" : "Rejected";
                $submit["remarks"] = $s['remarks'];
                $submit["grade"] = $s['grade'];
                break;
            }
        }
        return $submit;
    }
}
