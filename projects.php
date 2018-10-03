<?php
$page = "Projects";
include("functions.php");
include("common/head.php");
if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = NULL;
}
$projects = GetAllProjects($action);
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