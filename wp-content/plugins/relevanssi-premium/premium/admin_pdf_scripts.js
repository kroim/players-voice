jQuery(document).ready(function() {
    jQuery("#sendUrl").click(function(e) {
        e.preventDefault();
        var postID = jQuery("#sendUrl").data("post_id");
        console.log(postID);
        jQuery.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {
                'action' : 'relevanssi_send_url',
                'post_id' : postID,
            },
            dataType : 'json',
            complete : function( data ) {
                console.log(data);
                location.reload();
            }
        })
    });

    jQuery("#sendPdf").click(function(e) {
        e.preventDefault();
        var postID = jQuery("#sendPdf").data("post_id");
        console.log(postID);
        jQuery.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {
                'action' : 'relevanssi_send_pdf',
                'post_id' : postID,
            },
            dataType : 'json',
            complete : function( data ) {
                console.log(data);
                location.reload();
            }
        })
    });
});
