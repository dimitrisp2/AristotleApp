<?php
$page = "Contribution Posts";
include("functions.php");
if (isset($_GET['a'])) {
	$action = $_GET['a'];
} else {
	$action = "list";
}

if ($action == "list") {
	$pagecontent = GetContributionList();
} else {

}
include("common/head.php");
?>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php echo $pagecontent; ?>
            </div>
        </div>
    </div>
<?php
include("common/foot.php");
?>


