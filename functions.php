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
////////////////////////////////////////////////////////////


// require_once(dirname(__FILE__)."/config.php");


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

//////////////
// PROJECTS //
//////////////

function GetAllProjects($action) {
	
	switch ($action) {
		case "progress":
			$sqlaction = "WHERE `started` IS NOT NULL AND `finished` IS NULL ";
			break;
		case "finished":		
			$sqlaction = "WHERE `started` IS NOT NULL AND `finished` IS NOT NULL ";
			break;
		case "wait":
			$sqlaction = "WHERE `started` IS NULL AND `finished` IS NULL ";
			break;
		default:
			$sqlaction = "";
			break;
	}
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `p`.`id`, `p`.`name` AS `projectname`, `p`.`github` AS `github`, `p`.`crowdin` AS `crowdin`, `p`.`started` AS `started`, `p`.`finished` AS `finished`, `u`.`username` AS `translatorname`, `u2`.`username` AS `proofreadername` FROM `projects` AS `p` JOIN `users` AS `u` ON `p`.`translator` = `u`.`id` JOIN `users` AS `u2` on `p`.`proofreader` = `u2`.`id` ".$sqlaction." ORDER BY `projectname` ASC");
	if ($result) {
		$allprojects = "";
		while ($row = mysqli_fetch_assoc($result)) {
			//print_r($row);
			if ($row['finished'] == NULL) {
				$finished = "Not yet";
			} else {
				$finished = date("d/m/Y", strtotime($row['finished']));
			}
			
			if ($row['started'] == NULL) {
				$started = "Not yet";
			} else {
				$started = date("d/m/Y", strtotime($row['started']));
			}
			$allprojects .= "<tr><td>".$row['projectname']."</td><td>".$row['translatorname']."</td><td>".$row['proofreadername']."</td><td>".$started."</td><td>".$finished."</td><td><a href=\"projects.php?a=view&i=".$row['id']."\"><i class=\"tiny material-icons\">remove_red_eye</i></a><a href=\"projects.php?a=mark&w=1&i=".$row['id']."\"><i class=\"tiny material-icons text-success\">spellcheck</i></a></td>";
		}
		return $allprojects;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
	}
}


///////////
// TASKS //
///////////


function GetAllTasks() {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT * FROM `tasks` ORDER BY `id` ASC");
	
	if ($result) {
		$alltasks = "";
		while ($row = mysqli_fetch_assoc($result)) {
			//print_r($row);
			$alltasks[$row['id']] = $row;
		}
		return $alltasks;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
	}
}

function GetDoneTasks() {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT * FROM `tasks` WHERE AND `resolved` = 1 ORDER BY `id` ASC");
	
	if ($result) {
		$donetasks = "";
		while ($row = mysqli_fetch_assoc($result)) {
			//print_r($row);
			$donetasks[$row['id']] = $row;
		}
		return $donetasks;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
	}
}

function GetTaskReplies($taskid) {
	echo "Task id: " . $taskid;
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT * FROM `taskmsg` WHERE `parentid` = ". $taskid ." ORDER BY `id` ASC");
	
	if ($result) {
		$replies = "";
		while ($row = mysqli_fetch_assoc($result)) {
			print_r($row);
		}
		return $replies;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
	}
}

function GetPendingTasks() {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `t`.`id`, `t`.`project`, `t`.`user`, `t`.`title` AS `title`, `t`.`submitted` AS `submitted`, `u`.`username` AS `username`, `p`.`name` AS `projectname` FROM `tasks` AS `t` JOIN `users` AS `u` ON `t`.`user` = `u`.`id` JOIN `projects` AS `p` on `t`.`project` = `p`.`id` WHERE `resolved` = 0 ORDER BY `id` ASC");
	
	if ($result) {
		$pendingtasks = "";
		while ($row = mysqli_fetch_assoc($result)) {
			//print_r($row);
			$pendingtasks .= "<tr><td>".$row['projectname']."</td><td>".$row['username']."</td><td>".$row['title']."</td><td>".$row['submitted']."</td><td><a href=\"tasks.php?a=view&i=".$row['id']."\"><i class=\"tiny material-icons\">remove_red_eye</i></a><a href=\"tasks.php?a=mark&w=1&i=".$row['id']."\"><i class=\"tiny material-icons text-success\">spellcheck</i></a></td>";
		}
		return $pendingtasks;
	} else {
		// Error running the query. Return error.
		echo "unexpectederror";
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
