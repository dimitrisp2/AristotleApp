<?php
$page = "Users";
include("functions.php");

	$userlist = GetAllUsers();
	$pagecontent = "<table class=\"table table-striped table-hover\"><thead><tr><th>User</th><th>Member Role</th><th>Hired</th><th>Dismissed</th><th></th></tr></thead><tbody>$userlist</tbody></table>";
include("common/head.php");
?>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
				<?php echo $pagecontent; ?>
            </div>
        </div>
    </div>
<?php
//print_r($pending);
include("common/foot.php");
?>