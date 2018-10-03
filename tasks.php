<?php
$page = "Tasks";
include("functions.php");
include("common/head.php");
if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = NULL;
}
$pending = GetTasks($action);
?>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <p class="lead">Refine view: <a href="tasks.php?a=showall">All</a> | <a href="tasks.php">In Progress</a> | <a href="tasks.php?a=resolved">Resolved</a></p>
				  <table class="table table-striped table-hover">
					<thead>
					  <tr>
						<th>Project</th>
						<th>User</th>
						<th>Title</th>
						<th>Date</th>
						<th></th>
					  </tr>
					</thead>
					<tbody>
						<?php echo $pending; ?>
					</tbody>
				  </table>
            </div>
        </div>
    </div>
<?php
//print_r($pending);
include("common/foot.php");
?>