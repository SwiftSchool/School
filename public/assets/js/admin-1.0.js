(function (window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

$(document).ready(function() {
    $('#selectClass').on('change', function (e) {
        e.preventDefault();
        var val = $(this).val(),
            opts = {
                model: 'Classroom',
                query: [{
                    where: 'grade_id = ?',
                    value: val
                }]
            };
        request.create({
            action: 'school_admin/misc',
            data: {action: 'process', opts: opts},
            callback: function (data) {
                console.log(data);
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