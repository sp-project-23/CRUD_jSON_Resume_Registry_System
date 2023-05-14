<?php
	session_start();
	require_once "pdo.php";
	//require_once "utility.php";

	if (isset($_POST['cancel'])) {
		header("Location: index.php");
		return;
	}

	if ( isset($_SESSION['name']) && isset($_SESSION['user_id']) ) {
		if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && 
			isset($_POST['headline']) && isset($_POST['summary'])) {
			if ( strlen($_POST['first_name']) < 1 ||  strlen($_POST['last_name']) < 1 ||  strlen($_POST['email']) < 1 ||  
				 strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
				$_SESSION['error'] = "All values are required";
				header('Location: edit.php?profile_id='.$_GET['profile_id']);
				return;
			}
			else if (!preg_match("/@/", $_POST['email'])) {
				$_SESSION['error'] = "Email must contain @";
				error_log("Login fail: Error = ".$_SESSION['error']);
				header('Location: edit.php?profile_id='.$_GET['profile_id']);
				return;
			}
			/*else if (is_string($msg)) {
				$_SESSION['error'] = $msg;
				header('Location: edit.php?profile_id='.$_GET['profile_id']);
				return;
			}*/
			else {
				$sql = "UPDATE profile SET first_name=:fn, last_name=:ln, email=:em, headline=:he, summary=:su WHERE profile_id=:pid";
				$stmt = $pdo->prepare($sql);
				$stmt->execute(array(
						':pid' => $_POST['profile_id'],
						':fn' => $_POST['first_name'],
						':ln' => $_POST['last_name'],
						':em' => $_POST['email'],
						':he' => $_POST['headline'],
						':su' => $_POST['summary']));
						
				$stmt = $pdo->prepare('DELETE FROM position WHERE profile_id=:pid');
				$stmt->execute(array(':pid' => $_GET['profile_id']));	
				
				$stmt2 = $pdo->prepare('DELETE FROM education WHERE profile_id=:pid');
				$stmt2->execute(array(':pid' => $_GET['profile_id']));	
				
				$profile_id = $_GET['profile_id'];
				
				$rank = 1;
				for($i = 1; $i <= 9; $i++) {
					if(!isset($_POST['year'.$i])) continue;
					if(!isset($_POST['desc'.$i])) continue;
					$year = $_POST['year'.$i];
					$desc = $_POST['desc'.$i];

					$stmt = $pdo->prepare('INSERT INTO position (profile_id, rank, year, description)
										VALUES (:pid, :rank, :year, :desc)');
					$stmt->execute(array(
						':pid'=> $profile_id,
						':rank'=> $rank,
						':year'=> $year,
						':desc'=> $desc ));
					$rank++;
				}
				
				$edurank = 1;
				for($i = 1; $i <= 9; $i++) {
					if(!isset($_POST['edu_year'.$i])) continue;
					if(!isset($_POST['edu_school'.$i])) continue;
					$eduyear = $_POST['edu_year'.$i];
					$sch = $_POST['edu_school'.$i];
					//fetching & validating institution_id from institution table
					$ins_id = false;
					$stmt2 = $pdo->prepare('SELECT institution_id FROM institution WHERE name = :name');
					$stmt2 -> execute(array(':name' =>$sch));
					$row = $stmt2->fetch(PDO::FETCH_ASSOC);	
					
					if($row !== false)
						$ins_id = $row['institution_id'];
					//Inserting new school's name into institution table
					if($ins_id === false){
						$stmt2 = $pdo->prepare('INSERT INTO institution (name) VALUES (:name)');
						$stmt2 -> execute(array(':name' =>$sch));
						$ins_id = $pdo->lastInsertId();
					}
					//Insertion query to eduaction table
					$stmt3 = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year)
										VALUES (:p_id, :ins_id, :edu_rank, :edu_year)');
					$stmt3->execute(array(
						':p_id' => $profile_id,
						':ins_id' => $ins_id,
						':edu_rank' => $edurank,
						':edu_year' => $eduyear));
					$edurank++;
				}
				
				$_SESSION['success'] = "Record Updated";
				header("Location: index.php");
				return;
			}
		}
	}
	else {
		die("ACCESS DENIED");
	}

	function loadpos($pdo, $profile_id){
		$stmt = $pdo->prepare('SELECT * from position WHERE profile_id = :prof ORDER BY rank');
		$stmt->execute(array(':prof' =>$profile_id));
		$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $positions;
	}

	function loadedu($pdo, $profile_id){
		$stmt = $pdo->prepare('SELECT * FROM education JOIN institution ON education.institution_id = institution.institution_id WHERE profile_id = :prof ORDER BY rank');
		$stmt->execute(array(':prof' =>$profile_id));
		$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $educations;
	}
	
	$poses = loadpos($pdo, $_REQUEST['profile_id']);
	$schools = loadedu($pdo, $_REQUEST['profile_id']);

	$sql = "SELECT * FROM profile WHERE profile_id=:pid;";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(':pid' => $_GET['profile_id']));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($row === false) {
		$_SESSION['error'] = "Bad value for profile_id";
		header("Location: index.php");
		return;
	}
	$fn = $row['first_name'];
	$ln = $row['last_name'];
	$em = $row['email'];
	$he = $row['headline'];
	$su = $row['summary'];
?>

<!-- ============================================================================================================================ -->

<html>
<head>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<script src="//code.jquery.com/jquery-1.12.4.js"></script>
	<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<title>Editing Profile</title>
</head>
<body>
	<div class="container-fluid col-sm-6">
    <form method="post" class="container"  autocomplete="off">
		<?php
			echo('<div class="row p-4"><div class="col"><h3>Editing Profile by '.htmlentities($_SESSION['name']).'</h3></div></div>');
			if ( isset($_SESSION['error']) ) {
				echo ('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
				unset($_SESSION['error']);
			}
		?>
		<div class="row m-2">
			<div class="col">First Name :</div>
			<div class="col"><input type="text" class="form-control" name="first_name" value="<?php echo "$fn"; ?>"></div>
		</div>
		<div class="row m-2">
			<div class="col">Last Name :</div>
			<div class="col"><input type="text" class="form-control" name="last_name" value="<?php echo "$ln"; ?>"></div>
		</div>
		<div class="row m-2">
			<div class="col">Email :</div>
			<div class="col"><input type="text" class="form-control" name="email" value="<?php echo "$em"; ?>"></div>
		</div>	
		<div class="row m-2">
			<div class="col">Headline :</div>
			<div class="col"><input type="text" name="headline" class="form-control" value="<?php echo "$he"; ?>"></div>
		</div>			
		<div class="row m-2">
			<div class="col">Summary :</div>
			<div class="col"><textarea name="summary" class="form-control"><?php echo "$su"; ?></textarea></div>
		</div>	
		<div class="row m-2">
			<div class="col">Insert Education :</div>
			<div class="col"><input type="submit" id="addedu" value="+" class="btn btn-info"></div>
		</div>
		<div id="education_fields"></div>
		<?php
			$countEdu = 0;
			foreach($schools as $school) {
				echo('<div class = "education" id="education'.$school['rank'].'">');
				echo('<div class="row m-2"><div class="col">Year :</div><div class="col"><input type="text" class="form-control" name="edu_year'.$school['rank'].'" value="'.$school['year'].'" /></div></div>');
				echo('<div class="row m-2"><div class="col">School :</div><div class="col"><input type="text" id="school" class="form-control" name="edu_school'.$school['rank'].'" value = "'.$school['name'].'"/></div></div>');
				echo('<div class="row m-2"><div class="col">Delete Education :</div><div class="col"><input type="button" class="btn btn-info" value="-" onclick="'."$('#education".$school['rank']."').remove();return false;".'" ></div></div>');
				echo('</div>');
				$countEdu++;
			}
		?>
		<div class="row m-2">
			<div class="col">Insert Position :</div>
			<div class="col"><input type="submit" id="addpos" value="+" class="btn btn-info"></div>
		</div>
		<div id="position_fields"></div>
		<?php
			$countPos = 0;
			foreach($poses as $pos) {
				echo('<div id="position'.$pos['rank'].'">');
				echo('<div class="row m-2"><div class="col">Year: </div><div class="col"><input type="text" class="form-control" name="year'.$pos['rank'].'" value="'.$pos['year'].'" /></div></div>');
				echo('<div class="row m-2"><div class="col">Description :</div><div class="col"><textarea class="form-control" name="desc'.$pos['rank'].'">'.$pos['description'].'</textarea></div></div>');
				echo('<div class="row m-2"><div class="col">Delete Position :</div><div class="col"><input type="button" class="btn btn-info" value="-" onclick="'."$('#position".$pos['rank']."').remove();return false;".'" ></div></div>');
				echo('</div>');
				$countPos++;
			}
		?>
		<div class="col"><input type="hidden" name="profile_id" value="<?php echo $row['profile_id']; ?>"></div>
		<div class="row m-2">
			<div class="col"><input type="submit" value="Save" class="btn btn-primary"/></div>
			<div class="col" align="right"><input type="submit" class="btn btn-primary" name="cancel" value="Cancel"/></div>
		</div>
	

		<!--<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>-->

		<script>
		countPos = <?php echo ($countPos) ?>;
		countEdu = <?php echo ($countEdu) ?>;
		
		window.console && console.log("countPos = " + countPos);
		$(document).ready(function() {
		window.console && console.log('Document ready called');
		
		$('#addpos').click(function(event) {
			event.preventDefault();
			if (countPos >= 9) {
				alert('Maximum of nine position entries execeded');
				return;
			}
			countPos++;
			window.console && console.log(' Adding position ' + countPos);
			$('#position_fields').append( 
				'<div id="position' + countPos + '"> \
				<div class="row m-2"><div class="col">Year :</div><div class="col"><input type="text" class="form-control" name="year' + countPos + '" value="" /></div></div> \
				<div class="row m-2"><div class="col">Description :</div><div class="col"><textarea class="form-control" name="desc' + countPos + '"></textarea></div></div> \
				<div class="row m-2"><div class="col">Delete Position :</div><div class="col"><input type="button" class="btn btn-info" value="-" onclick="$(\'#position' + countPos + '\').remove(); countPos--; return false;"></div></div>\
				</div>');
		});
		});
		
		//window.console && console.log("countEdu = " + countEdu);
		$(document).ready(function() {
		window.console && console.log('Document ready called');
		$('#addedu').click(function(event) {
			event.preventDefault();
			if (countEdu >= 9) {
				alert('Maximum of nine education entries execeded');
				return;
			}
			countEdu++;
			window.console && console.log(' Adding education ' + countEdu);
			
			$('#education_fields').append(
				'<div id="education' + countEdu + '"> \
				<div class="row m-2"><div class="col">Year :</div><div class="col"><input type="text" class="form-control" name="edu_year' + countEdu + '" value="" /></div></div> \
				<div class="row m-2"><div class="col">School :</div><div class="col"><input type="text" id="school" class="form-control" name="edu_school' + countEdu + '"></div></div> \
				<div class="row m-2"><div class="col">Delete Education :</div><div class="col"><input type="button" class="btn btn-info" value="-" onclick="$(\'#education' + countEdu + '\').remove(); countEdu--; return false;"></div></div>\
				</div>');
			
				$('#school').autocomplete({	source: "school.php" });
			});
			$('#school').autocomplete({	source: "school.php" });
		});
		</script>	
	</form>
</div>
</body>
</html>