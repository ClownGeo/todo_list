/**
 * Created by georgijsergusin on 17.10.16.
 */
$(function () {
    $('#add-note').click(
        function () {
            $.ajax({
                url: '/getNewNote',
                success: function(data)
                {
                    $('#workplace').html(data);
                }
            });
        }
    )
});
$(function () {
    $('input[type=radio][name=filter]').change(function() {
        $('#jstree').jstree(true).settings.core.data.url = '/getIt/' + $('input[type=radio][name=filter]:checked').val();
        $('#jstree').jstree(true).refresh();
    });
});