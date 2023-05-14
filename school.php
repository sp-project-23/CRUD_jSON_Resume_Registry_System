<?php
	require_once "pdo.php";	
	if (isset($_GET['term'])){
		$retval = array();
		try {
			$stmt = $pdo->prepare('SELECT name FROM institution WHERE name LIKE :prefix');
			$stmt->execute(array(':prefix' => '%'.$_GET['term'].'%'));
			//$retval = array();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				array_push($retval,$row['name']); 
			}	
		}
		catch(PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
		}

		//print_r($retval);
		echo json_encode($retval);
	}
?>						