<?php
$page = "Welcome to AristotleApp";
include("functions.php");
include("common/head.php");
?>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                This app is only intended for use by the <?php echo $teamname; ?> Translation Team. You need to login before you proceed to use anything in this app!<br />
				<a href="https://steemconnect.com/oauth2/authorize?client_id=aristotle.app&redirect_uri=http://localhost/tasklist/callback.php&scope=login" class="font-weight-bold">Secure login via SteemConnect</a><br />
				<hr />
				Follow <a href="https://steemit.com/@dimitrisp" target="_blank">@dimitrisp</a> and the app's account <a href="https://steemit.com/@aristotle.team" target="_blank">@aristotle.team</a> for updates!<br />
				<span class="font-weight-light font-italic">Right now, this app is only using the 'login' scope of SteemConnect, just to verify your identity. This app can't and won't post/transfer/vote anything with your account. Even if we wanted to, the 'login' scope is not giving us any permissions to do any of those actions.</span>
            </div>
        </div>
    </div>
<?php
include("common/foot.php");
?>


