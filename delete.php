<?php
require_once "pdo.php";
session_start();

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql1 = "DELETE FROM profile WHERE profile_id = :zip";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute(array(':zip' => $_POST['profile_id']));
	
	$sql2 = "DELETE FROM position WHERE profile_id = :zip";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute(array(':zip' => $_POST['profile_id']));
	
	$sql3 = "DELETE FROM education WHERE profile_id = :zip";
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute(array(':zip' => $_POST['profile_id']));
	
    $_SESSION['success'] = 'Profiles deleted';
    header( 'Location: index.php' ) ;
    return;
}

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}
//deleted from profile table
$stmt1 = $pdo->prepare("SELECT first_name, last_name, profile_id FROM profile where profile_id = :xyz");
$stmt1->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt1->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'No profile for profile_id';
    header( 'Location: index.php' ) ;
    return;
}
$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);

//deleted from [osition table
$stmt2 = $pdo->prepare("SELECT profile_id, position_id, rank, year, description FROM position where profile_id = :xyz");
$stmt2->execute(array(":xyz" => $_GET['profile_id']));

$user_name = $_SESSION['name'];
?>
<html>
<head>
	<title>Deleting Profile</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
</head>
<body>
<div class="container fluid col-sm-6">
	<form method="post" class="container">
		<div class = "row p-4">
			<div class="col"><h3>Confirm! Deleting the profile of <b><u><?php echo $fn," ",$ln; ?></u></b> by <?php echo $user_name;?></h3></div>
		</div>
		<input type="hidden" name="profile_id" value="<?php echo $_GET['profile_id']; ?>">
		<div class="row m-2">
			<div class="col"><input type="submit" value="Delete" class="btn btn-danger" name="delete"></div>
			<div class="col" align="right"><input type="button" value="No" class="btn btn-danger" id="ind" onclick="location.replace('index.php')"></div>
		</div>
	</form>
</div>
</bodY>
</html>