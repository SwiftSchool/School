(function (window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

(function (window, request, $) {
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
				$('#addEvent').openModal('show');
				$('#addEventForm').on('submit', function () {
					self._reload();
				});
			},
			_show: function (opts) {
				if (opts.event) {
					var evtModal = {
						description: $('#eventDescription'),
						title: $('#eventTitle'),
						edit: $('#eventEdit'),
						del: $('#eventDel'),
						display: $('#displayEvent')
					};

					evtModal.description.html(opts.event._description);
					evtModal.title.html(opts.event._title);
					evtModal.edit.attr("href", this.controller + "/edit/" + opts.event._id);
					evtModal.del.attr("data-eventId", opts.event._id);
					

					evtModal.display.openModal('show');
				} else {
					alert('Could not fetch the details for the event!!');
				}
			},
			showEvent: function (id) {
				var self = this;
				request.read({
					action: self.controller + '/display/' + id,
					data: '',
					callback: function (data) {
						if (data.e) {
							self._show({event: data.e});
						} else if (data.err) {
							self._show({event: null});
						} 
					}
				});
			},
			changeEvent: function (e, revertFunc) {
				if (!confirm("Are you sure about this change?")) {
					this._reload();
				}
				var apptmtId = e.id,
					date = e.start._d,
					start,
					self = this;

				start = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
				start += " 00:00:00";
				
				request.create({
					action: self.controller + '/change',
					data: {action: "reschedule", id: apptmtId, start: start},
					callback: function (data) {
						if (data.success) {
							alert("The event has been rescheduled");
						}
					}
				});
			},
			deleteEvent: function (id) {
				if (!confirm("Are you sure, you want to delete this object?")) {
					return false;
				}
				var self = this;
				request.read({
					action: self.controller + '/delete/' + id,
					data: '',
					callback: function (data) {
						if (data.success) {
							alert("Event deleted successfully!");
						} else {
							alert("Failed to delete the event!!");
						}
						self._reload();
					}
				});
			}
		}
		return Cal;
	}());
	window.Cal = new Cal();
}(window, window.request, jQuery));

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
			Cal.addEvent(date);
		},
		defaultDate: today,
		timezone: 'Asia/Kolkata',
		editable: true,
		eventDrop: function (event, delta, revertFunc) {
			Cal.changeEvent(event, revertFunc);
		},
		eventClick: function (event) {
			Cal.showEvent(event.id);
		},
		eventLimit: true, // allow "more" link when too many events
		eventSources: ["/events/all"]
	});

	$('#eventDel').on('click', function (e) {
		e.preventDefault();
		var id = $(this).attr("data-eventId");
		Cal.deleteEvent(id);
	});

});
