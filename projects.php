<?php
$page = "Projects";
include("functions.php");
if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = "all";
}

if ($action == "mark") {
	ProjectMarkedComplete($_GET['i']);
	header("Location: projects.php");
	die();
} else if ($action == "view") {
	
} else {
	$projects = GetAllProjects($action);
}

include("common/head.php");
?>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <p class="lead">Refine View: <a href="projects.php">All</a> | <a href="projects.php?a=progress">Being Translated</a> | <a href="projects.php?a=finished">Finished</a> | <a href="projects.php?a=wait">Not Started</a></p>
				  <table class="table table-striped table-hover">
					<thead>
					  <tr>
						<th>Project</th>
						<th>Translator</th>
						<th>Proofreader</th>
						<th>Started</th>
						<th>Finished</th>
						<th></th>
					  </tr>
					</thead>
					<tbody>
						<?php echo $projects; ?>
					</tbody>
				  </table>
            </div>
        </div>
    </div>
<?php
include("common/foot.php");
?>