(function (window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

$(document).ready(function() {
    $('#selectGrade').on('change', function (e) {
        e.preventDefault();
        var val = $(this).val(),
            opts = {
                model: 'Classroom',
                query: [{
                    where: 'grade_id = ?',
                    value: val
                }]
            },
            sections = $("#gradeSections");

        request.create({
            action: 'school/misc',
            data: {action: 'process', opts: opts},
            callback: function (data) {
                if (data.results) {
                    sections.html('');
                    var classrooms = data.results, i, max;
                    for (i = 0, max = classrooms.length; i < max; ++i) {
                        sections.append('<option value="' + classrooms[i]._id + '">' + classrooms[i]._section + '</option>');
                    }
                } else {
                    sections.html('<option>No Sections for this grade</option>');
                }
            }
        });
    });

    $('#addMore').on('click', function(event) {
        event.preventDefault();
        
        var page = $(this).data('page'),
            formClass = page + 'Structure',
            form = $('.' + formClass);

        $("#more_data").after(form);
    });
});