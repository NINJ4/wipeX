<?php
#####################################################
#	No security module for the PEX web Interface.
#		--CAUTION--: This is not recommended unless 
#			you have the entire directory password 
#			protected!
#
#		Author: NINJ4
#		Created 12/20
#		Last Edited: 
#####################################################

global $wipeX_User;
$wipeX_User = $_SERVER['REMOTE_ADDR'];

function wipex_check_security() {
	return true;
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
