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
	
	$pagecontent = GetContributionList($translator, $project, $fromdate, $todate, $voteutopian, $reviewed);
	
} else {
	
}
include("common/head.php");
?>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
				<a href="#" data-toggle="modal" data-target="#searchfilters">Filter Results</a>
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
<!-- Modal -->
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
<?php
include("common/foot.php");
?>


