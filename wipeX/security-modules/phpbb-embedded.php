<?php
#####################################################
#	PHPBB-embedded security module.
#		Advanced Users Only: use this module if you plan to simply
#			include the PEX_Interface.php file in another
#			phpbb page.
#
#		Author: NINJ4
#		Created 12/28
#		Last Edited: 
#####################################################
# Check for PHPBB constant configuration:
if ( !IN_PHPBB ) {
	die('Security Error');
}

global $wipeX_User;
$wipeX_User = $user->data['username'];

function wipex_check_security() {
	return true;
}
function wipeX_header() {
	echo '';
}
function wipeX_footer() {
	echo '';
}

?>
