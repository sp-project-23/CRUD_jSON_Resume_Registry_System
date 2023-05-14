<?php
	require_once "pdo.php";
	//require_once "school.php";
	session_start();
	if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && 
		 isset($_POST['summary'])) {

		// Data validation
		if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || 
			strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
			$_SESSION['error'] = "All fields are required";
			header("Location: add.php");
			return;
		}

		if ( strpos($_POST['email'],'@') === false ) {
			$_SESSION['error'] = 'Email must contain @';
			header("Location: add.php");
			return;
		}
		
		/*if (!is_numeric($_POST['edu_year'])) {
			$_SESSION['error'] = 'Year must be numeric';
			header("Location: add.php");
			return;
		}*/
		
		//Insert into the profile
		$sql = "INSERT INTO profile (user_id, first_name, last_name, email, headline, summary) VALUES (:u_id, :fname, :lname, :email, :headline, :summary)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(
			':u_id' => $_SESSION['user_id'],
			':fname' => $_POST['first_name'],
			':lname' => $_POST['last_name'],
			':email' => $_POST['email'],
			':headline' => $_POST['headline'],
			':summary' => $_POST['summary'])
					);
			
		
		$profile_id = $pdo->lastInsertId();
		
		//Insert the position entries
		$rank=1;
		for($i=1;$i<=9;$i++){
			if(!isset($_POST['year'.$i])) continue;
			if(!isset($_POST['desc'.$i])) continue;
			$year = $_POST['year'.$i];
			$desc = $_POST['desc'.$i];
			$stmt1 = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
			$stmt1->execute(array(
				':pid' => $profile_id,
				':rank' => $rank,
				':year' => $year,
				':desc' => $desc
								 )
						   );
			$rank++;
		};	
		
		//Insert the education entries		
		$edu_rank=1;
		for($i=1;$i<=9;$i++){
			if(!isset($_POST['edu_year'.$i])) continue;
			if(!isset($_POST['edu_school'.$i])) continue;
			$eduyear = $_POST['edu_year'.$i];
			$sch = $_POST['edu_school'.$i];
			
			if (!is_numeric($_POST['edu_year'.$i])) {
				$_SESSION['error'] = 'Year must be numeric';
				header("Location: add.php");
				return;
			}
			//fetching & validating institution_id from institution table
			$ins_id = false;
			$stmt2 = $pdo->prepare('SELECT institution_id FROM institution WHERE name = :name');
			$stmt2->execute(array(':name' =>$sch));
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
									VALUES ( :p_id, :ins_id, :edu_rank, :edu_year)');
			$stmt3->execute(array(
				':p_id' => $profile_id,
				':ins_id' => $ins_id,
				':edu_rank' => $edu_rank,
				':edu_year' => $eduyear
								 )
						   );
			$edu_rank++;
		};	
		
		$_SESSION['success'] = 'Profile added';
		header( 'Location: index.php' ) ;
		return;
	}
	$user_name = $_SESSION['name'];
?>
<!doctype html>
<html>
<head>
	<title>Adding Profile</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<script src="//code.jquery.com/jquery-1.12.4.js"></script>
	<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
<div class="container-fluid col-sm-6">
	<form method="post" class="container" autocomplete="off">
		<div class="row p-4">
			<div class="col"><h3>Adding New Profile by <?php echo $user_name;?></h3></div>
		</div>
		<div class="row m-2">
			<div class="col">First Name :</div>
			<div class="col"><input type="text" name="first_name" class="form-control"></div>
		</div>
		<div class="row m-2">
			<div class="col">Last Name :</div>
			<div class="col"><input type="text" name="last_name" class="form-control"></div>
		</div>
		<div class="row m-2">
			<div class="col">Email :</div>
			<div class="col"><input type="text" name="email" class="form-control"></div>
		</div>
		<div class="row m-2">
			<div class="col">Headline :</div>
			<div class="col"><input type="text" name="headline" class="form-control"></div>
		</div>
		<div class="row m-2">
			<div class="col">Summary :</div>
			<div class="col"><textarea name="summary" class="form-control"></textarea></div>
		</div>
		<div class="row m-2">
			<div class="col">Insert Education :</div>
			<div class="col"><input type="submit" id="addEdu" value="+" class="btn btn-info"></div>
		</div>
		<div id="education_fields"></div>
		
		<div class="row m-2">
			<div class="col">Insert Position :</div>
			<div class="col"><input type="submit" id="addPos" value="+" class="btn btn-info"></div>
		</div>
		<div id="position_fields"></div>		
		<div class="row m-2">
			<div class="col-sm-6"><input type="submit" value="Add" class="btn btn-info"></div>
			<div class="col-sm-6" align="right"><input type="button" value="Cancel" class="btn btn-info" onclick="location.replace('index.php')"></div>
		</div>			
	</form>
		<!--<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>-->
		
		
		<script>			
			countEdu=0;	
			$(document).ready(function(){
				window.console && console.log('Document ready called');
				$('#addEdu').click(function(event){
					event.preventDefault();
					if(countEdu>=9){
						alert("Maximum of nine education entries execeded");
						return;
					}
					countEdu++;
					window.console && console.log("Adding Education"+countEdu);
					$('#education_fields').append(
						'<div id="education'+countEdu+'">\
							<div class="row m-2">\
								<div class=col>Year :</div>\
								<div class=col><input type="text" name="edu_year'+countEdu+'" class="form-control" value=""/></div>\
							</div>\
							<div class="row m-2">\
								<div class=col>School :</div>\
								<div class=col><input type="text" name="edu_school'+countEdu+'" size="35" id="school" class="form-control" value=""/></div>\
							</div>\
							<div class="row m-2">\
								<div class=col>Delete Education :</div>\
								<div class=col><input type="button" value="-" class="btn btn-info" onclick="$(\'#education'+countEdu+'\').remove(); return false;">\
								</div>\
							</div>\
						</div>');
						
						$('#school').autocomplete({ source: "school.php" });
				});
				$('#school').autocomplete({ source: "school.php" });
			});
			countPos=0;	
			$(document).ready(function(){
				window.console && console.log('Document ready called');
				$('#addPos').click(function(event){
					event.preventDefault();
					if(countPos>=9){
						alert("Maximum of nine position entries execeded");
						return;
					}
					countPos++;
					//window.console && console.log("Adding Position"+countPos);
					
					$('#position_fields').append(
						'<div id="position'+countPos+'">\
							<div class="row m-2">\
								<div class=col>Year :</div>\
								<div class=col><input type="text" name="year'+countPos+'" class="form-control" value=""/></div>\
							</div>\
							<div class="row m-2">\
								<div class=col>Description :</div>\
								<div class=col><textarea name="desc'+countPos+'" class="form-control" value=""/></textarea></div>\
							</div>\
							<div class="row m-2">\
								<div class=col>Delete Position :</div>\
								<div class=col><input type="button" value="-" class="btn btn-info" onclick="$(\'#position'+countPos+'\').remove(); return false;">\
								</div>\
							</div>\
						</div>');
				});	
			
			});
		</script>
		<?php
		// Flash pattern
			if ( isset($_SESSION['error']) ) {
				echo "<div class=col align=right><font color=red>".$_SESSION['error']."</font></div>";
				unset($_SESSION['error']);
			}
		?>
</div>
</body>
</html>