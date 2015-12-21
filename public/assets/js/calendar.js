(function (window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

(function (window, request, $, bootbox) {
	var Cal = (function () {
		function Cal() {
			var now = new Date(),
				today;
			now = now.toISOString();
			today = now.split("T")[0];

			this.today = today;
			this.controller = 'events';
		}

		Cal.prototype = {
			_reload: function () {
				window.location.href = '/' + this.controller;
			},
			addEvent: function (date) {
				var self = this;
				$('#sendDate').attr("value", date.format());
				$('#addEvent').modal('show');
				$('#addAppointment').on('submit', function () {
					self._reload();
				});
			},
			_show: function (opts) {
				if (opts.event) {
					var evtModal = {
						location: $('#appointLocation'),
						title: $('#appointTitle'),
						edit: $('#appointEdit'),
						del: $('#appointDel'),
						display: $('#displayEvent')
					},
					usr = {
						name: $('#usrName'),
						email: $('#usrEmail'),
						phone: $('#usrPhone')
					};

					evtModal.location.html(opts.event._location);
					evtModal.title.html(opts.event._title);
					evtModal.edit.attr("href", this.controller + "/edit/" + opts.event._id);
					evtModal.del.attr("data-appointId", opts.event._id);
					
					usr.name.html(opts.user._name);
					usr.email.html(opts.user._email);
					usr.phone.html(opts.user._phone);

					evtModal.display.modal('show');
				} else {
					bootbox.alert('Could not fetch the details for the event!!');
				}
			},
			showEvent: function (id) {
				var self = this;
				request.read({
					action: self.controller + '/display/' + id,
					data: '',
					callback: function (data) {
						if (data.e) {
							self._show({event: data.e, user: data.usr});
						} else if (data.err) {
							self._show({event: null});
						} 
					}
				});
			},
			changeEvent: function (e, revertFunc) {
				bootbox.confirm("Are you sure about this change?", function (proceed) {
					if (!proceed) {
						revertFunc();
						return;
					}
				});
				var apptmtId = e.id,
					date = e.start._d,
					start,
					self = this;

				start = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
				start += " 00:00:00";
				
				request.create({
					action: self.controller + '/change',
					data: {action: "changeAppointment", id: apptmtId, start: start},
					callback: function (data) {
						if (data.success) {
							bootbox.alert("The event has been rescheduled");
						}
					}
				});
			},
			deleteEvent: function (id) {
				bootbox.confirm("Are you sure, you want to delete this object?", function(result) {
					if (!result) {
					    return;
					}
				});
				var self = this;
				request.read({
					action: self.controller + '/delete' + id,
					data: '',
					callback: function (data) {
						if (data.success) {
							bootbox.alert("Event deleted successfully!");
						} else {
							bootbox.alert("Failed to delete the event!!");
						}
						self._reload();
					}
				});
			}
		}
		return Cal;
	}());
	window.Cal = new Cal;
}(window, window.request, jQuery, bootbox));

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
			/* Cal.addEvent(date); */
			$('#sendDate').attr("value", date.format());
			$('#addEvent').modal('show');
			$('#addAppointment').on('submit', function () {
				reloadCal();
			});
		},
		defaultDate: today,
		timezone: 'Asia/Kolkata',
		editable: true,
		eventDrop: function (event, delta, revertFunc) {
			/* Cal.changeEvent(event, revertFunc); */
			if (!confirm("Are you sure about this change?")) {
				revertFunc();
			}
			changeAppointment(event);
		},
		eventClick: function (event) {
			/* Cal.displayEvent(event.id) */
			displayEvent(event.id);
		},
		eventLimit: true, // allow "more" link when too many events
		eventSources: ["/appointments/all"]
	});

	$('#appointDel').on('click', function (e) {
		e.preventDefault();
		var id = $(this).attr("data-appointId");
		x = confirm('Delete the appointment?');
		if (x) {
			deleteEvent(id);
		}
		/* Cal.deleteEvent(id);	*/
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