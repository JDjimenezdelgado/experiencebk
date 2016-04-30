jQuery(document).ready(function() {
    jQuery('.colorpick').each(function() {
        var $this = jQuery(this);
        jQuery(this).colpick({
            color: $this.val().substr(1, 7),
            onChange: function(hsb, hex, rgb, el, bySetColor) {
                jQuery(el).val('#' + hex);
            }
        });
    });
});
function vcht_customFilter(){
    var content = jQuery('#filter_custom').val();
    if (content.length>0){
        document.location.href = 'admin.php?page=vcht-logsList&search='+content;
    } else {
        document.location.href = 'admin.php?page=vcht-logsList';
    }
}