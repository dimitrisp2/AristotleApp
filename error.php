<?php
$page = "Error";
include("functions.php");
include("common/head.php");
if (isset($_GET['i'])) {
	$i = $_GET['i'];
} else {
	$i = ERROR_GENERIC;
}
if ($i == ERROR_KICKED_OUT) {
	$pagecontent = "The account you tried to log in with, has no permissions to the page you tried to access, because you are no longer part of the translation team.";
} else if ($i == ERROR_NOT_MEMBER) {
	$pagecontent = "The account you tried to log in with, has no permissions to the page you tried to access, because you are not part of the team.";
} else if ($i == ERROR_MYSQL) {
	$pagecontent = "An SQL error occured. You've been logged out, so try again. If this error persists, please contact @dimitrisp on the DaVinci discord.";
} else if ($i == ERROR_LOGIN) {
	$pagecontent = "You are not logged in. Please <a href=\"https://steemconnect.com/oauth2/authorize?client_id=aristotle.app&redirect_uri=http://localhost/tasklist/callback.php&scope=login\">click here to login via SteemConnect</a>";
} else if ($i == ERROR_PERMISSIONS) {
	$pagecontent = "You do not have permissions to the page you tried to access";
} else {
	$pagecontent = "An unexpected error occured. Please try again later";
}
?>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
				<?php echo $pagecontent; ?>
            </div>
        </div>
    </div>
<?php
include("common/foot.php");
?>


