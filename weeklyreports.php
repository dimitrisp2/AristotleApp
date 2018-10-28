<?php
$page = "Weekly Reports";
include("functions.php");

if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = NULL;
}

if ($action == NULL || $action == "list") {
	$reviewlist = GetReportList();
	$pagecontent = "<table class=\"table table-striped table-hover\"><thead><tr><th>Week</th><th>Moderator</th><th></th></tr></thead><tbody>$reviewlist</tbody></table>";
} else if ($action = "view") {
	
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