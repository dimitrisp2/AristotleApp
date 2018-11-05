<?php
include("functions.php");

// Set Current page access level, and check if user has access
$currentacl = FOR_STAFF_AND_LM;
CheckPageAccess();

if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = NULL;
}

if ($action == NULL || $action == "list") {
	$page = "Weekly Reports";
	$reviewlist = GetReportList();
	$pagecontent = "<table class=\"table table-striped table-hover\"><thead><tr><th>Week</th><th>Moderator</th><th></th></tr></thead><tbody>$reviewlist</tbody></table>";
} else if ($action = "view") {
	$page = "";
	$rid = intval($_GET['id']);
	if (is_int($rid)) {
		$pagecontent = GetSingleReport($_GET['id']);
	} else {
		$pagecontent = "Invalid ID. Please try again";
	}
} else if ($action == "add") {
	
} else if ($action = "processadd") {
	
} else {
	
}

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