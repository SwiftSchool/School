<?php

/**
 * Attendance Service
 *
 * @author Hemant Mann
 */
namespace Shared\Services;
use Shared\Services\Classroom as Classroom;

class Attendance extends Classroom {
	/**
	 * Stores the Attendance table
	 * @readwrite
	 */
	protected static $_collection = false;

	public function __construct($options = array()) {
		parent::__construct($options);

		if (!self::$_collection) {
			$mongo = Registry::get("MongoDB");
        	self::$_collection = $mongo->selectCollection("attendance");
		}	
	}
	/**
	 * Find the attendance for the given user (student)
	 */
	public function find($start = null, $end = null) {
        $attendance = self::$_collection;
        
        if ($start && $end) {
            $start = new MongoDate(strtotime($start));
            $end = new MongoDate(strtotime($end));
            $records = $attendance->find(array('user_id' => (int) $this->user->id, 'live' => true, 'date' => array('$gte' => $start, '$lte' => $end)));
        } else {
            $records = $attendance->find(array('user_id' => (int) $this->user->id, 'live' => true), array('date' => true, '_id' => false, 'presence' => true));    
        }

        $i = 1; $results = array();
        foreach ($records as $r) {
            $date = date('Y-m-d', $r["date"]->sec);
            $results[] = array(
                "title" => ($r["presence"]) ? "Present" : "Absent",
                "start" => $date . "T00:00:00",
                "end" => $date . "T23:59:59",
                "allDay" => true,
                "className" => "attendance",
                "color" => ($r["presence"]) ? "green" : "red"
            );
            ++$i;
        }
        return $results;
	}
}
