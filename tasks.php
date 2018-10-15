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
	$page = "Mark Task as Completed";
	$task = $_GET['i'];
	$pagecontent = SubmitCompleteTask($task, "1");
} else if ($action == "commit-reply") {
	// Add a reply to task (form is already on the task page)
	$page = "Reply to Task";
	//print_r($_POST);
	$taskid = $_POST['id'];
	$from = $_COOKIE['username'];
	$msg = $_POST['taskreply'];
	$pagecontent = SubmitReplyTask($from, $msg, $taskid);
} else if ($action == "commit-new") {
	// Submit new task's details to the database. Form is located on "new-task"
	$page = "Submit New Task";
	$from = $_COOKIE['username'];
	$to = $_POST['proofreader'];
	$msg = $_POST['taskbody'];
	$project = $_POST['project'];
	$title = $_POST['taskname'];
	$pagecontent = SubmitNewTask($from, $to, $title, $msg, $project);
} else if ($action == "new-task") {
	// Submit new task
	$page = "New Task";
	$proofcsv = GetProofreadersCSV();
	$proofhtml = ConvertArray2HTMLOptions(explode(",", $proofcsv), "#", "proofreader");
	$projectcsv = GetProjectsCSV();
	$projecthtml = ConvertArray2HTMLOptions(explode(",", $projectcsv), "#", "project", NULL);
	$pagecontent = "<form action=\"tasks.php?a=commit-new\" method=\"post\"><div class=\"form-group row\"><label for=\"taskname\" class=\"col-2 col-form-label\">Task Title</label> <div class=\"col-10\"><input id=\"taskname\" name=\"taskname\" type=\"text\" required=\"required\" class=\"form-control here\"></div></div><div class=\"form-group row\"><label for=\"project\" class=\"col-2 col-form-label\">Rel.Project</label> <div class=\"col-10\">$projecthtml</div></div><div class=\"form-group row\"><label for=\"proofreader\" class=\"col-2 col-form-label\">To</label> <div class=\"col-10\">$proofhtml</div></div><div class=\"form-group row\"><label for=\"taskbody\" class=\"col-2 col-form-label\">Task Body</label> <div class=\"col-10\"><textarea id=\"taskbody\" name=\"taskbody\" cols=\"40\" rows=\"5\" aria-describedby=\"taskbodyHelpBlock\" required=\"required\" class=\"form-control\"></textarea> <span id=\"taskbodyHelpBlock\" class=\"form-text text-muted\">Please be as instructive as possible on what you need.</span></div></div> <div class=\"form-group row\"><div class=\"offset-2 col-10\"><button name=\"submit\" type=\"submit\" class=\"btn btn-primary\">Submit</button></div></div></form><script>\$(document).ready(function() {\$('#taskbody').summernote();});</script>";
} else if ($action == "view") {
	// View task and messages
	if (!intval($_GET['i'])) {
		$pagecontent = "Invalid arguments passed to the app. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";
	} else {
		$pagecontent = GetTask($_GET['i']);
		$pagecontent .= "<hr /><form action=\"tasks.php?a=commit-reply\" method=\"post\"><div class=\"form-group\"><label for=\"taskreply\">Reply to the Task</label> <textarea id=\"taskreply\" name=\"taskreply\" cols=\"40\" rows=\"5\" class=\"form-control\"></textarea></div><input type=\"hidden\" name=\"id\" value=\"".$_GET['i']."\"><div class=\"form-group\"><button name=\"submit\" type=\"submit\" class=\"btn btn-primary\">Submit</button></div></form><script>\$(document).ready(function() {\$('#taskreply').summernote();});</script>";
		$page = "View Task";
	}
} else if ($action == "showall" || $action == "resolved") {
	$tasklist = GetTasks($action);
	$pagecontent = "<p class=\"lead\">Refine View: <a href=\"tasks.php?a=showall\">All</a> | <a href=\"tasks.php\">In Progress</a> | <a href=\"tasks.php?a=resolved\">Resolved</a> | <a href=\"tasks.php?a=new-task\" class=\"text-white bg-secondary\">New Task</a></p><table class=\"table table-striped table-hover\"><thead><tr><th>Project</th><th>User</th><th>Title</th><th>Date</th><th></th></tr></thead><tbody>$tasklist</tbody></table>";
} else {
	$tasklist = GetTasks(NULL);
	$pagecontent = "<p class=\"lead\">Refine View: <a href=\"tasks.php?a=showall\">All</a> | <a href=\"tasks.php\">In Progress</a> | <a href=\"tasks.php?a=resolved\">Resolved</a> | <a href=\"tasks.php?a=new-task\" class=\"text-white bg-secondary\">New Task</a></p><table class=\"table table-striped table-hover\"><thead><tr><th>Project</th><th>User</th><th>Title</th><th>Date</th><th></th></tr></thead><tbody>$tasklist</tbody></table>";
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