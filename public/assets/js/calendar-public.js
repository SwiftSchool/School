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

					evtModal.display.modal('show');
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
		defaultDate: today,
		timezone: 'Asia/Kolkata',
		editable: true,
		eventClick: function (event) {
			Cal.showEvent(event.id);
		},
		eventLimit: true, // allow "more" link when too many events
		eventSources: ["/events/all"]
	});

});
