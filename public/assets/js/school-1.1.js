(function (window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

(function (window, $) {
    var Display = (function () {
        function Display() {
            this.target = false;
        }

        Display.prototype = {
            find: function (selector) {
                var find = $(selector), target;
                if (find.length > 1) {
                    target = find[opts.index];
                } else {
                    target = find[0];
                }
                target = $(target);
                return target;
            },
            display: function (opts, data) {
                var target = this.find(opts.selector);
                if (data.results) {
                    target.html('');
                    var objects = data.results, i, max;
                    for (i = 0, max = objects.length; i < max; ++i) {
                        target.append('<option value="' + objects[i][opts.val] + '">' + objects[i][opts.display] + '</option>');
                    }
                } else {
                    target.html('<option>No ' + opts.title + 's for this grade</option>');
                }
            },
            info: function (usr) {
                var modal = $('#userInfo'),
                    el = $('#queryResults');

                el.html('<p><strong>Name:</strong> ' + usr._name + '</p><p><strong>Email:</strong> ' + usr._email + '</p><p><strong>Phone:</strong> ' + usr._phone + '<p><strong>Roll No:</strong> ' + usr._roll_no + '</p><p><strong>Date Of Birth:</strong> ' + usr._dob + '</p>');
                modal.openModal('show');
            }
        }

        return Display;
    }());

    window.Display = new Display();
}(window, jQuery));

/*** School Controller ***/
(function (window, request, $, Display) {
    var School = (function () {
        function School() {
            this.cache = { student: {} };
        }

        School.prototype = {
            _fetch: function (opts, callback) {
                var self = this;
                request.create({
                    action: 'school/misc',
                    data: {action: 'process', opts: opts},
                    callback: function (data) {
                        callback.call(self, data);
                    }
                });
            },
            findSections: function (opts) {
                this._fetch(opts, Display.display({ val: '_id', display: '_section', selector: opts.selector, title: 'Section' }, data));
            },
            findCourses: function(opts) {
                this._fetch(opts, Display.display({ val: '_id', display: '_title', selector: opts.selector, title: 'Courses' }, data));
            },
            findExams: function (opts) {
                var target = Display.find(opts.selector);
                this._fetch(opts, function (data) {
                    if (data.results) {
                        target.html('');
                        var exams = data.results, i, max, str, unique = [];

                        for (i = 0, max = exams.length; i < max; ++i) {
                            str = exams[i]._type + ' (' + exams[i]._year + ')';
                            
                            if ($.inArray(str, unique) == -1) {
                                unique.push(str);
                                target.append('<option value="' + exams[i]._type + ';' + exams[i]._year + '">' + str + '</option>');
                            }
                        }
                    } else {
                        target.html('<option>No Exams for this grade</option>');
                    }
                });
            },
            _studentInfo: function (usr, callback) {
                var opts = {
                    model: 'Scholar',
                    query: [{
                        where: 'user_id = ?',
                        value: usr._id
                    }],
                    fields: ["dob", "roll_no"]
                };
                var self = this;
                self._fetch(opts, function (data) {
                    callback.call(self, data);
                });
            },
            studentInfo: function (opts) {
                var self = this,
                    uid = opts.query[0].value,
                    cached = self.cache.student;
                if (typeof cached['User-' + uid] == "undefined") {
                    self._fetch(opts, function (data) {
                        if (!data.results) {
                            return;
                        }
                        var usr = data.results[0];

                        self._studentInfo(usr, function (data) {
                            if (data.results) {
                                var st = data.results[0];
                                usr._roll_no = st._roll_no; usr._dob = st._dob;
                            }
                            cached['User-' + uid] = usr;
                            Display.info(cached['User-' + uid]);
                        });
                    });
                } else {
                    Display.info(cached['User-' + uid]);
                }
            }
        }

        return School;
    }());
    window.School = new School();
}(window, window.request, jQuery, window.Display));

/*** Calendar Controller ***/
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

$(document).ready(function() {
    $('.findSections').on('change', function (e) {
        e.preventDefault();
        var val = $(this).val(),
            opts = {
                selector: '.gradeSections',
                index: $(this).data('id'),
                model: 'Classroom',
                query: [{
                    where: 'grade_id = ?',
                    value: val
                }]
            };
            
        School.findSections(opts);
    });

    $('.findCourses').on('change', function (e) {
        e.preventDefault();
        var val = $(this).val(),
            opts = {
                selector: '.gradeCourses',
                index: $(this).data('id'),
                model: 'Course',
                query: [{
                    where: 'grade_id = ?',
                    value: val
                }]
            };
            
        School.findCourses(opts);
    });

    $('.findExams').on('change', function (e) {
        e.preventDefault();
        var val = $(this).val(),
            opts = {
                selector: '.gradeExams',
                index: $(this).data('id'),
                model: 'Exam',
                query: [{
                    where: 'grade_id = ?',
                    value: val
                }]
            };
            
        School.findExams(opts);
    });

    $('#addMore').on('click', function(event) {
        event.preventDefault();
        
        var page = $(this).data('page'),
            formClass = page + 'Structure',
            form = $('.' + formClass);

        $("#more_data").after(form);
    });

    if ($("#calendar").length !== 0) {
        $("#calendar").fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            dayClick: function (date) {
                Cal.addEvent(date);
            },
            defaultDate: Cal.today,
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
    }
    
    $('#eventDel').on('click', function (e) {
        e.preventDefault();
        var id = $(this).attr("data-eventId");
        Cal.deleteEvent(id);
    });

    $('.showUserInfo').on('click', function (e) {
        e.preventDefault();

        var opts = {
                model: 'User',
                query: [{
                    where: 'id = ?',
                    value: $(this).data('uid')
                }],
                fields: ["name", "email", "phone", "id"]
            };
        School.studentInfo(opts);
    });
});