<?php
$page = "Contribution Posts";
include("functions.php");
if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = "list";
}

if ($action == "list") {
	$pagecontent = GetContributionList();
} else if ($action == "filter") {
	//print_r($_POST);
	$translator = NULL;
	$project = NULL;
	$fromdate = NULL;
	$todate = NULL;
	$voteutopian = NULL;
	$reviewed = NULL;
	
	if ((isset($_POST['translator'])) && ($_POST['translator'] != "na")) { $translator = $_POST['translator']; }
	if ((isset($_POST['project'])) && ($_POST['project'] != "na")) { $project = $_POST['project']; }
	if ((isset($_POST['fromdate'])) && ($_POST['fromdate'] != NULL)) { $fromdate = $_POST['fromdate']; }
	if ((isset($_POST['todate'])) && ($_POST['todate'] != NULL)) { $todate = $_POST['todate']; }
	if ((isset($_POST['vote-utopian'])) && ($_POST['vote-utopian'] != "na")) { $voteutopian = $_POST['vote-utopian']; }
	if ((isset($_POST['reviewed'])) && ($_POST['reviewed'] != "na")) { $reviewed = $_POST['reviewed']; }
	$page = "Contribution Search";
	
	$pagecontent = GetContributionList($translator, $project, $fromdate, $todate, $voteutopian, NULL, $reviewed);
} else if ($action == "add") {
	$page = "Add Contribution";
	
	// Do a basic check on the provided url, to check if it is eligible for adding to the DB
	// If it is a basic steemit.com utopian link, IsSteemLink() returns an array
	// Otherwise, returns false
	$returndata = IsSteemLink($_POST['steemlink']);
	
	// First check, if the link is an actual Steemit link, check if it was already added.
	if (is_array($returndata)) {
		$isadded = CheckSteemLinkDB($_POST['steemlink']);
	}

	if ($isadded == TRUE) {
		$pagecontent = "The contribution was already in the database!";
	} else if ($isadded == FALSE) {
		if(($_POST['steemlink'] != NULL) && (filter_var($_POST['steemlink'], FILTER_VALIDATE_URL) !== false) && is_array($returndata)) {
			$parselink = ParseSteemLink($returndata);
			if (CheckUserAccess($parselink['author']) > 0) {
				$steemlink = $_POST['steemlink'];
				if (is_array($parselink)) {
					$pagecontent = "<i>Please verify if the contribution details are correct and press \"Submit\" to continue</i>";
					$author = $parselink['author'];
					$partno = $parselink['part'];
					$wordcount = $parselink['words'];
					$projectname = $parselink['project'];
					$submitdate = $parselink['time'];
				} else {
					$pagecontent = "<i>Unable to parse the post. Please try again later or fill the details below and press \"Submit\" to continue</i>";
					$author = "";
					$partno = "";
					$wordcount = "";
					$projectname = "";
					$submitdate = "";
				}
				//print_r($parselink);
				$pagecontent .= "<form action=\"contributions.php?a=processadd\" method=\"post\"><div class=\"form-group row\"><label for=\"author\" class=\"col-4 col-form-label\">Author</label> <div class=\"col-8\"><input id=\"author\" name=\"author\" type=\"text\" aria-describedby=\"authorHelpBlock\" required=\"required\" class=\"form-control here\" value=\"$author\"> <span id=\"authorHelpBlock\" class=\"form-text text-muted\">Steem Username without @</span></div></div><div class=\"form-group row\"><label for=\"partno\" class=\"col-4 col-form-label\">Part Number</label> <div class=\"col-8\"><input id=\"partno\" name=\"partno\" placeholder=\"20\" type=\"text\" required=\"required\" class=\"form-control here\" value=\"$partno\"></div></div><div class=\"form-group row\"><label for=\"wordcount\" class=\"col-4 col-form-label\">Word Count</label> <div class=\"col-8\"><input id=\"wordcount\" name=\"wordcount\" placeholder=\"1251\" type=\"text\" required=\"required\" class=\"form-control here\" value=\"$wordcount\"></div></div><div class=\"form-group row\"><label for=\"projectname\" class=\"col-4 col-form-label\">Project</label> <div class=\"col-8\"><input id=\"projectname\" name=\"projectname\" placeholder=\"ReactOS\" type=\"text\" required=\"required\" class=\"form-control here\" value=\"$projectname\"></div></div> <div class=\"form-group row\"><div class=\"offset-4 col-8\"><button name=\"submit\" type=\"submit\" class=\"btn btn-primary\">Submit</button></div></div><input type=\"hidden\" id=\"steemlink\" name=\"steemlink\" value=\"".$steemlink."\"><input type=\"hidden\" id=\"created\" name=\"created\" value=\"".$submitdate."\"></form>";
			} else {
				print_r($parselink);
				$pagecontent = "The contribution you've tried to submit was not made by a member of the ".$teamname." Team. This contribution couldn't be added to the database. If you believe this is an error, please contact an administrator";
			}
		} else {
			$pagecontent = "The input is not a valid link, or is empty. Please try again by specifying a Steemit contribution link.";
		}
	} else {
		//echo "Couldn't check";
		$pagecontent = "Unable to check. Please try again later.";
	}

} else if ($action == "processadd") {
	$projectid = GetProjectID($_POST['projectname']);
	$userid = GetUserID($_POST['author']);
	$pagecontent = AddContribution($projectid, $userid, $_POST['steemlink'], $_POST['created'], $_POST['partno'], $_POST['wordcount']);
} else {
	
}
include("common/head.php");
?>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
				<p><a href="#" data-toggle="modal" data-target="#searchfilters">Filter Results</a> | <a href="#" data-toggle="modal" data-target="#addcontribution">Add Contribution</a></p>
                <?php echo $pagecontent; ?>
            </div>
        </div>
    </div>

<?php	
$projectcsv = GetProjectsCSV();
$projecthtml = ConvertArray2HTMLOptions(explode(",", $projectcsv), "#", "project", NULL, TRUE);
$tcsv = GetTranslatorsCSV();
$thtml = ConvertArray2HTMLOptions(explode(",", $tcsv), "#", "translator", NULL, TRUE);
?>
<!-- Modal Search Filters-->
<div id="searchfilters" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Filter Results</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p><form action="contributions.php?a=filter" method="post">
		  <div class="form-group row">
			<label for="translator" class="col-4 col-form-label">Translator</label> 
			<div class="col-8">
			  <?php echo $thtml; ?>
			</div>
		  </div>
		  <div class="form-group row">
			<label for="project" class="col-4 col-form-label">Project</label> 
			<div class="col-8">
			  <?php echo $projecthtml;?>
			</div>
		  </div>
		  <div class="form-group row">
			<label for="fromdate" class="col-4 col-form-label">From</label> 
			<div class="col-8">
			  <input id="fromdate" name="fromdate" type="text" aria-describedby="fromdateHelpBlock" class="form-control here"> 
			  <span id="fromdateHelpBlock" class="form-text text-muted">Format: YYYY-MM-DD</span>
			</div>
		  </div>
		  <div class="form-group row">
			<label for="todate" class="col-4 col-form-label">To</label> 
			<div class="col-8">
			  <input id="todate" name="todate" type="text" aria-describedby="todateHelpBlock" class="form-control here"> 
			  <span id="todateHelpBlock" class="form-text text-muted">Format: YYYY-MM-DD</span>
			</div>
		  </div>
		  <div class="form-group row">
			<label for="vote-utopian" class="col-4 col-form-label">Voted (Utopian)</label> 
			<div class="col-8">
			  <select id="vote-utopian" name="vote-utopian" class="custom-select">
				<option value="na">N/A</option>
				<option value="y">Yes</option>
				<option value="n">No</option>
			  </select>
			</div>
		  </div>
		  <div class="form-group row">
			<label for="reviewed" class="col-4 col-form-label">Reviewed</label> 
			<div class="col-8">
			  <select id="reviewed" name="reviewed" class="custom-select">
				<option value="na">N/A</option>
				<option value="y">Yes</option>
				<option value="n">No</option>
			  </select>
			</div>
		  </div> 
		</p>
      </div>
      <div class="modal-footer">
		<button name="submit" type="submit" class="btn btn-primary">Submit</button></form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- Modal Add Contribution -->
<div id="addcontribution" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add Contribution</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p><form action="contributions.php?a=add" method="post">
		  <div class="form-group row">
			<label for="steemlink" class="col-4 col-form-label">Steemit.com Link</label> 
			<div class="col-8">
			  <input id="steemlink" name="steemlink" type="text" aria-describedby="todateHelpBlock" class="form-control here"> 
			  <span id="steemlinkHelpBlock" class="form-text text-muted">https://steemit.com/utopian-io/@username/post</span>
			</div>
		  </div>
		</p>
      </div>
      <div class="modal-footer">
		<button name="submit" type="submit" class="btn btn-primary">Submit</button></form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>


<?php
include("common/foot.php");
?>


