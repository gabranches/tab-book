<?php 
include('connect.php');

$id = mysqli_real_escape_string($mysqli, $_POST['id']);

$query = "DELETE FROM entries WHERE id=".$id." LIMIT 1";
$mysqli->query($query) or die(mysqli_error($mysqli));      


$mysqli->close();

?>