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

function closeSQL() {
	global $sqlcon;
	mysqli_close($sqlcon);
}
?>
