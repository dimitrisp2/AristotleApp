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

// Below this line, this file holds all the crucial code of the app.
// Please do not edit anything if you don't know what you are doing

//////////////////////////
// GENERIC DB FUNCTIONS //
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

$user = require 'checkauth.php';
if ((isset($_COOKIE['username'])) && ($_COOKIE['username'] != $user) && (basename($_SERVER['PHP_SELF']) != "index.php")) {
	unset($_COOKIE['username']);
	unset($_COOKIE['code']);
	setcookie('username', null, -1);
	setcookie('code', null, -1);
	Header("Location: index.php");
	die();
} else if (isset($_COOKIE['username'])) {
	$hasaccess = CheckUserAccess($_COOKIE['username']);
	if ($hasaccess <= 0) {
		unset($_COOKIE['username']);
		unset($_COOKIE['code']);
		setcookie('username', null, -1);
		setcookie('code', null, -1);
		header("Location: error.php?i=".$hasaccess);
		die();
	}
} else if ((!isset($_COOKIE['username'])) && ((basename($_SERVER['PHP_SELF']) != "index.php") && (basename($_SERVER['PHP_SELF']) != "callback.php"))) {
	header("Location: error.php?i=-3");
	die();
} else {
	// All is cool.
}

// This will check if the user who tried to login, has any access level to this app, and return it.
function CheckUserAccess($username) {
	$username = mysqli_real_escape_string($GLOBALS['sqlcon'], $username);
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id`, `role` FROM `users` WHERE `username` = '".$username."';");
	if ($result) {
		$proofreaders = mysqli_num_rows($result);
		if ($proofreaders == 1) {
			$row = mysqli_fetch_assoc($result);
			return $row['role'];
		} else {
			return -1;
		}
	} else {
		//return -2;
		echo mysqli_error($GLOBALS['sqlconnect']);
	}
}

// This will be used to convert a PHP array to an options list.
// $arrayinput is generated from GetTranslatorsCSV(), GetProofreadersCSV() and GetProjectsCSV(). These functions may get merged in the future.
// $name will be used as the field's HTML name
// $selectedid will be used to mark a certain value as preselected
function ConvertArray2HTMLOptions($arrayinput, $seperator, $name, $selectedid = NULL) {
	$HTMLOptions = "<select name=\"". $name ."\" id=\"". $name ."\"  class=\"custom-select\">";
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
// Can be used to re-assign a project if needed.

function GetProjectsCSV() {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id`, `name` FROM `projects` ORDER BY `name` ASC");
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

// Get the ID of the user set in $username
function GetUserID($username) {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id` FROM `users` WHERE `username` = '" . $username . "'");
	if ($result) {
		$userfound = mysqli_num_rows($result);
		if ($userfound == 1) {			
			$row = mysqli_fetch_assoc($result);
			return $row['id'];
		} else {
			return "notfound";
		}
	} else {
		return "error";
	}
}

///////////////////
// CONTRIBUTIONS //
///////////////////

function GetContributionList($user = NULL, $project = NULL) {
	// prepare SQL action if either or both $user and $project are set.
	if ((!is_null($user)) || (!is_null($project))) {
		$sqlaction = "WHERE ";
		if (!is_null($user)) {
			$sqlaction = $sqlaction . "`translator` = " . $user . " ";
		} else if (!is_null($project)) {
			$sqlaction = $sqlaction . "`project` = " . $project . " ";
		}
	} else {
		$sqlaction = "";
	}
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `c`.`id` AS `cid`, `c`.`translator` AS `tid`, `c`.`proofreader` AS `pid`, `c`.`link` AS `contrlink`, `c`.`submit` AS `submitdate`, `c`.`review` AS `reviewdate`, `c`.`vote-utopian` AS `vote-utopian`, `p`.`name` AS `projectname`, `p`.`crowdin` AS `crowdinlink`, `u1`.`username` AS `translator`, `u2`.`username` AS `proofreader` FROM `contributions` AS `c` LEFT JOIN `users` AS `u1` on `c`.`translator` = `u1`.`id` LEFT JOIN `users` AS `u2` on `c`.`proofreader` = `u2`.`id` LEFT JOIN `projects` AS `p` ON `c`.`project` = `p`.`id` ORDER BY `c`.`submit` DESC");
	
	if ($result) {
		// Initialise an empty variable to store the content
		$contributionlist = "";
		// Get all projects
		while ($row = mysqli_fetch_assoc($result)) {
			// fields:
			// translator^v, proofreader^v, submitdate^v, reviewdate^v, vote-utopian^v
			// projectname^v, crowdinlink^v, contrlink^v, cid
			$translator = $row['translator'];
			$submit = date("d/m/Y", strtotime($row['submitdate']));
			$project = $row['projectname'];
			$contributionlink = $row['contrlink'];
			$crowdin = $row['crowdinlink'];
			if ($row['reviewdate'] == NULL) {
				$review = "Not yet";
			} else {
				$review = date("d/m/Y", strtotime($row['reviewdate'])) . " (".$row['proofreader'].")";
			}
			
			if ($row['vote-utopian'] == 0) {
				$voteutopian = "Not Voted";
			} else {
				$voteutopian = "Voted";
			}
			
			// Add the project to the list
			$contributionlist .= "<tr><td>".$project."</td><td>".$translator."</td><td>".$submit."</td><td>".$review."</td><td><a href=\"".$contributionlink."\" target=\"_blank\">Post</a> | <a href=\"".$crowdin."\" target=\"_blank\">CrowdIn</a></td><td>".$voteutopian."</td><td>(TBC)</td>";
		}
		return $contributionlist;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
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
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `p`.`id`, `p`.`name` AS `projectname`, `p`.`github` AS `github`, `p`.`crowdin` AS `crowdin`, `p`.`started` AS `started`, `p`.`proofreader` AS `proofreader`, `p`.`finished` AS `finished`, `u`.`username` AS `translatorname`, `u2`.`username` AS `proofreadername` FROM `projects` AS `p` LEFT JOIN `users` AS `u` ON `p`.`translator` = `u`.`id` LEFT JOIN `users` AS `u2` on `p`.`proofreader` = `u2`.`id` ".$sqlaction." ORDER BY `projectname` ASC");
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

// Submit new task to the database.
// Discord notifications could be added to enhance the user experience.
function SubmitNewTask($from, $to, $title, $msg, $project) {
	// Prepare the connection
	$stmt = mysqli_stmt_init($GLOBALS['sqlcon']);
	// Get submitted user's ($from) ID
	$fromid = GetUserID($from);
	// Prepare the statement and add all needed variables
	if (mysqli_stmt_prepare($stmt, 'INSERT INTO `tasks` (`project`, `user`, `recipient`, `title`, `message`, `resolved`) VALUES (?, ?, ?, ?, ?, 0)')) {
		mysqli_stmt_bind_param($stmt, "iiiss", $project, $fromid, $to, $title, $msg);
		$rvl = mysqli_stmt_execute($stmt);
		
		// Return if true or false to inform the user if it was a success.
		if ($rvl) {
			return "Task has been added to the database successfully";
		} else {
			return "Error adding the Task to the database. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";
		}
	}	
}

function SubmitReplyTask($from, $msg, $taskid) {
	// Prepare the connection
	$stmt = mysqli_stmt_init($GLOBALS['sqlcon']);
	// Get submitted user's ($from) ID
	$fromid = GetUserID($from);
	// Prepare the statement and add reply to the database
	if (mysqli_stmt_prepare($stmt, 'INSERT INTO `taskmsg` (`user`, `parentid`, `message`) VALUES (?, ?, ?)')) {
		mysqli_stmt_bind_param($stmt, "iis", $fromid, $taskid, $msg);
		$rvl = mysqli_stmt_execute($stmt);
		
		// Return if true or false to inform the user if it was a success.
		if ($rvl) {
			return "Task reply has been added to the database successfully. <a href=\"tasks.php?a=view&i=".$taskid."\">Return to the thread</a>";
		} else {
			return "Error adding your reply to the database. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";
		}
	} else {
		return "Error adding your reply to the database. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";		
	}
}

function SubmitCompleteTask($task, $iscomplete) {
	// Prepare the connection
	$stmt = mysqli_stmt_init($GLOBALS['sqlcon']);
	// Prepare the statement and add reply to the database
	if (mysqli_stmt_prepare($stmt, 'UPDATE `tasks` SET `resolved` = ? WHERE `id` = ?')) {
		mysqli_stmt_bind_param($stmt, "ii", $iscomplete, $task);
		$rvl = mysqli_stmt_execute($stmt);
		
		// Return if true or false to inform the user if it was a success.
		if ($rvl) {
			return "Task has been marked as completed. You can keep adding replies if you want to.";
		} else {
			return "Error marking the task as completed. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";
		}
	} else {
		return "Error marking the task as completed. Please <a href=\"javascript:history.back()\">return to the previous page</a> and try again. If the problem persists, contact <b>dimitrisp</b> on the DaVinci Discord server.";
	}	
}

function SubmitIncompleteTask() {
	
}

function showanddie($errortoshow) {
	echo $errortoshow;
	closeSQL();
	//include "templates/common-foot.tpl";
	die();
}

///////////
// USERS //
///////////

function GetAllUsers() {
	// Prepare the MySQL-depended action into a variable that will be used on the query

	// Fetch all tasks that fullfil our criteria
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT * FROM `users` ORDER BY `username` ASC");
	
	if ($result) {
		// Initialise an empty variable to store the content
		$users = "";
		// Get all tasks
		while ($row = mysqli_fetch_assoc($result)) {
			//print_r($row);
			if ($row['role'] == 1) {
				$role = "Translator";
			} else if ($row['role'] == 2) {
				$role = "Proofreader";
			} else if ($row['role'] == 3) {
				$role = "Translator & Proofreader";
			} else {
				$role = "No longer member";
			}
			$users .= "<tr><td>".$row['username']."</td><td>".$role."</td><td>".$row['hired']."</td><td>".$row['dismissed']."</td><td></a></td>";
		}
		return $users;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
	}
}

function GetMainPageContent() {
	if (isset($_COOKIE['username'])) {
		$hasaccess = CheckUserAccess($_COOKIE['username']);
	} else {
		$hasaccess = 0;
	}
	if ($hasaccess > 0) {
		return "Welcome, " . $_COOKIE['username'] . ". You are already logged in, and you are registered as a member with access to the app, so feel free to stick around.";
	} else {
		return "This app is only intended for use by the <?php echo $teamname; ?> Translation Team. You need to login before you proceed to use anything in this app!<br /><a href=\"https://steemconnect.com/oauth2/authorize?client_id=aristotle.app&redirect_uri=". $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']) . "/callback.php\"; ?>&scope=login\" class=\"font-weight-bold\">Secure login via SteemConnect</a>";
	}
	
}

function GetMenu() {
	if (isset($_COOKIE['username'])) {
		$hasaccess = CheckUserAccess($_COOKIE['username']);
	} else {
		$hasaccess = 0;
	}
	if ($hasaccess > 0) {
		return "<button type=\"button\" class=\"btn btn-secondary dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Welcome ".$_COOKIE['username']."</button><div class=\"dropdown-menu dropdown-menu-right\"><a class=\"btn dropdown-item\" href=\"logout.php\">Logout</a></div>";
	} else {
		return "<button type=\"button\" class=\"btn btn-secondary dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Welcome, Guest</button><div class=\"dropdown-menu dropdown-menu-right\"><a class=\"btn dropdown-item\" href=\"https://steemconnect.com/oauth2/authorize?client_id=aristotle.app&redirect_uri=".  $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']) . "/callback.php&scope=login\">Login via SteemConnect</a></div>";
	}
}

function LogOut() {
	unset($_COOKIE['username']);
	unset($_COOKIE['code']);
	setcookie('username', null, -1);
	setcookie('code', null, -1);
	Header("Location: index.php");
}

function closeSQL() {
	global $sqlcon;
	mysqli_close($sqlcon);
}
?>
