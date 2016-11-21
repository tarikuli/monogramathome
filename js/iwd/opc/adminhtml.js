document.observe("dom:loaded", function() {
	if($('_accountcreated_in') != undefined && $('_accountcreated_in').readAttribute('disabled'))
		$('_accountsocial_security_number').disable();
});
