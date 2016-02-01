<?php

/**
 * Teacher Service: Will find and store data required to perform various actions
 *
 * @author Hemant Mann
 */
namespace Shared\Services;
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use Framework\ArrayMethods as ArrayMethods;

class Teacher extends \Shared\Controller {
	/**
	 * @var object \Educator
	 * @readwrite
	 */
	protected static $_teacher;

	/**
	 * @readwrite
	 */
	public static $_courses = null;

	/**
	 * @readwrite
	 */
	public static $_classes = null;

	public static function init($teacher) {
		self::$_teacher = $teacher;

		self::_init();
	}

	public static function destroy() {
		$session = Registry::get("session");
		$session->erase('TeacherService:$courses')
				->erase('TeacherService:$classes');
		self::$_teacher = null;
		self::$_courses = null;
		self::$_classes = null;
	}

	protected static function _init() {
		$session = Registry::get("session");
		if (!self::$_courses || !self::$_classes) {
			if (!$session->get('TeacherService:$courses') || !$session->get('TeacherService:$classes')) {
				$teaches = \Teach::all(array("user_id = ?" => self::$_teacher->user_id));
				
				$session->set('TeacherService:$courses', self::_findCourses($teaches));
				$session->set('TeacherService:$classes', self::_findClasses($teaches));
			}
			self::$_courses = $session->get('TeacherService:$courses');
			self::$_classes = $session->get('TeacherService:$classes');
		}
	}

	protected static function _findCourses($teaches) {
		$c_ids = array();
		foreach ($teaches as $t) {
			$c_ids[] = $t->course_id;
		}
		$c_ids = array_unique($c_ids);

		$courses = array();
		foreach ($c_ids as $key => $value) {
			$c = \Course::first(array("id = ?" => $value));
			$courses[$c->id] = $c;
		}
		return $courses;
	}

	protected static function _findClasses($teaches) {
		$class_ids = array();
		foreach ($teaches as $t) {
			$class_ids[] = $t->classroom_id;
		}
		$class_ids = array_unique($class_ids);

		$classes = array();
		foreach ($class_ids as $key => $value) {
			$k = \Classroom::first(array("id = ?" => $value));
			$g = \Grade::first(array("id = ?" => $k->grade_id));
			$data = array(
				"id" => $k->id,
				"grade_id" => $g->id,
				"grade" => $g->title,
				"section" => $k->section,
				"year" => $k->year
			);
			$classes[$k->id] = ArrayMethods::toObject($data);
		}
		return $classes;
	}
}
