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

	public function all($assignments, $courses) {
		$this->noview();
		$user = Registry::get("session")->get("user");
        $submissions = \Submission::all(array("user_id = ?" => $user));

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
                "submission_id" => $submit["submission_id"],
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
        $this->noview();
        $user = Registry::get("session")->get("user");
        $assignments = \Assignment::count(array("classroom_id = ?" => $classroom->id));
        $submissions = \Submission::all(array("user_id = ?" => $user));

        $return = array();
        $return['total'] = (int) $assignments;
        $return['submitted'] = 0;
        foreach ($submissions as $s) {
            if (array_key_exists($s->course_id, $courses)) {
                $return['submitted']++;
            }
        }
        return $return;
    }

    public function submit($assignment) {
        $this->noview();
        $user = Registry::get("session")->get("user");
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

        $submission = \Submission::first(array("user_id = ?" => $user, "assignment_id = ?" => $assignment->id));
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
                $submission = new \Submission(array(
                    "user_id" => $user,
                    "assignment_id" => $assignment->id,
                    "course_id" => $assignment->course_id,
                    "grade" => null,
                    "remarks" => null
                ));
            } else {
                unlink(APP_PATH ."/public/assets/uploads/assignments/". $submission->response);
            }
            $submission->response = $response;

            if (!$submission->validate()) {
                $return["success"] = "Invalid Response! Validation Failed";
                return $return;
            }
            $submission->save();
            $return["success"] = "You have successfully submitted the assignment!";
        }
        return $return;
    }

	protected function _submission($submissions, $a) {
        $submit = array("submission" => false, "file" => null, "status" => null, "remarks" => null, "grade" => null, "submission_id" => null);
        
        foreach ($submissions as $s) {
            if ($s->assignment_id == $a->id) {
                $submit["file"] = $s->response;
                $submit["submission"] = true;
                $submit["status"] = $s->live ? "Accepted" : "Rejected";
                $submit["remarks"] = $s->remarks;
                $submit["grade"] = $s->grade;
                $submit["submission_id"] = $s->id;
                break;
            }
        }
        return $submit;
    }
}