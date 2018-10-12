<?php

$teamname = "Greek";

//////////////////
// MySQL Config //
//////////////////

// MySQL Server
$sqlserver = "localhost";
// MySQL Username
$sqluser = "root";
// MySQL Password
$sqlpass = "";
// MySQL DB
$sqldb = "translator";

////////////////////////////////////////////////////////////
// 						functions.php					  //
// Contains all the functions that interact with MySQL DB //
//														  //
//														  //
// DO NOT EDIT BELOW THIS LINE IF YOU DON'T KNOW WHAT YOU //
// 						  ARE DOING						  //
//														  //
// DO NOT EDIT BELOW THIS LINE IF YOU DON'T KNOW WHAT YOU //
// 						  ARE DOING						  //
//														  //
// DO NOT EDIT BELOW THIS LINE IF YOU DON'T KNOW WHAT YOU //
// 						  ARE DOING						  //
//														  //
// DO NOT EDIT BELOW THIS LINE IF YOU DON'T KNOW WHAT YOU //
// 						  ARE DOING						  //
//														  //
// DO NOT EDIT BELOW THIS LINE IF YOU DON'T KNOW WHAT YOU //
// 						  ARE DOING						  //
//														  //
// DO NOT EDIT BELOW THIS LINE IF YOU DON'T KNOW WHAT YOU //
// 						  ARE DOING						  //
//														  //
// DO NOT EDIT BELOW THIS LINE IF YOU DON'T KNOW WHAT YOU //
// 						  ARE DOING						  //
//														  //
////////////////////////////////////////////////////////////

//////////////////////////
//						//
// GENERIC DB FUNCTIONS //
//						//
//////////////////////////


function openSQL() {
	$GLOBALS['sqlcon'] = mysqli_connect($GLOBALS['sqlserver'], $GLOBALS['sqluser'], $GLOBALS['sqlpass'], $GLOBALS['sqldb']);
	if (mysqli_connect_error()) {
		$logMessage = 'MySQL Error: ' . mysqli_connect_error();
		// Call your logger here.
		showanddie('Could not connect to the database');
	} else {
		mysqli_set_charset($GLOBALS['sqlcon'],"utf8");
	}
}

openSQL();

// This will be used to convert a PHP array to an options list.
// $arrayinput is generated from GetTranslatorsCSV(), GetProofreadersCSV() and GetProjectsCSV(). These functions may get merged in the future.
// $name will be used as the field's HTML name
// $selectedid will be used to mark a certain value as preselected
function ConvertArray2HTMLOptions($arrayinput, $seperator, $name, $selectedid = NULL) {
	$HTMLOptions = "<select name=\"". $name ."\" id=\"". $name ."\">  class=\"form-control\"";
	foreach ($arrayinput as $item) {
		$thisitem = explode($seperator, $item);
		if ((!is_null($selectedid) || $selectedid != 0) AND ($thisitem['0'] == $selectedid)) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$HTMLOptions .= "<option value=\"".$thisitem['0']."\" ".$selected.">".$thisitem['1']."</option>";
	}
	$HTMLOptions .= "</select>";
	return $HTMLOptions;
}

// Get a CSV list of the translators to be manipulated as needed throughout the Aristotle App
function GetTranslatorsCSV() {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id`, `username` FROM `users` WHERE `role` = 1 OR `role` = 3 ORDER BY `username` ASC");
	if ($result) {
		$translatorlist = "";
		$translators = mysqli_num_rows($result);
		$i = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$i++;
			if ($i < $translators) {
				$translatorlist .= $row['id'] . "#" . $row['username'] . ",";
			} else {
				$translatorlist .= $row['id'] . "#" . $row['username'];
			}
		}
		return $translatorlist;
	}
}

// Get a CSV list of the proofreaders to be manipulated as needed throughout the Aristotle App
function GetProofreadersCSV() {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id`, `username` FROM `users` WHERE `role` = 2 OR `role` = 3 ORDER BY `username` ASC");
	if ($result) {
		$proofreaderlist = "";
		$proofreaders = mysqli_num_rows($result);
		$i = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$i++;
			if ($i < $proofreaders) {
				$proofreaderlist .= $row['id'] . "#" . $row['username'] . ",";
			} else {
				$proofreaderlist .= $row['id'] . "#" . $row['username'];
			}
		}
		return $proofreaderlist;
	}	
}

// Get a CSV list of the projects to be manipulated as needed throughout the Aristotle App. 
// Will only get projects without a translator, by design, to avoid re-assigning a project by mistake.
// An "Edit Translator" functionality should/will be added to re-assign a project if needed.
function GetProjectsCSV() {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id`, `name` FROM `projects` WHERE `translator` = 0 ORDER BY `name` ASC");
	if ($result) {
		$projectlist = "";
		$projects = mysqli_num_rows($result);
		$i = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$i++;
			if ($i < $projects) {
				$projectlist .= $row['id'] . "#" . $row['name'] . ",";
			} else {
				$projectlist .= $row['id'] . "#" . $row['name'];
			}
		}
		return $projectlist;
	}	
}


///////////
// LOGIN //
///////////

// Get SteemConnect details and submit them to the DB for checking in the future. We will only store 1 authkey and expiration date.
function SubmitCookie2DB($access_token, $username, $expires_in	) {
	$stmt = mysqli_stmt_init($GLOBALS['sqlcon']);
	$exp = date('Y-m-d H:i:s', time() + $expires_in);
	if (mysqli_stmt_prepare($stmt, 'UPDATE `users` SET `authkey` = ?, `expiresin` = ? WHERE `username` = ?')) {
		mysqli_stmt_bind_param($stmt, "sss", $access_token, $exp, $username);
		$rvl = mysqli_stmt_execute($stmt);
	}
}

//////////////
// PROJECTS //
//////////////

// Get a list of projects depending on what "$action" is set as.
function GetAllProjects($action) {
	// Prepare the MySQL-depended action into a variable that will be used on the query
	switch ($action) {
		// Get only non-finished projects
		case "progress":
			$sqlaction = "WHERE `started` IS NOT NULL AND `finished` IS NULL ";
			break;
		// Get only finished projects
		case "finished":		
			$sqlaction = "WHERE `started` IS NOT NULL AND `finished` IS NOT NULL ";
			break;
		// Get only non-started projects
		case "wait":
			$sqlaction = "WHERE `started` IS NULL AND `finished` IS NULL ";
			break;
		// Get all projects
		default:
			$sqlaction = "";
			break;
	}
	
	// Fetch all projects that fullfil our criteria
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `p`.`id`, `p`.`name` AS `projectname`, `p`.`github` AS `github`, `p`.`crowdin` AS `crowdin`, `p`.`started` AS `started`, `p`.`proofreader` AS `proofreader`, `p`.`finished` AS `finished`, `u`.`username` AS `translatorname`, `u2`.`username` AS `proofreadername` FROM `projects` AS `p` JOIN `users` AS `u` ON `p`.`translator` = `u`.`id` JOIN `users` AS `u2` on `p`.`proofreader` = `u2`.`id` ".$sqlaction." ORDER BY `projectname` ASC");
	if ($result) {
		// Initialise an empty variable to store the content
		$allprojects = "";
		// Get all projects
		while ($row = mysqli_fetch_assoc($result)) {
			// Finished/non-finished project field wording & icon customisation
			if ($row['finished'] == NULL) {
				$finished = "Not yet";
				$finishlink = "<a href=\"projects.php?a=mark&w=1&i=".$row['id']."\"><i class=\"tiny material-icons text-success\">spellcheck</i></a>";
			} else {
				$finished = date("d/m/Y", strtotime($row['finished']));
				$finishlink = "";
			}
			
			// Started/not-started project field wording & icon customisation
			if ($row['started'] == NULL) {
				$started = "Not yet";
			} else {
				$started = date("d/m/Y", strtotime($row['started']));
			}
			
			if ($row['proofreader'] == 0) {
				// If there is no proofreader, add an assign link and hide the "mark as finished link" that was added earlier
				$assignlink = "<a href=\"projects.php?a=prepare-assign&i=".$row['id']."\"><i class=\"tiny material-icons\">assignment_ind</i></a>&nbsp;";
				$finishlink = "";
			} else {
				// If there is a proofreader, hide the "assignlink"
				$assignlink = "";
			}
			
			// Add the project to the list
			$allprojects .= "<tr><td>".$row['projectname']."</td><td>".$row['translatorname']."</td><td>".$row['proofreadername']."</td><td>".$started."</td><td>".$finished."</td><td>".$assignlink."<a href=\"projects.php?a=view&i=".$row['id']."\"><i class=\"tiny material-icons\">remove_red_eye</i></a>&nbsp;".$finishlink."</td>";
		}
		return $allprojects;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
	}
}

// Assign a project to a translator and a proofreader
function AssignProject($project, $translator, $proofreader, $startdate = NULL) {
	// Prepare the connection
	$stmt = mysqli_stmt_init($GLOBALS['sqlcon']);
	if ($startdate == NULL) {
		// If there was no date set on the frontend, get current time and use it as a MySQL timestamp.
		$started = date('Y-m-d H:i:s', time());
	} else {
		// If there was a date set from the frontend, use it as the project's start date.
		$started = date('Y-m-d H:i:s', $startdate);
	}
	
	// Prepare the statement and add all needed variables
	if (mysqli_stmt_prepare($stmt, 'UPDATE `projects` SET `translator` = ?, `proofreader` = ?, `started` =? WHERE `id` = ?')) {
		mysqli_stmt_bind_param($stmt, "iisi", $translator, $proofreader, $started, $project);
		$rvl = mysqli_stmt_execute($stmt);
		
		// Return if true or false to inform the user if it was a success.
		if ($rvl) {
			return true;
		} else {
			return false;
		}
	}
}

// Mark a project's translation as complete
function ProjectMarkedComplete($project) {
	// Prepare the connection
	$stmt = mysqli_stmt_init($GLOBALS['sqlcon']);
	// Get current time to use as timestamp in the database
	$finishdate = date('Y-m-d H:i:s', time());
	
	// Prepare the statement and add all the needed variables
	if (mysqli_stmt_prepare($stmt, 'UPDATE `projects` SET `finished` = ? WHERE `id` = ?')) {
		mysqli_stmt_bind_param($stmt, "si", $finishdate, $project);
		$rvl = mysqli_stmt_execute($stmt);
		
		// Return if true or false to inform the user if it was a success.
		if ($rvl) {
			return true;
		} else {
			return false;
		}
	}
}
///////////
// TASKS //
///////////

// Get a list of tasks depending on what "$action" is set as.
function GetTasks($action) {
	// Prepare the MySQL-depended action into a variable that will be used on the query
	switch ($action) {
		// List all tasks
		case "showall":
			$sqlaction = "";
			break;
		// List of resolved-only tasks
		case "resolved":		
			$sqlaction = "WHERE `resolved` != 0 ";
			break;
		// List all non-resolved tasks. This is the default list
		default:
			$sqlaction = "WHERE `resolved` = 0 ";
			break;
	}
	// Fetch all tasks that fullfil our criteria
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `t`.`id`, `t`.`project`, `t`.`user`, `t`.`title` AS `title`, `t`.`submitted` AS `submitted`, `u`.`username` AS `username`, `p`.`name` AS `projectname` FROM `tasks` AS `t` JOIN `users` AS `u` ON `t`.`user` = `u`.`id` JOIN `projects` AS `p` on `t`.`project` = `p`.`id` ".$sqlaction."ORDER BY `id` ASC");
	
	if ($result) {
		// Initialise an empty variable to store the content
		$tasks = "";
		// Get all tasks
		while ($row = mysqli_fetch_assoc($result)) {
			//print_r($row);
			$tasks .= "<tr><td>".$row['projectname']."</td><td>".$row['username']."</td><td>".$row['title']."</td><td>".$row['submitted']."</td><td><a href=\"tasks.php?a=view&i=".$row['id']."\"><i class=\"tiny material-icons\">remove_red_eye</i></a><a href=\"tasks.php?a=mark&w=1&i=".$row['id']."\"><i class=\"tiny material-icons text-success\">spellcheck</i></a></td>";
		}
		return $tasks;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
	}
}

// Get Single Task with all the replies
function GetTask($taskid) {
	// Initialise an empty variable to store the content
	$replies = "";
	// Get the thread details and original message
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `t`.`submitted` AS `submitted`, `t`.`title` AS `title`, `t`.`message` AS message, `p`.`name` AS `projectname`, `u1`.`username` AS `sender` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `t`.`project` = `p`.`id` JOIN `users` AS `u1` ON `t`.`user` = `u1`.`id` WHERE `t`.`id` = ". $taskid .";");
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
			$replies .= "<h3 class=\"text-center\">Task ".$row['title']."</h3><div class=\"col-lg-12 border\"> by <a href=\"https://steemit.com/@" . $row['sender'] . "\">" . $row['sender'] . "</a> - Date: " . $row['submitted'] . "<hr />" . $row['message'] . "</div>";
		}
	} else {
		return "Error fetching the Task's thread. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";
	}
	
	// Get all the replies to the thread
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `t`.`message` AS `message`, `t`.`submitted` AS `submitted`, `u1`.`username` AS `sender` FROM `taskmsg` AS `t` JOIN `users` AS `u1` ON `t`.`user` = `u1`.`id` WHERE `t`.`parentid` = ". $taskid ." ORDER BY `t`.`id` ASC");	
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
			//print_r($row);
			$replies .= "<br /><div class=\"col-lg-12 border\"> by <a href=\"https://steemit.com/@" . $row['sender'] . "\">" . $row['sender'] . "</a> - Date: " . $row['submitted'] . "<hr />" . $row['message'] . "</div>";
		}
		return $replies;
	} else {
		// Error running the query. Return error.
		return "Error fetching the Task's thread. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";
	}
}

function SubmitNewTask() {
	
}

function SubmitEditTask() {
	
}

function SubmitCompleteTask() {
	
}

function SubmitIncompleteTask() {
	
}

function showanddie($errortoshow) {
	echo $errortoshow;
	closeSQL();
	//include "templates/common-foot.tpl";
	die();
}

//closeSQL();

function closeSQL() {
	global $sqlcon;
	mysqli_close($sqlcon);
}
?>