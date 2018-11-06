<?php
include("functions.php");
$urocksapi = file_get_contents("test.json"); // Example response saved from utopian.rocks/api, used for testing
$utopian=json_decode($urocksapi,true);

foreach ($utopian as $key => $value){
	// Checks if the current post is from $languagename
	echo "<br />";
	echo $utopian[$key]["url"] . ": ";
	if (strpos($utopian[$key]['title'], $languagename) === false) {
		echo "is not " . $languagename;
		continue;
	} else {
		echo "is " . $languagename;
	}

	// Checks if the link has already been added
	$isadded = CheckSteemLinkDB($utopian[$key]["url"]);
	// TODO: Create functions that will check if a contribution entry has been marked as "reviewed" and "upvoted by utopian".
	if ($isadded) {
		echo $utopian[$key]["url"] . ": already added<br />";
		// If the contribution is added but not marked as "reviewed/upvoted", the details should be taken from the $utopian[$key] array.
		// If marked as "reviewed/upvoted", check if review has been upvoted
		// If all the above are "TRUE", then there is nothing else to do.
	} else {
		// If the contribution is not yet added, get all the needed details
		// (creation date, review/upvote status etc) and add it to the db
		// The following code is used as an example to show the details
		echo "<hr />";
		echo $utopian[$key]["url"] . ":<br />";
		echo $utopian[$key]["review_date"]["\$date"]."<br />";
		echo $utopian[$key]["moderator"]."<br />";
		if (isset($utopian[$key]["review_status"])) {
			echo $utopian[$key]["review_status"]."<br />";
			echo $utopian[$key]["voted_on"]."<br />";
			
			
		}
		echo "<hr />";
	}
}

?>