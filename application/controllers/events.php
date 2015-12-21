<?php

/**
 * Class controlling appointment - scheduling, editing etc.
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Events extends School_Admin {
	/**
	 * @protected
	 */
	public function changeLayout() {
		$this->defaultLayout = "layouts/school_admin";
		$this->setLayout();
	}

	/**
	 * @before _secure, _admin
	 */
	public function index() {
		$this->seo(array("title" => "Schedule Your Appointments", "view" => $this->getLayoutView()));
        $this->getLayoutView()->set("cal", true);
        $view = $this->getActionView();
	}

	public function success() {
		$this->seo(array("title" => "Schedule Your Appointments", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "appointment") {
            $user = new User(array(
                "name" => RequestMethods::post("name"),
                "email" => RequestMethods::post("email"),
                "password" => sha1(rand(100000, 9999999)),
                "phone" => RequestMethods::post("contact"),
                "admin" => FALSE,
                "gender" => RequestMethods::post("gender")
            ));
            $user->save();
            $view->set("user", $user);
            
            $appointment = new Event(array(
                "user_id" => $user->id,
                "title" => RequestMethods::post("service"), 
                "start" => RequestMethods::post("date"),
                "end" => RequestMethods::post("date"),
                "allDay" => "1"
            ));
            $appointment->save();
            $view->set("appointment", $appointment);
        }
	}

	/**
	 * @before _secure, _admin
	 */
	public function schedule() {
		if (RequestMethods::post("action") == "addEvent") {
			$date = RequestMethods::post("date");
			$date = explode("T", $date);
			$apptmt = new Event(array(
				"user_id" => $this->user->id,
				"title" => RequestMethods::post("title"),
				"start" => $date[0]." 00:00:00",
				"end" => $date[0]. " 23:59:59",
				"allDay" => true,
				"live" => true,
				"deleted" => false
			));
			$apptmt->save();
		}
		self::redirect("/appointments");
	}

	/**
	 * @before _secure, _admin
	 */
	public function delete($appointId) {
		$this->noview();
		$apptmt = Event::first(array("id = ?" => $appointId));
		if ($apptmt->delete()) {
			echo true;
		} else {
			echo false;
		}
	}

	/**
	 * @before _secure
	 */
	public function all() {
		$this->noview();
		$results = Event::all();
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
	 * @before _secure, _admin
	 */
	public function display($id) {
		$this->seo(array("title" => "Display Appointments", "view" => $this->getLayoutView()));
		$view = $this->getActionView();
		$apptmt = Event::first(array("id = ?" => $id), array("title", "id"));
		$usr = User::first(array("id = ?" => $apptmt->user_id), array("name", "email", "phone"));
		if (!$apptmt) {
			$view->set("err", "Invalid ID");
		} else {
			$view->set("usr", $usr);
			$view->set("e", $apptmt);
		}
	}


	/**
	 * @before _secure, _admin
	 */
	public function edit($appointId) {
		$this->seo(array("title" => "Edit an Appointment", "view" => $this->getLayoutView()));
		$view = $this->getActionView();

		$apptmt = Event::first(array("id = ?" => $appointId));
		
		if (RequestMethods::post("action") == "editApptmt") {
			$apptmt->title = RequestMethods::post("title");
			$apptmt->save();

			$view->set("message", "Appointment Updated Successfully");
		}

		$view->set("apptmt", $apptmt);


	}

	private function returnTime($date) {
		$d = explode(" ", $date);
		$d = implode("T", $d);
		return $d;
	}

	/**
	 * @before _secure, _admin
	 */
	public function change() {
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "changeAppointment") {
			$apptmt = Event::first(array("id = ?" => RequestMethods::post("id")));
			$apptmt->start = RequestMethods::post("start");
			$apptmt->save();

			$view->set("success", "Appointment has been saved");
		}
	}
}