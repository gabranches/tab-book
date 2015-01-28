<?php 
include('connect.php');

$query = "SELECT * FROM entries ORDER BY artist, title";
$result = $mysqli->query($query);       

$rows = array();
while($r= mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

$initial = json_encode($rows);

echo json_encode($rows);

$mysqli->close();

?>