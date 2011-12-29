<?php
#####################################################
#	Simple security module for WIPEX.
#		To access the PEX panel, simply visit the url
#		and append "?pass=PASSWORD"
#
#		Author: NINJ4
#		Created 12/20
#		Last Edited: 
#####################################################

global $wipeX_User;
$wipeX_User = $_SERVER['REMOTE_ADDR'];

function wipex_check_security() {
	if ( "DEFAULT" == SIMPLE_PASSWORD )
		die( "You must change the default password before using." );
	session_start();
	if ( ( isset( $_SESSION['wipex_pass'] ) ) && ( $_SESSION['wipex_pass'] == SIMPLE_PASSWORD ) )
		return true;
	else if ( $_GET['pass'] == SIMPLE_PASSWORD ) {
		$_SESSION['wipex_pass'] = SIMPLE_PASSWORD;
		return true;
	}
	else
		return false;
}
function wipeX_header() {
	echo '
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-gb" xml:lang="en-gb">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="content-style-type" content="text/css" />
		<meta http-equiv="content-language" content="en-gb" />
		<meta name="resource-type" content="document" />
		<meta name="distribution" content="global" />
		<title>wipeX &bull; Web Interface for PermissionsEX by NINJ4</title>
	</head>
	<body>
	';
}
function wipeX_footer() {
	echo '
	</body>
</html>
	';
}

?>
