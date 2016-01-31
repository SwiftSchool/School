<?php

/**
 * Student Service
 *
 * @author Hemant Mann
 */
namespace Shared\Services;
use Framework\Registry as Registry;
use Framework\StringMethods as StringMethods;
use Framework\ArrayMethods as ArrayMethods;

class Student extends \Shared\Controller {
	/**
	 * @var object \Educator
	 * @readwrite
	 */
	protected static $_student;

	/**
	 * @readwrite
	 */
	public static $_courses = null;

	/**
	 * @readwrite
	 */
	public static $_classroom = null;

	public static function init($student) {
		self::$_student = $student;

		self::_init();
	}

	public static function destroy() {
		self::$_student = null;
		self::$_courses = null;
		self::$_classroom = null;
	}

	protected static function _init() {
		if (!self::$_classroom) {
			$enrollment = \Enrollment::first(array("user_id = ?" => self::$_student->user_id), array("classroom_id"));
			$c = \Classroom::first(array("id = ?" => $enrollment->classroom_id), array("grade_id", "section", "year", "id", "created"));
			$g = \Grade::first(array("id = ?" => $c->grade_id), array("title", "id"));

			$classroom = array(
				"id" => $c->id,
				"grade" => $g->title,
				"grade_id" => $g->id,
				"section" => $c->section,
				"year" => $c->year,
				"created" => $c->created
			);
			self::$_classroom = ArrayMethods::toObject($classroom);
		}
		if (!self::$_courses) {
			$courses = \Course::all(array("grade_id = ?" => self::$_classroom->grade_id));
			
			$subject = array();
			foreach ($courses as $c) {
				$subject[$c->id] = $c;
			}
			self::$_courses = $subject;
		}
	}

	public function performance($course) {
		$session = Registry::get("session");
		$perf = Registry::get("MongoDB")->performance;

        $week = (new \DateTime(date('Y-m-d')))->format("W");

        $performance = array();
        $classroom = self::$_classroom;

        $record = $perf->findOne(array('user_id' => (int) self::$_student->user_id, 'course_id' => (int) $course->id, 'year' => date('Y'), 'classroom_id' => (int) $classroom->id));

        $d = StringMethods::month_se();
        $start = (int) (new \DateTime($d['start']))->format("W");
        if ($start == 53) {
            $start = 1;
        }
        $end = (int) (new \DateTime($d['end']))->format("W");
        $monthly = array();

        if (isset($record)) {
            $performance['course'] = $course->title;
            $performance['teacher'] = \User::first(array("id = ?" => $record['teacher_id']), array("name"))->name;
            foreach ($record['track'] as $track) {
                $week = $track['week'];
                if ($week <= $end && $week >= $start) {
                    $monthly[] = $track['grade'];
                }
                $performance['tracking'][] = $track;
            }
        }

        return array('performance' => $performance, 'monthly' => $monthly);
	}
}
