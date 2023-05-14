<?php
	require_once "pdo.php";
	session_start();
	//profile table fetch
	$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
	$stmt->execute(array(":xyz" => $_GET['profile_id']));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ( $row === false ) {
		$_SESSION['error'] = 'Bad value for user_id';
		header( 'Location: index.php' ) ;
		return;
	}
	// Flash pattern
	if ( isset($_SESSION['error']) ) {
		echo '<font color=red>'.$_SESSION['error']."</font>";
		unset($_SESSION['error']);
	}
	$fn = htmlentities($row['first_name']);
	$ln = htmlentities($row['last_name']);
	$e = htmlentities($row['email']);
	$h = htmlentities($row['headline']);
	$s = htmlentities($row['summary']);
	$profile_id = $row['profile_id'];
?>
<html>
<head>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<title>View Profile</title>
</head>
<body>
<div class="container-fluid col-sm-4">	
	<form method="post" class="container">
	<div class="row p-4">
		<div class="col"><h3>Profile</h3></div>
	</div>
	<div class="row m-2">
		<div class="col">First Name :</div>
		<div class="col"><?php echo $fn; ?></div>
	</div>
	<div class="row m-2">
		<div class="col">Last Name :</div>
		<div class="col"><?php echo $ln; ?></div>
	</div>
	<div class="row m-2">
		<div class="col">Email :</div>
		<div class="col"><?php echo $e; ?></div>
	</div>
	<div class="row m-2">
		<div class="col">Headline :</div>
		<div class="col"><?php echo $h; ?></div>
	</div>
	<div class="row m-2">
		<div class="col">Summary :</div>
		<div class="col"><?php echo $s; ?></div>
	</div>
	<div class="row m-2">
		<div class="col">Educations :</div>
	</div>
		<?php
			$stmt = $pdo->prepare("SELECT year, name FROM education JOIN institution ON education.institution_id = institution.institution_id WHERE profile_id = :xyz ORDER BY rank");
			$stmt->execute(array(":xyz" => $_GET['profile_id']));
			echo "<ul>";
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				echo "<div class=col><li>".$row['year']." : ".$row['name']."</li></div>";
			}
			echo "</ul>";
		?>
	<div class="row m-2">		
		<div class="col">Positions :</div>";
	</div>
		<?php 
			$stmt = $pdo->prepare("SELECT year, description FROM position where profile_id = :xyz ORDER BY rank");
			$stmt->execute(array(":xyz" => $_GET['profile_id']));
			echo "<ul>";
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				echo "<div class=col><li>".$row['year']." : ".$row['description']."</li></div>";
			}
			echo "</ul>";
		?>	
	<div class="row m-2">
		<div class="col" align="right"><input type="button" class="btn btn-warning" value="Done" id="back" onclick="location.replace('index.php')"></div>
	</div>
	</form>
</div>
</body>
</html>