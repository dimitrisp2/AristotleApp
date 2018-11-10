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
	$pagecontent = "<p class=\"lead\"><a href=\"weeklyreports.php?a=add\">Add new report</a></p><table class=\"table table-striped table-hover\"><thead><tr><th>Week</th><th>Moderator</th><th></th></tr></thead><tbody>$reviewlist</tbody></table>";
} else if ($action == "view") {
	$page = "";
	$rid = intval($_GET['id']);
	if (is_int($rid)) {
		$pagecontent = GetSingleReport($_GET['id']);
	} else {
		$pagecontent = "Invalid ID. Please try again";
	}
} else if ($action == "add") {
	$page = "Add Weekly Report";
	$lastsaturday = date('Y-m-d', strtotime('last Saturday'));
	//echo $lastsaturday;
	$pagecontent = "<form action=\"weeklyreports.php?a=processadd\" method=\"post\"><div class=\"form-group row\"><label for=\"week\" class=\"col-4 col-form-label\">Report for week</label> <div class=\"col-8\"><input id=\"week\" name=\"week\" placeholder=\"2018-11-10\" type=\"text\" value=\"$lastsaturday\" onchange=\"getContributionList()\" aria-describedby=\"weekHelpBlock\" required=\"required\" class=\"form-control here\"> <span id=\"weekHelpBlock\" class=\"form-text text-muted\">Enter the last day of the week (Saturday)</span></div></div><div class=\"form-group row\"><label for=\"reportcomment\" class=\"col-4 col-form-label\">Comment</label> <div class=\"col-8\"><textarea id=\"reportcomment\" name=\"reportcomment\" cols=\"40\" rows=\"5\" required=\"required\" class=\"form-control\"></textarea></div></div> <div class=\"form-group row\"><div class=\"offset-4 col-8\"><button id=\"submitbtn\" name=\"submit\" type=\"submit\" class=\"btn btn-primary\">Submit</button></div></div></form><br /><div id=\"clist\"><i>Loading contribution list...</i></div><script>getContributionList()</script>";
} else if ($action == "processadd") {
	$week = $_POST['week'];
	$comment = $_POST['reportcomment'];
	$proofreader = GetUserID($_COOKIE['username']);
	$pagecontent = SubmitReport($week, $comment, $proofreader);
	$page = "Submit weekly report";
} else if ($action == "getconlist") {
	if (date('N', strtotime($_GET['endate'])) == 6) {
		echo GetContributionsForReport($_GET['endate']);
		echo "<script>$(\":submit\").removeAttr(\"disabled\", true);</script>";
	} else {
		echo "The chosen date is not Saturday. <script>$(\":submit\").attr(\"disabled\", true);</script>";
	}
	exit;
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