<?php
#####################################################
#	PHPBB-standalone security module.
#		Only allows access to members of the chosen
#		user groups.
#
#		Author: NINJ4
#		Created 12/20
#		Last Edited: 
#####################################################
#PHPBB setup info:
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : PHPBB_REL_PATH;
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
##End PHPBB3


# Check for non-logged-in or bots
if ( ( $user->data['user_id'] == ANONYMOUS ) || ( $user->data['is_bot'] ) ) {
	login_box( $_SERVER['PHP_SELF'], $user->lang['LOGIN'] );
	die();
}
global $wipeX_standalone_security;
$wipeX_standalone_security = false;
if ( strpos( STANDALONE_GROUPS, $user->data['group_id'] ) !== false )
	$wipeX_standalone_security = true;

global $wipeX_User;
$wipeX_User = $user->data['username'];

function wipex_check_security() {
	return $GLOBALS['wipeX_standalone_security'];
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
