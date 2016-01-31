<?php

/**
 * Classroom Service
 *
 * @author Hemant Mann
 */
namespace Shared\Services;

class Classroom extends \Shared\Controller {
	/**
	 * @var object \Classroom
	 * @readwrite
	 */
	protected static $_classroom;

	public static function init($classroom) {
		self::$_classroom = $classroom;
	}

	public function findEnrollments() {

	}

}
