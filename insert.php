<?php 

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

include('connect.php');

$artist 	= mysqli_real_escape_string($mysqli, $_POST['artist']);
$title 		= mysqli_real_escape_string($mysqli, $_POST['title']);
$url 		= mysqli_real_escape_string($mysqli, $_POST['url']);
$editMode 	= mysqli_real_escape_string($mysqli, $_POST['editMode']);
$id 		= mysqli_real_escape_string($mysqli, $_POST['id']);

if($editMode == "1"){
	$query = "REPLACE entries (id, user_id, artist, title, url) VALUES ($id, 1, '".$artist."', '".$title."', '".$url."')";
} else {
	$query = "INSERT INTO entries (user_id, artist, title, url) VALUES (1, '".$artist."', '".$title."', '".$url."')";
}

if($artist != ""){
	$mysqli->query($query) or die(mysqli_error($mysqli));      
}



$mysqli->close();

?>