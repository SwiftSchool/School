<?php

/**
 * Teacher Service: Will find and store data required to perform various actions
 *
 * @author Hemant Mann
 */
namespace Shared\Services;

class Teacher extends \Shared\Controller {
	/**
	 * @var object \Educator
	 * @readwrite
	 */
	protected static $_teacher;

	/**
	 * @readwrite
	 */
	protected static $_courses = null;

	/**
	 * @readwrite
	 */
	protected static $_classrooms = null;

	public static function init($teacher) {
		self::$_teacher = $teacher;

		self::_init();
	}

	public static function destroy() {
		self::$_teacher = null;
		self::$_courses = null;
		self::$_classrooms = null;
	}

	protected static function _init() {
		self::_courses();
		self::_classes();
	}

	protected static function _courses() {
		if (!self::$_courses) {
			$teaches = Teach::all(array("user_id = ?" => self::$_teacher->user_id));
			
			$c_ids = array();
			foreach ($teaches as $t) {
				$c_ids[] = $t->course_id;
			}
			$c_ids = array_unique($c_ids);

			$courses = array();
			foreach ($c_ids as $key => $value) {
				$c = Course::first(array("id = ?" => $value));
				$courses[$c->id] = $c;
			}
			self::$_courses = $courses;
		}
	}

	protected static function _classes() {
		if (!self::$classrooms) {
			
		}
	}
}
