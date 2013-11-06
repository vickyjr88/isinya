<?php
if (isset($_SERVER['HTTP_HOST']) && PHP_SAPI != 'cli') {
	$site_url = 'http://' . $_SERVER['HTTP_HOST'] . $my_base_url;

	if (isset($_COOKIE['user_timezone']) && in_array($_COOKIE['user_timezone'], timezone_identifiers_list())){
		define('USER_TIMEZONE', $_COOKIE['user_timezone']);
	}

	$last_time_checked = isset($_COOKIE['last_tz_autodetect']) ? $_COOKIE['last_tz_autodetect'] : time();
	$autodetect_interval = 1 * 60;
	$next_time_check = $last_time_checked + $autodetect_interval;
	?>
	<?php if (!isset($_COOKIE['user_timezone']) || !$_COOKIE['user_timezone'] || !isset($_COOKIE['last_tz_autodetect']) || intval($_COOKIE['last_tz_autodetect']) == 0 || (strtolower($_COOKIE['user_timezone']) == 'unknown' && time() > $next_time_check) ) : ?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta http-equiv="Refresh" content="10" />
			<title>Initializing..</title>
		</head>
		<body style="text-align: center; padding-top: 20px;">
			<div><p>Please wait..</p><img src="<?php echo $site_url ?>assets/images/progress-loader.gif" /></div>
			<script type="text/javascript" src='//cdn.bitbucket.org/pellepim/jstimezonedetect/downloads/jstz-1.0.4.min.js'></script>
			<script type="text/javascript" src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>
			<script type="text/javascript" src='//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.3.1/jquery.cookie.min.js'></script>
	    	<script type="text/javascript">
	    		function setCookie(c_name,value,exdays) {
					var exdate=new Date();
					exdate.setDate(exdate.getDate() + exdays);
					var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
					document.cookie=c_name + "=" + c_value;
				}
	    		Date.now = Date.now || function() {return +new Date;};
	    		setCookie('last_tz_autodetect', parseInt(Date.now()/1000));
	    		setCookie('user_timezone', 'unknown');
	    	</script>
	    	<script type="text/javascript">
				$(document).ready(function() {
					$.cookie('last_tz_autodetect', parseInt(Date.now()/1000));
					if (!$.cookie('user_timezone') <?php echo (isset($_COOKIE['user_timezone']) && strtolower($_COOKIE['user_timezone']) == 'unknown' && time() > $next_time_check) ? "|| $.cookie('user_timezone') == 'unknown'" : "" ?>) {
						var tz = jstz.determine();
						userTz = 'unknown';

						if ( typeof (tz) === 'undefined') {
							userTz = 'unknown';
						} else {
							userTz = tz.name();
						}

						$.cookie('user_timezone', userTz);
						window.location.reload();
					}
				});
			</script>
		</body>
	</html>
	<?php exit; endif;
} else {
	define('USER_TIMEZONE', 'UTC');
}
