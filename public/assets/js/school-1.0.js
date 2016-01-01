(function (window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

(function (window, request, $) {
    var School = (function () {
        function School() {}

        School.prototype = {
            _find: function (opts) {
                var find = $(opts.selector), target;
                if (find.length > 1) {
                    target = find[opts.index];
                } else {
                    target = find[0];
                }
                target = $(target);
                return target;
            },
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
                var target = this._find(opts);
                this._fetch(opts, function (data) {
                    if (data.results) {
                        target.html('');
                        var classrooms = data.results, i, max;
                        for (i = 0, max = classrooms.length; i < max; ++i) {
                            target.append('<option value="' + classrooms[i]._id + '">' + classrooms[i]._section + '</option>');
                        }
                    } else {
                        target.html('<option>No Sections for this grade</option>');
                    }
                });
            },
            findCourses: function(opts) {
                var target = this._find(opts);
                this._fetch(opts, function (data) {
                    if (data.results) {
                        target.html('');
                        var courses = data.results, i, max;
                        for (i = 0, max = courses.length; i < max; ++i) {
                            target.append('<option value="' + courses[i]._id + '">' + courses[i]._title + '</option>');
                        }
                    } else {
                        target.html('<option>No Courses for this grade</option>');
                    }
                });
            }
        }

        return School;
    }());
    window.School = new School();
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

    $('#addMore').on('click', function(event) {
        event.preventDefault();
        
        var page = $(this).data('page'),
            formClass = page + 'Structure',
            form = $('.' + formClass);

        $("#more_data").after(form);
    });
});