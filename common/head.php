<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="custom.css">
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title><?php echo $page; ?> - Aristotle Team App</title>
	<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js" integrity="sha384-pjaaA8dDz/5BgdFUPX6M/9SUZv4d12SUPF0axWc+VRZkx5xU3daN+lYb49+Ax+Tl" crossorigin="anonymous"></script>
	<?php 
	if (basename($_SERVER['PHP_SELF']) == "tasks.php") {
	?>
		<!-- include summernote css/js -->
		<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.css" rel="stylesheet">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.js"></script>
	<?php 
	}
	?>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">
            <img src="assets/logo.png" width="30" height="30" class="d-inline-block align-top" alt=""> Aristotle
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-item nav-link active" href="index.php">Home</a>
				<?php
				// Check if the user is a CM, and don't give access to project/contributions/tasks
				if ($hasaccess != IS_STAFF && $hasaccess >= IS_TRANSLATOR) {
					?>
					<a class="nav-item nav-link" href="projects.php">Projects</a>
					<a class="nav-item nav-link" href="contributions.php">Contributions</a>
					<a class="nav-item nav-link" href="tasks.php">Tasks</a>
					<?php 
				}
				// Check if the user is a moderator or a CM, and give access to "Users" and "Weekly Reports"
				if ($hasaccess == IS_PROOFREADER || $hasaccess == IS_BOTH || $hasaccess == IS_STAFF) {
					?>
					<a class="nav-item nav-link" href="users.php">Users</a>
					<a class="nav-item nav-link" href="weeklyreports.php">W.Reports</a>
					<?php
				}
				?>
				<a class="nav-item nav-link active" href="https://steemit.com/utopian-io/@dimitrisp/aristotle-app-an-app-to-supplement-the-utopian-translation-teamwork" target="_blank">Steemit App Post</a>
				<div class="btn-group">
					<?php echo GetMenu(); ?>
				</div>
            </div>
        </div>
    </nav>
    <header>
        <div class="container text-center">
            <h1><?php echo $page; ?></h1>
        </div>
    </header>
