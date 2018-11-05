<?php
$page = "Projects";
include("functions.php");

// Set Current page access level, and check if user has access
$currentacl = FOR_TRANSLATORS;
CheckPageAccess();

if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = "all";
}

if ($action == "mark") {
	If (ProjectMarkedComplete($_GET['i'])) {
		$pagecontent = "Action done";
	} else {
		$pagecontent = "Error occured finishin action";
	}
	//header("Location: projects.php");
	//die();
} else if ($action == "prepare-assign") {
	if (isset($_GET['selected'])) {
		$selected = $_GET['selected'];
	} else {
		$selected = "0";
	}
	$pagecontent = "<h3 class=\"text-center\">Assign Translator and Proofreader to Project</h3><br /><hr /><form action=\"projects.php?a=commit-assign\" method=\"post\" class=\"offset-sm-4\">";
	$projectcsv = GetProjectsCSV();
	$projecthtml = ConvertArray2HTMLOptions(explode(",", $projectcsv), "#", "project", $selected);
	$pagecontent .= "<div class=\"form-group row\"><label for=\"project\" class=\"col-3 col-form-label\">Project</label> <div class=\"col-5\">$projecthtml</div></div> ";
	$tcsv = GetTranslatorsCSV();
	$thtml = ConvertArray2HTMLCheckbox(explode(",", $tcsv), "#", "translator");
	$pagecontent .= "<div class=\"form-group row\"><label for=\"translator\" class=\"col-3 col-form-label\">Translator</label> <div class=\"col-5\">$thtml</div></div> ";
	$proofcsv = GetProofreadersCSV();
	$proofhtml = ConvertArray2HTMLCheckbox(explode(",", $proofcsv), "#", "proofreader");
	$pagecontent .= "<div class=\"form-group row\"><label for=\"proofreader\" class=\"col-3 col-form-label\">Proofreader</label> <div class=\"col-5\">$proofhtml</div></div> ";
	$pagecontent .= "<div class=\"form-group\"><button name=\"submit\" type=\"submit\" class=\"btn btn-primary offset-sm-2\">Submit</button></div>";
	$pagecontent .= "</form>";
} else if ($action == "commit-assign") {
	if (!isset($_POST['project']) || !isset($_POST['translator']) || !isset($_POST['proofreader']) || !intval($_POST['project']) || !intval($_POST['translator']) || !intval($_POST['proofreader'])) {
		$pagecontent = "Attempted to submit invalid data. Please try again. <a href=\"javascript:history.back()\">Return to the previous page.</a>";
	} else {
		$translators = ConvertArray2CSV($_POST['translator'], ",");
		echo $translators;
		$proofreaders = ConvertArray2CSV($_POST['proofreader'], ",");
		echo $proofreaders;
		$assignproject = AssignProject($_POST['project'], $translators, $proofreaders);
		//echo $assignproject;
		if ($assignproject == TRUE) {
			$pagecontent = "Project was assigned successfully! <a href=\"javascript:history.back()\">Return to the previous page.</a>";
		} else {
			$pagecontent = "There was an error while trying to assign the project. Please try again or contact <b>dimitrisp</b> on the DaVinci Discord. <a href=\"javascript:history.back()\">Return to the previous page.</a>";
		}
	}
} else if ($action == "view") {
	$page = "Contributions List";
	$pagecontent = GetContributionList(NULL, $_GET['i'], NULL, NULL, NULL, NULL, NULL, "project");
} else {
	$projects = GetAllProjects($action);
	$pagecontent = "<p class=\"lead\">Refine View: <a href=\"projects.php\">All</a> | <a href=\"projects.php?a=progress\">Being Translated</a> | <a href=\"projects.php?a=finished\">Finished</a> | <a href=\"projects.php?a=wait\">Not Started</a></p><table class=\"table table-striped table-hover\"><thead><tr><th>Project</th><th>Translator</th><th>Proofreader</th><th>Started</th><th>Finished</th><th></th></tr></thead><tbody>$projects</tbody></table>";
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
include("common/foot.php");
?>


