function getContributionList(){
	$('#clist').load("weeklyreports.php?a=getconlist&endate=" + $( "#week" ).val());
}