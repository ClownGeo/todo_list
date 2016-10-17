/**
 * Created by georgijsergusin on 17.10.16.
 */
$(function () {
    $('#jstree').jstree({
        "plugins" : [ "wholerow" ],
        'core' : {
            "themes" : {
                "variant" : "large"
            },
            'data' : {
                'url' : '/getIt/' + $('input[type=radio][name=filter]:checked').val(),
                'data' : function (node) {
                    return { 'id' : node.id };
                }
            }
        }
    });
});
$(function () {
    $('#jstree').on('activate_node.jstree', function (e, jsTreeEvent) {
            $.ajax({
                url: '/getNote/' + jsTreeEvent.node.id,
                success: function(data){
                    $('#workplace').html(data);
                }
            });
        })
        .jstree();
});