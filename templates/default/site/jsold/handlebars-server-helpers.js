Handlebars.registerHelper('user_can', function(hasAccess, options) {
	//console.log(this, hasAccess, options);
	if (parseInt(hasAccess) == 0) {
		return options.inverse(this);
	} else {
		//console.log('has access');
		return options.fn(this);
	}
});