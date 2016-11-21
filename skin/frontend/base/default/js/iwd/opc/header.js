jQuery(document).ready(function() {
    jQuery('.header-container a').click(function(e) {
    	if(window.top != undefined)
    	{
    		e.preventDefault();
    		window.top.location.href = jQuery(this).attr('href');
    	}
    });
});