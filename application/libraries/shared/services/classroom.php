<?php

/**
 * Classroom Service
 *
 * @author Hemant Mann
 */
namespace Shared\Services;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;
use Framework\RequestMethods as RequestMethods;

class Classroom extends \Auth {
	/**
	 * @var object \Classroom
	 * @readwrite
	 */
	protected static $_classroom;

	public static function init($classroom) {
		self::$_classroom = $classroom;
	}

	public function enrollments($classroom, $opts = array()) {
		$this->noview();
        $enrollments = \Enrollment::all(array("classroom_id = ?" => $classroom->id), array("user_id"));

        $students = array();
        foreach ($enrollments as $e) {
            $usr = \User::first(array("id = ?" => $e->user_id), array("name", "username"));
            if (!isset($opts['only_user'])) {
                $scholar = \Scholar::first(array("user_id = ?" => $e->user_id), array("roll_no"));   
            }

            $extra = $this->_extraFields($e, $opts);
            
            if (isset($opts['conversation'])) {
                $extra = array(
                    'username' => $usr->username,
                    'class' => $classroom->grade,
                    'section' => $classroom->section,
                    'display' => $usr->name . " (Class: ". $classroom->grade ." - ". $classroom->section . ") Roll No: " . $scholar->roll_no
                );
            }

            if (!isset($opts['only_user'])) {
               $data = array(
                   "user_id" => $e->user_id,
                   "name" => $usr->name,
                   "roll_no" => $scholar->roll_no
               ); 
            } else {
                $data = array("user_id" => $e->user_id);
            }
            
            $data = array_merge($data, $extra);
            $data = ArrayMethods::toObject($data);
            $students[] = $data;
        }
        return $students;
	}

	protected function _extraFields($e, $opts) {
		$extra = array();
		if (!isset($opts['table'])) {
			return $extra;
		}
		$t = Registry::get("MongoDB")->$opts["table"];

		$user = Registry::get("session")->get("user"); $yr = date('Y');
	    switch ($opts["table"]) {
	        case 'attendance':
	            $date = date('Y-m-d 00:00:00');
	            $mongo_date = new \MongoDate(strtotime($date));
	            $record = $t->findOne(array('date' => $mongo_date, 'user_id' => (int) $e->user_id));
	            if (isset($record)) {
	                $extra = array('presence' => $record["presence"]);    
	            } else {
	                $extra = array('presence' => null);
	            }
	            break;
	        
	        case 'performance':
	            $record = $t->findOne(array('user_id' => (int) $e->user_id, 'teacher_id' => (int) $user, 'course_id' => (int) ($opts["teach"]->course_id), 'year' => $yr));
	            if (isset($record)) {
	                $date = new \DateTime(date('Y-m-d'));
	                $week = $date->format("W");
	                foreach ($record['track'] as $r) {
	                    if ($week == $r['week']) {
	                        $extra = array('grade' => (int) $r['grade']);
	                        break;
	                    } else {
	                    	$extra = array('grade' => null);
	                    }
	                }
	            }
	            break;
	    }

		return $extra;
	}

	public function saveAttendance($classroom) {
		$this->noview();
		$mongo = Registry::get("MongoDB");
        $attendance = $mongo->attendance;
        
        $date = date('Y-m-d 00:00:00');
        $mongo_date = new \MongoDate(strtotime($date));
        if (RequestMethods::post("action") == "saveAttendance") {
            $attendances = $this->reArray($_POST);

            foreach ($attendances as $a) {
                if (!is_numeric($a["user_id"]) || !is_numeric($a["presence"])) {
                    return false;
                }
                $doc = array(
                    "user_id" => (int) $a["user_id"],
                    "classroom_id" => (int) $classroom->id,
                    "organization_id" => (int) Registry::get("session")->get("organization")->id,
                    "date" => $mongo_date,
                    "presence" => (int) $a["presence"],
                    "live" => true
                );

                $where = array('user_id' => (int) $a["user_id"], 'date' => $mongo_date);
                $record = $attendance->findOne($where);
                if (isset($record)) {
                    $attendance->update($where, array('$set' => $doc));
                } else {
                    $attendance->insert($doc);
                }
            }
            return array("success" => true);
        } else {
            $record = $attendance->findOne(array('classroom_id' => (int) $classroom->id, 'date' => $mongo_date));
            if (isset($record)) {
                return array("saved" => true);
            } else {
                return array("empty" => true);
            }
        }
	}

	public function weeklyPerformance($teach) {
		$this->noview();
		$perf = Registry::get("MongoDB")->performance;

        if (RequestMethods::post("action") == "grade") {
            $performances = $this->reArray($_POST);

            $week = (new DateTime(date('Y-m-d')))->format("W");
            $yr = date('Y');

            foreach ($performances as $p) {
                if (!is_numeric($p["user_id"]) || !is_numeric($p["grade"])) {
                    return false;
                }

                $where = array('user_id' => (int) $p['user_id'], 'course_id' => (int) $teach->course_id, 'teacher_id' => (int) $this->user->id, 'year' => $yr);
                $record = $perf->findOne($where);
                
                // if record exists then we need to loop for the performance tracks
                // to update grade for this week
                $track = array();
                if (isset($record)) {
                    $found = false;
                    foreach ($record['track'] as $r) {
                        if ($week == $r['week']) {
                            $track[] = array('week' => (int) $week, 'grade' => (int) $p['grade']);
                            $found = true;
                        } else {
                            $track[] = $r;
                        }
                    }
                    if (!$found) {
                        $track[] = array('week' => (int) $week, 'grade' => (int) $p['grade']);
                    }
                    $perf->update($where, array('$set' => array('track' => $track)));
                } else {
                    $where = array_merge($where, array('classroom_id' => (int) $teach->classroom_id));
                    $track[] = array('week' => (int) $week, 'grade' => (int) $p['grade']);
                    $doc = array_merge($where, array('track' => $track));
                    $perf->insert($doc);
                }
            }
            return array('message' => 'Weekly performance of students saved!!');
        } else {
            return array('message' => null);
        }
	}

}
