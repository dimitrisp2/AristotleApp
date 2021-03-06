<?php
include("functions.php");
$urocksapi = file_get_contents("https://utopian.rocks/api/posts?category=translations");
$utopian=json_decode($urocksapi,true);

foreach ($utopian as $key => $value){
	// Checks if the current post is from $languagename
	if (strpos($utopian[$key]['title'], $languagename) === false) {
		continue;
	}
	
	// Ignore posts that have "Ignore" as moderator, or the title includes the word "Ignore"
	if ($utopian[$key]["moderator"] == "IGNORE") {
		continue;
	} else if (strpos($utopian[$key]["title"], "ignore") !== false) {
		continue;
	}
	
	// Checks if the link has already been added
	$isadded = CheckSteemLinkDB($utopian[$key]["url"]);
	if ($isadded) {
		$utopiancsv = GetUtopianStatus($utopian[$key]["url"]);
		$utopianstatus = explode(",", $utopiancsv);
		// $utopianstatus array 0: id, 1: vote-utopian, 2: review (date), 3: vote-review, 4: review-link, 5: proofreader, 6: rowlocked [TRUE/FALSE]
		// Related constants 0: CID, 1: UTOPIANVOTE, 2: REVIEWDATE, 3: REVIEWVOTE, 4: REVIEWLINK, 5: PROOFREADER, 6: ROWLOCKED
		
		// If the row has been locked, there's no need to waste CPU power to check/update the data.
		if ($utopianstatus[ROWLOCKED] == TRUE) {
			continue;
		}
		
		// Check if the review date on utopian.rocks is 0.
		if ($utopian[$key]["review_date"] == 0) {
			// If it is, the date should be 0000-00-00 00:00:00
			$reviewdate = "0000-00-00 00:00:00";
		} else {		
			// Convert utopian rocks' Epoch Milliseconds to Year-Month-Date Hour:Minutes:Seconds
			$reviewdate = ConvertEpochToYMD($utopian[$key]["review_date"]["\$date"]);
		}

		// Get proofreader's username instead of the user id
		$proofreader = GetUsername($utopianstatus[PROOFREADER]);

		// $triggerupdate will be used to initiate an update
		$triggerupdate = FALSE;
		
		// Review checks. Using a single IF instead of multiple ones, as triggering an update will update the full contribution data
		// so there's no need to waste computational for the extra checks.
		if ($proofreader != $utopian[$key]["moderator"]) {
			$triggerupdate = TRUE;
		} else if (($utopianstatus[REVIEWDATE] == "0000-00-00 00:00") && ($utopian[$key]["status"] == "reviewed")) {
			$triggerupdate = TRUE;
		}
		
		// Change vote status int to string
		if ($utopianstatus[UTOPIANVOTE] == CVOTED) {
			$upvotestatus = "true";
		} else {
			$upvotestatus = "false";
		}
		
		// Hack to change "voted_on" 0 to "false"
		if ($utopian[$key]["voted_on"] == 0) {
			$utopian[$key]["voted_on"] = "false";
		}
		
		// Check the vote status
		if ($upvotestatus != $utopian[$key]["voted_on"]) {
			$triggerupdate = TRUE;
		}
		
		if ($triggerupdate == TRUE) {
			// 
			$condetails = ParseTitle($utopian[$key]["title"]);
			
			// Hack for some of the Greek team titles.
			$projecttitle = str_replace(" -", "", $condetails["project"]);
			
			// Try to get the project's ID. If the project is not found, lock the row and do not update it.
			$project = GetProjectID($projecttitle);
			
			if ($project == null) {
				$locked = LockContribution($utopianstatus[CID]);
				continue;
			}

			$translator = GetUserID($utopian[$key]["author"]);
			$created = ConvertEpochToYMD($utopian[$key]["created"]["\$date"]);
			$partno = $condetails["part"];
			$wordcount = $condetails["words"];
			$proofreaderid = GetUserID($utopian[$key]["moderator"]);
			if ($utopian[$key]["voted_on"] == "true") {
				$utopianvote = CVOTED;
			} else {
				$utopianvote = CNOTVOTED;
			}
			
			// $reviewdate is already set in line 40

			if ($reviewdate == "0000-00-00 00:00:00" || $reviewdate == NULL) {
				$reviewstatus = CUNREVIEWED;
			} else {
				$reviewstatus = CREVIEWED;
			}
			
			if (isset($utopian[$key]["comment_url"]) && $utopian[$key]["comment_url"] != NULL) {
				$reviewlink = $utopian[$key]["comment_url"];
			} else {
				$reviewlink = NULL;
			}
			
			$postpayout = $utopian[$key]["total_payout"];
			
			// If post has paid out, CheckPayoutStatus() returns PAIDOUT (= 1), so the row will lock.
			$rowlock = CheckPayoutStatus($created);
			
			$updatecont = UpdateContribution($utopianstatus[CID], $project, $translator, $created, $partno, $wordcount, $proofreaderid, $utopianvote, $reviewdate, $reviewstatus, $reviewlink, $postpayout, $rowlock);
		}
		
	} else {
		echo $utopian[$key]["url"] . ": not added<br />";
		$condetails = ParseTitle($utopian[$key]["title"]);
		echo "<pre>";
		print_r($condetails);
		echo "</pre>";
		$projectid = GetProjectID($condetails["project"]);
		$translatorid = GetUserID($utopian[$key]["author"]);
		$steemlink = $utopian[$key]["url"];
		$submitdate = ConvertEpochToYMD($utopian[$key]["created"]['$date']);
		$partno = $condetails["part"];
		$wordcount = $condetails["words"];
		if ($projectid == NULL) {
			$projectid = 0;
		}
		$addcont = AddContribution($projectid, $translatorid, $steemlink, $submitdate, $partno, $wordcount);
	}
}

?>