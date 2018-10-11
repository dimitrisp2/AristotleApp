<?php
$page = "Tasks";
include("functions.php");
if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = NULL;
}

if ($action == "mark") {
	// Mark task as complete
} else if ($action == "reply-task") {
	// Reply to task
} else if ($action == "new-task") {
	// Submit new task
} else if ($action == "view") {
	// View task and messages
	if (!intval($_GET['i'])) {
		$pagecontent = "Invalid arguments passed to the app. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";
	} else {
		$pagecontent = GetTask($_GET['i']);
		
	}
} else if ($action == "showall" || $action == "resolved") {
	$tasklist = GetTasks($action);
	$pagecontent = "<p class=\"lead\">Refine view: <a href=\"tasks.php?a=showall\">All</a> | <a href=\"tasks.php\">In Progress</a> | <a href=\"tasks.php?a=resolved\">Resolved</a></p><table class=\"table table-striped table-hover\"><thead><tr><th>Project</th><th>User</th><th>Title</th><th>Date</th><th></th></tr></thead><tbody>$tasklist</tbody></table>";
} else {
	$tasklist = GetTasks(NULL);
	$pagecontent = "<p class=\"lead\">Refine view: <a href=\"tasks.php?a=showall\">All</a> | <a href=\"tasks.php\">In Progress</a> | <a href=\"tasks.php?a=resolved\">Resolved</a></p><table class=\"table table-striped table-hover\"><thead><tr><th>Project</th><th>User</th><th>Title</th><th>Date</th><th></th></tr></thead><tbody>$tasklist</tbody></table>";
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