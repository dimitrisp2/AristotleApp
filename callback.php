<?php
include("functions.php");

if (isset($_GET['access_token']) AND isset($_GET['expires_in']) AND isset($_GET['username'])) {
	setcookie("code", $_GET['access_token'], time() + 604800);
	setcookie("username", $_GET['username'], time() + 604800);
	SubmitCookie2DB($_GET['access_token'], $_GET['username'], $_GET['expires_in']);
} else {
	header("Location: https://steemit.com/@aristotle.team/");
}
?>