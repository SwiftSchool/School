(function (window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

var now = new Date();
now = now.toISOString();
var today = now.split("T")[0];	// today: '2015-09-14'

$(document).ready(function () {
	$("#calendar").fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		dayClick: function (date) {
			$('#sendDate').attr("value", date.format());
			$('#addEvent').modal('show');
			$('#addAppointment').on('submit', function () {
				reloadCalCal();
			});
		},
		defaultDate: today,
		timezone: 'Asia/Kolkata',
		editable: true,
		eventDrop: function (event, delta, revertFunc) {
			if (!confirm("Are you sure about this change?")) {
				revertFunc();
			}
			changeAppointment(event);
		},
		eventClick: function (event) {
			displayEvent(event.id);
		},
		eventLimit: true, // allow "more" link when too many events
		eventSources: ["/appointments/all"]
	});

	$('#appointDel').on('click', function (e) {
		e.preventDefault();
		var appointId = $(this).attr("data-appointId");
		x = confirm('Delete the appointment?');
		if (x) {
			deleteEvent(appointId);
		}
	});

});

function deleteEvent(id) {
	$.ajax({
		url: "/appointments/delete/" + id,
		type: 'GET'
	})
	.done(function(data) {
		if (data == 1) {
			alert('Appointment has been deleted');
		} else {
			alert('Failed to delete appointment');
		}
		reloadCal();
	})
	.fail(function() {
		alert('Failed to delete appointment');
	});
}

function reloadCal() {
	window.location.href = "/appointments";
}

function displayEvent(id) {
	request.read({
		action: '/appointments/display/' + id,
		data: '',
		callback: function (data) {
			if (data.e) {
				showAppointment(data.e, data.usr);
			} else if (data.err) {
				showAppointment(null);
			} 
		}
	});
	
}

function showAppointment(appointmnt, usr) {
	if (appointmnt) {
		// Add appointment data to the event
		$('#appointLocation').html(appointmnt._location);
		$('#appointTitle').html(appointmnt._title);
		$('#appointEdit').attr("href", "/appointments/edit/" + appointmnt._id);
		$('#appointDel').attr("data-appointId", appointmnt._id);
		
		$('#usrName').html(usr._name);
		$('#usrEmail').html(usr._email);
		$('#usrPhone').html(usr._phone);

		$('#displayEvent').modal('show');
	} else {
		alert('Could not fetch data for the appointment');
	}
}

function changeAppointment(e) {
	var apptmtId = e.id,
		date = e.start._d;

	var start = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
	start += " 00:00:00";
	
	request.create({
		action: '/appointments/change',
		data: {action: "changeAppointment", id: apptmtId, start: start},
		callback: function (data) {
			if (data.success) {
				alert("The appointment has been rescheduled");
			}
		}
	});
}