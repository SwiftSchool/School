<?php

/**
 * Assignment Service
 *
 * @author Hemant Mann
 */
namespace Shared\Services;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Assignment extends Student {

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