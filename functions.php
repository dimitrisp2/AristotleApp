<?php

// Your team name, will be shown in various places in-app.
$teamname = "Greek";

// Your language name as added in posts, will be used to parse the titles of Contributions in order to fetch the project name, part number and wordcount.
// For this to work correctly, titles must be in this format:
// Projectname $languagename Translation - Part XX (XXXX words)
$languagename = "Greek";

// Choose a contribution limit when no filters have been added.
// For teams with less than 5 translators, 30 contributions will contributions up to 2 weeks in the past by default.
$contlimit = 30;


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

///////////////
// Constants //
///////////////


// User access levels
const NO_SQL_CONNECTION = -2;
const DENY_ACCESS = -1;
const NO_ACCESS = 0;
const IS_TRANSLATOR = 1;
const IS_PROOFREADER = 2;
const IS_BOTH = 3;
const IS_STAFF = 4;

// Page access levels
const FOR_TRANSLATORS = 1;
const FOR_PROOFREADER = 2;
const FOR_STAFF_AND_LM = 4;
const FOR_ALL = 6;

// Error codes
const ERROR_GENERIC = 1;
const ERROR_KICKED_OUT = 0;
const ERROR_NOT_MEMBER = -1;
const ERROR_MYSQL = -2;
const ERROR_LOGIN = -3;
const ERROR_PERMISSIONS = -4;

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
	if ($hasaccess <= NO_ACCESS) {
		unset($_COOKIE['username']);
		unset($_COOKIE['code']);
		setcookie('username', null, -1);
		setcookie('code', null, -1);
		header("Location: error.php?i=".$hasaccess);
		die();
	}
} else if ((!isset($_COOKIE['username'])) && ((basename($_SERVER['PHP_SELF']) != "index.php") && (basename($_SERVER['PHP_SELF']) != "callback.php") && (basename($_SERVER['PHP_SELF']) != "error.php"))) {
	header("Location: error.php?i=-3");
	die();
} else {
	$hasaccess = DENY_ACCESS;
	// All is cool.
}

// This will check if the user who tried to login, has any access level to this app, and return it.
function CheckUserAccess($username) {
	$username = mysqli_real_escape_string($GLOBALS['sqlcon'], $username);
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id`, `role` FROM `users` WHERE `username` = '".$username."';");
	if ($result) {
		$userdet = mysqli_num_rows($result);
		if ($userdet == 1) {
			$row = mysqli_fetch_assoc($result);
			return $row['role'];
		} else {
			return DENY_ACCESS;
		}
	} else {
		return NO_SQL_CONNECTION;
		echo mysqli_error($GLOBALS['sqlconnect']);
	}
}

// Used to check if the user has access to the current page.
// Should be changed to something better.

function CheckPageAccess() {
	//echo $GLOBALS['currentaccesslevel'];
	$acl = $GLOBALS['currentacl'];
	$hasaccess = $GLOBALS['hasaccess'];

	$showerror = FALSE;
	
	// Basically, the following IF will not allow:
	// A translator to access pages marked with a level of FOR_PROOFREADER or bigger
	// A staff to access pages marked with a level FOR_PROOFREADER or lower
	// A person with NO_ACCESS, to access any page with ACL
	// Perhaps, this could be simplified in some way
	
	if (($acl != FOR_TRANSLATORS && $acl != FOR_ALL) && $hasaccess == IS_TRANSLATOR) {
		$showerror = TRUE;
	} else if (($acl != FOR_STAFF_AND_LM && $acl != FOR_ALL) && $hasaccess == IS_STAFF) {
		$showerror = TRUE;
	} else if ($hasaccess == NO_ACCESS) {
		$showerror = TRUE;
	}

	
	if ($showerror) {
		echo "You have no access";
		header("Location: error.php?i=-4");
		die();
	}
	
}

function ConvertArray2CSV($arrayinput, $seperator) {
	$arraycount = count($arrayinput);
	$thisarray = "";
	$i = 0;
	foreach ($arrayinput as $item) {
		$thisarray .= $item;
		$i++;
		if ($i != $arraycount) {
			$thisarray .= ",";
		}
	}
	return $thisarray;
}

// This will be used to convert a PHP array to an options list.
// $arrayinput is generated from GetTranslatorsCSV(), GetProofreadersCSV() and GetProjectsCSV(). These functions may get merged in the future.
// $name will be used as the field's HTML name
// $selectedid will be used to mark a certain value as preselected
function ConvertArray2HTMLOptions($arrayinput, $seperator, $name, $selectedid = NULL, $addempty = FALSE) {

	$HTMLOptions = "<select name=\"". $name ."\" id=\"". $name ."\"  class=\"custom-select\">";
	if ($addempty) {
		$HTMLOptions .= "<option value=\"na\">No Selection</option>";
	}
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

function ConvertArray2HTMLCheckbox($arrayinput, $seperator, $name) {
	$HTMLSelect = "";
	foreach ($arrayinput as $item) {
		$thisitem = explode($seperator, $item);
		$HTMLSelect .= "<input type=\"checkbox\" name=\"".$name."[]\" value=\"".$thisitem['0']."\">".$thisitem['1']."</input><br />";
	}
	
	return $HTMLSelect;	
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

// Get the User of the id set in $userid
function GetUsername($userid) {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `username` FROM `users` WHERE `id` = " . $userid);
	if ($result) {
		$userfound = mysqli_num_rows($result);
		if ($userfound == 1) {			
			$row = mysqli_fetch_assoc($result);
			return $row['username'];
		} else {
			return "notfound";
		}
	} else {
		return "error";
	}
}

// Get the ID of the project set in $project
function GetProjectID($project) {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id` FROM `projects` WHERE `name` = '" . $project . "'");
	if ($result) {
		$projectfound = mysqli_num_rows($result);
		if ($projectfound == 1) {			
			$row = mysqli_fetch_assoc($result);
			return $row['id'];
		} else {
			return FALSE;
		}
	} else {
		return "error";
	}
}


///////////////////
// CONTRIBUTIONS //
///////////////////

function AddContribution($project, $translator, $link, $created, $partno = NULL, $wordcount = NULL) {
	if (is_null($partno)) {
		$partno = 0;
	}
	
	if (is_null($wordcount)) {
		$wordcount = 0;
	}
	
	$stmt = mysqli_stmt_init($GLOBALS['sqlcon']);
	if (mysqli_stmt_prepare($stmt, 'INSERT INTO `contributions` (`project`, `translator`, `link`, `submit`, `partno`, `wordcount`) VALUES(?, ?, ?, ?, ?, ?)')) {
		mysqli_stmt_bind_param($stmt, "iissii", $project, $translator, $link, $created, $partno, $wordcount);
		$rvl = mysqli_stmt_execute($stmt);
		if ($rvl) {
			return "The contribution has been added successfully to the database";
		} else {
			return "Unable to add the contribution to the database. Please try again";
		}
	} else {
		return "error";
	}
}

function GetContributionList($user = NULL, $project = NULL, $from = NULL, $to = NULL, $voted = NULL, $reviewed = NULL, $proofreader = NULL, $title = NULL, $weeklyreport = NULL) {
	// $limit will be used if no other arguments are set.
	$limit = "";
	// prepare SQL action if any/all of the arguments are set.
	if ((!is_null($user)) || (!is_null($project)) || (!is_null($from)) || (!is_null($to)) || (!is_null($voted)) || (!is_null($reviewed))) {
		$sqlaction = "WHERE ";
	} else {
		// No arguments have been set, we need to set a limit of 30 contributions (in order to not overload the system)
		$sqlaction = "";
		$limit = " LIMIT " . $GLOBALS['contlimit'];
	}
	
	// Hack to show "AND" between two "WHERE" actions
	$showand = FALSE;
	
	if (!is_null($user)) {
		$sqlaction = $sqlaction . "`c`.`translator` = " . $user . " ";
		$showand = TRUE;
	} 
	
	if (!is_null($project)) {
		if ($showand == TRUE) {
			$sqlaction .= "AND ";
		}
		$showand = TRUE;
		$sqlaction = $sqlaction . "`c`.`project` = " . $project . " ";
	}
	
	if (!is_null($from)) {
		if ($showand == TRUE) {
			
		}
		$showand = TRUE;
		$sqlaction = $sqlaction . "`c`.`submit` >= '" . $from . "' ";
	}
	
	if (!is_null($to)) {
		if ($showand == TRUE) {
			$sqlaction .= "AND ";
		}
		$showand = TRUE;
		$sqlaction = $sqlaction . "`c`.`submit` <= '" . $to . "' ";
	}

	if (!is_null($voted)) {
		if ($showand == TRUE) {
			$sqlaction .= "AND ";
		}
		$showand = TRUE;
		$sqlaction = $sqlaction . "`c`.`vote-utopian` = " . $voted . " ";
	}
	
	if (!is_null($proofreader)) {
		if ($showand == TRUE) {
			$sqlaction .= "AND ";
		}
		$showand = TRUE;
		$sqlaction = $sqlaction . "`c`.`proofreader` = " . $proofreader . " ";
	}

	if (!is_null($reviewed)) {
		if ($showand == TRUE) {
			$sqlaction .= "AND ";
		}
		$sqlaction = $sqlaction . "`c`.`review` = " . $reviewed . " ";
	}
	
	if ($weeklyreport == TRUE ) {
		$weeklyreportaction = ", `c`.`score` AS `contrscore`, `c`.`comment` AS `contrcomment`";
	} else {
		$weeklyreportaction = "";
	}
	
	// Fetch all Contributions that fit the seach criteria
	// By default there are no search criteria, should return a full list of all contributions
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `c`.`id` AS `cid`, `c`.`translator` AS `tid`, `c`.`proofreader` AS `pid`, `c`.`link` AS `contrlink`, `c`.`submit` AS `submitdate`, `c`.`review` AS `reviewdate`, `c`.`vote-utopian` AS `vote-utopian`, `p`.`name` AS `projectname`, `p`.`crowdin` AS `crowdinlink`, `p`.`github` AS `githublink`, `c`.`wordcount` AS `wordcount`, `c`.`partno` AS `partno`, `u1`.`username` AS `translator`, `u2`.`username` AS `proofreader`". $weeklyreportaction . " FROM `contributions` AS `c` LEFT JOIN `users` AS `u1` on `c`.`translator` = `u1`.`id` LEFT JOIN `users` AS `u2` on `c`.`proofreader` = `u2`.`id` LEFT JOIN `projects` AS `p` ON `c`.`project` = `p`.`id` ".$sqlaction."ORDER BY `c`.`submit` DESC" . $limit);
	
	if ($result) {
		// Initialise an empty variable to store the content
		$contributionlist = "";
		
		// Used to check if the page subtitle was added, inside the loop
		$titled = FALSE;
		
		// Used to check if the <table> has started, inside the loop
		$tabled = FALSE;
		// Get all projects
		while ($row = mysqli_fetch_assoc($result)) {
			$translator = $row['translator'];

			$submit = date("d/m/Y", strtotime($row['submitdate']));
			$project = $row['projectname'];
			$contributionlink = $row['contrlink'];
			$crowdin = $row['crowdinlink'];
			$partno = $row['partno'];
			$wordcount = $row['wordcount'];
			// If it's a weekly report, assign score and contribution comment to the variable
			if ($weeklyreport == TRUE ) {
				$score = $row['contrscore'];
				$comment = $row['contrcomment'];
			}
			
			// Show if/when a review has been added
			if ($row['reviewdate'] == NULL) {
				$review = "Not yet";
			} else {
				$review = date("d/m/Y", strtotime($row['reviewdate'])) . "<br />(".$row['proofreader'].")";
			}
			
			// Show if a utopian vote has been added to the contribution
			if ($row['vote-utopian'] == 0) {
				$voteutopian = "<i class=\"fa fa-times text-danger\" aria-hidden=\"false\"></i>";
			} else {
				$voteutopian = "<i class=\"fa fa-check text-success\" aria-hidden=\"false\"></i>";
			}
			
			// Will be expanded to add more details, like payout amount, part no and wordcount (where available).
			
			if (!$titled){
				$titled = TRUE;
				switch ($title) {
					case "project":
						if (($row['githublink'] == "") || ($row['githublink'] == NULL)) {
							$linkedrepos = "[<a href=\"".$crowdin."\">Crowdin</a>]";
						} else {
							$linkedrepos = "[<a href=\"".$row['githublink']."\">Github</a>|<a href=\"".$crowdin."\">Crowdin</a>]";
						}
						$contributioncount = mysqli_num_rows($result);
						$GLOBALS['page'] = $row['projectname'] . " Overview";
						$contributionlist .= "<div class=\"text-center\">View project on: $linkedrepos. <b>$contributioncount</b> Translation Contributions</div>";
						break;
					default:
						break;
				}
			}
			
			if (!$tabled) {
				$tabled = TRUE;
				if ($weeklyreport == TRUE) {
					$contributionlist .= "<table class=\"table table-striped table-hover\"><thead><tr><th>Project</th><th>Submit</th><th>Review</th><th>Links</th><th>Score</th><th>Wordcount</th><th>Comment</th></tr></thead><tbody>";
				} else {
					$contributionlist .= "<table class=\"table table-striped table-hover\"><thead><tr><th>Project</th><th>Submit</th><th>Review</th><th>Links</th><th>Wordcount<th></th></tr></thead><tbody>";
				}
			}
			
			// Add the project to the list
			if ($weeklyreport == TRUE) {
				$contributionlist = $contributionlist .= "<tr><td>".$project." (p.".$partno.")</td><td>".$submit.$voteutopian."<br />(".$translator.")</td><td>".$review."</td><td><a href=\"".$contributionlink."\" target=\"_blank\">Post</a> | <a href=\"".$crowdin."\" target=\"_blank\">[C]</a></td><td>".$score."</td><td>".$wordcount."</td><td>".$comment."</td><td>(TBC)</td>";
			} else {
				$contributionlist .= "<tr><td>".$project." (p.".$partno.")</td><td>".$submit.$voteutopian."<br />(".$translator.")</td><td>".$review."</td><td><a href=\"".$contributionlink."\" target=\"_blank\">Post</a> | <a href=\"".$crowdin."\" target=\"_blank\">[C]</a></td><td>".$wordcount."</td><td>(TBC)</td>";
			}
		}
		if ($tabled) {
			$contributionlist .= "</table>";
		}
		return $contributionlist;
	} else {
		// Error running the query. Return error.
		mysqli_error($GLOBALS['sqlcon']);
	}
}

// Check if the link is already in the DB
function CheckSteemLinkDB($url) {
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `id` FROM `contributions` WHERE `link` = \"".$url."\"");
	if ($result) {
		if (mysqli_num_rows($result) > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return "error";
	}
}

// Check if it is a valid Steemit/Utopian-io contribution link
// Should change it to check the actual tags, instead of the tag in the url.
function IsSteemLink($url) {
	$urlcomponents = parse_url($url);
	//print_r($urlcomponents);
	if (strpos($urlcomponents['host'], 'steemit.com') !== false) {
		//echo "is steemit link";
	} else {
		return FALSE;
	}
	
	$pathcomponents = explode("/", ltrim($urlcomponents['path'], "/"));
	//print_r($pathcomponents);
	// $pathcomponents[0] is the tag
	// $pathcomponents[1] is the username
	// $pathcomponents[2] is the post permlink
	
	if ($pathcomponents[0] != "utopian-io") {
		return FALSE;
	} else {
		$firsttag = $pathcomponents[0];
	}
		
	
	if (strncmp($pathcomponents[1], "@", 1) !== 0) {
		return FALSE;
	} else {
		$data['username'] = $pathcomponents[1];
	}
	
	$data['permlink'] = $pathcomponents[2];
	
	return $data;
}

// Parse the blockchain data for the provided post.
// When automation is implemented, this should be asynchronous
function ParseSteemLink($postdata) {
	// Add username to the $details array, as it will be returned with the rest of the data.
	$details['author'] = ltrim($postdata['username'], "@");
	$url = "https://api.steemjs.com/get_content?author=".$details['author']."&permlink=".$postdata['permlink'];
	//echo $url;
	$json = file_get_contents($url);
	//echo $json;

	$postdetails = json_decode($json, TRUE);

	//echo $postdetails['post']['title'];
	// Valid title formats:
	// "Project name XX Translation - Part YY (~ZZ words)"
	// "Project name XX Translation | Part YY | ~ZZ Words
	// where XX is the language name, YY is the part number and ZZ is the word count.
	$title = $postdetails['title'];
	if ((strpos($title, '-') !== false) || (strpos($title, '|') !== false)) {
		$titlextr = explode($GLOBALS['languagename'], $title, 2);
		// $titlextr[0] = Project name
		// $titlextr[1] = Rest of the title without language name
		$project = rtrim($titlextr[0]);
		preg_match_all('/\d+/i', $title, $contrnumbers);
		// $contrnumbers[0] = Part number
		// $contrnumbers[1] = Word count
		$partno = $contrnumbers[0][0];
		$wordcount = $contrnumbers[0][1];
		//echo "Project: " . $project . ", Part: " . $partno . ", Wordcount: " . $wordcount;
		$details['project'] = $project;
		$details['part'] = $partno;
		$details['words'] = $wordcount;

		$details['permlink'] = $postdetails['permlink'];
		$details['fulltitle'] = $postdetails['title'];
		$details['time'] = $postdetails['created'];
		return $details;
	} else {
		return FALSE;
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
				$finishlink = "<a href=\"projects.php?a=mark&w=1&i=".$row['id']."\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"Mark as Completed\"><i class=\"tiny material-icons text-success\">spellcheck</i></a>";
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
				$assignlink = "<a href=\"projects.php?a=prepare-assign&i=".$row['id']."\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"Assign to Translator\"><i class=\"tiny material-icons\">assignment_ind</i></a>&nbsp;";
				$finishlink = "";
			} else {
				// If there is a proofreader, hide the "assignlink"
				$assignlink = "";
			}
			
			if (($row['github'] == "") || ($row['github'] == NULL)) {
				$linkedrepos = "[<a href=\"".$row['crowdin']."\">C</a>]";
			} else {
				$linkedrepos = "[<a href=\"".$row['github']."\">G</a>|<a href=\"".$row['crowdin']."\">C</a>]";
			}
			
			// Add the project to the list
			$allprojects .= "<tr><td>".$row['projectname']." ".$linkedrepos."</td><td>".$row['translatorname']."</td><td>".$row['proofreadername']."</td><td>".$started."</td><td>".$finished."</td><td>".$assignlink."<a href=\"projects.php?a=view&i=".$row['id']."\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"View details/contributions\"><i class=\"tiny material-icons\">remove_red_eye</i></a>&nbsp;".$finishlink."</td>";
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
	if (mysqli_stmt_prepare($stmt, 'UPDATE `projects` SET `translator` = ?, `proofreader` = ?, `started` = ? WHERE `id` = ?')) {
		mysqli_stmt_bind_param($stmt, "sssi", $translator, $proofreader, $started, $project);
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

////////////////////
// Weekly Reports //
////////////////////

function GetReportList($user = NULL) {
	// prepare SQL action if any/all of the arguments are set.
	if (!is_null($user)) {
		// If $user was set, show only their weekly reports
		$sqlaction = "WHERE `user` = '" .$user. "' ";
		$limit = "";
	} else { 
		// Otherwise, show the last 15 weekly reports
		$sqlaction = "";
		$limit = " LIMIT 15";
	}
	
	// Fetch all Contributions that fit the seach criteria
	// By default there are no search criteria, should return a full list of all contributions
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `w`.`id` AS `rid`, `w`.`weekend` AS `enddate`, `w`.`overview` AS `overview`, `u`.`username` AS `username` FROM `weeklyreports` AS `w` LEFT JOIN `users` AS `u` ON `w`.`user` = `u`.`id` ".$sqlaction."ORDER BY `weekend` DESC" . $limit);
	if ($result) {
		// Initialise an empty variable to store the content
		$reviewlist = "";

		while ($row = mysqli_fetch_assoc($result)) {
			$reviewlist .= "<tr><td>".$row['enddate']."</td><td>".$row['username']."</td><td><a href=\"weeklyreports.php?a=view&id=".$row['rid']."\"><i class=\"tiny material-icons\">remove_red_eye</i></a></td></tr>";
		}
		return $reviewlist;
	} else {
		// Error running the query. Return error.
		mysqli_error($GLOBALS['sqlcon']);
	}
}

function GetSingleReport($reportid) {
	// Get the proofreader's ID, the report date and the overview comment from the $reportid.
	$result = mysqli_query($GLOBALS['sqlcon'], "SELECT `weekend`, `overview`, `user` FROM `weeklyreports` WHERE `id` = ". $reportid);
	$row = mysqli_fetch_assoc($result);
	
	$weekend = $row['weekend'];
	$proofreader = $row['user'];
	$overview = $row['overview'];
	
	// We want the reviews of the past 7 dates, so we need to get the actual date of $weekend - 6 days.
	$weekstart = date('Y-m-d', strtotime('-6 days', strtotime($weekend)));

	// Now we want to get all the contributions from that week, in a table. 
	$contributions = GetContributionList(NULL, NULL, $weekstart, $weekend, NULL, NULL, $proofreader, NULL, true);
	
	$username = GetUsername($proofreader);
	if ($username != "error" && $username != "notfound") {
		$GLOBALS['page'] = $username . " Report " . $weekstart . " to " . $weekend;
	}
	
	$returncontent = "<br /><b>Overview</b>: " . $overview . "<br /><br />" . $contributions;
	return $returncontent;
}

function GetMainPageContent() {
	if (isset($_COOKIE['username'])) {
		return "Welcome, " . $_COOKIE['username'] . ". You are already logged in, and you are registered as a member with access to the app, so feel free to stick around.";
	} else {
		return "This app is only intended for use by the ".$GLOBALS['teamname']." Translation Team. You need to login before you proceed to use anything in this app!<br /><a href=\"https://steemconnect.com/oauth2/authorize?client_id=aristotle.app&redirect_uri=https://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']) . "callback.php&scope=login\" class=\"font-weight-bold\">Secure login via SteemConnect</a>";
	}
}

function GetMenu() {
	if (isset($_COOKIE['username'])) {
		return "<button type=\"button\" class=\"btn btn-secondary dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Welcome ".$_COOKIE['username']."</button><div class=\"dropdown-menu dropdown-menu-right\"><a class=\"btn dropdown-item\" href=\"logout.php\">Logout</a></div>";
	} else {
		return "<button type=\"button\" class=\"btn btn-secondary dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Welcome, Guest</button><div class=\"dropdown-menu dropdown-menu-right\"><a class=\"btn dropdown-item\" href=\"https://steemconnect.com/oauth2/authorize?client_id=aristotle.app&redirect_uri=".  $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']) . "callback.php&scope=login\">Login via SteemConnect</a></div>";
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
