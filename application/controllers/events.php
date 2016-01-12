<?php

/**
 * Class controlling appointment - scheduling, editing etc.
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Events extends School {

	/**
	 * @before _secure, _school
	 */
	public function index() {
		$this->setSEO(array("title" => "Schedule Your Appointments"));
        $this->getLayoutView()->set("cal", true);
        $view = $this->getActionView();
	}

	/**
	 * @before _secure, _school
	 */
	public function schedule() {
		if (RequestMethods::post("action") == "addEvent") {
			$date = RequestMethods::post("date");
			$date = explode("T", $date);
			$event = new Event(array(
				"user_id" => $this->user->id,
				"organization_id" => $this->organization->id,
				"description" => RequestMethods::post("description"),
				"title" => RequestMethods::post("title"),
				"start" => $date[0]." 00:00:00",
				"end" => $date[0]. " 23:59:59",
				"allDay" => true,
				"live" => true,
				
			));
			$event->save();
		}
		self::redirect("/events");
	}

	/**
	 * @before _secure, _school
	 */
	public function delete($event_id) {
		$this->JSONView();
		$view = $this->getActionView();
		$event = Event::first(array("id = ?" => $event_id));
		if (!$event || $event->organization_id != $this->organization->id) {
			$view->set("error", true);
			return;
		}

		$event->delete();
		$view->set("success", true);
	}

	/**
	 * @before _secure
	 */
	public function all() {
		$this->noview();
		$results = Event::all(array("organization_id = ?" => Registry::get("session")->get("organization")->id));
		$events = array();

		foreach ($results as $r) {
			$events[] = array(
				"title" => $r->title,
				"start" => $this->returnTime($r->start),
				"end" => $this->returnTime($r->start),
				"allDay" => ($r->allDay) ? true : false,
				"id" => $r->id
			);
		}
		echo json_encode($events);
	}

	/**
	 * @before _secure
	 */
	public function display($id) {
		$this->JSONView();
		$view = $this->getActionView();
		$event = Event::first(array("id = ?" => $id), array("title", "id", "user_id", "description"));
		if($event->organization_id!=$this->organization->id){
			self::redirect('/');
		}
		if (!$event) {
			$view->set("err", "Invalid ID");
		} else {
			$view->set("e", $event);
		}
	}


	/**
	 * @before _secure, _school
	 */
	public function edit($event_id) {
		$this->setSEO(array("title" => "Edit the Event"));
		$view = $this->getActionView();

		$event = Event::first(array("id = ?" => $event_id));
		
		if (RequestMethods::post("action") == "editEvent") {
			$event->title = RequestMethods::post("title");
			$event->description = RequestMethods::post("description");
			$event->save();

			$view->set("success", "Event Updated Successfully");
		}

		$view->set("event", $event);
	}

	private function returnTime($date) {
		$d = explode(" ", $date);
		$d = implode("T", $d);
		return $d;
	}

	/**
	 * @before _secure, _school
	 */
	public function change() {
		$this->JSONView();
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "reschedule") {
			$event = Event::first(array("id = ?" => RequestMethods::post("id")));
			$event->start = RequestMethods::post("start");
			$event->save();

			$view->set("success", "Event has been rescheduled");
		}
	}
}
