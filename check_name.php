<?php
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'pai');

if (!$conn) {
  die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $username = $_SESSION['username'];

  $query = "SELECT * FROM resources WHERE username = '$username' AND name = '$name'";
  $result = mysqli_query($conn, $query);
  if (mysqli_num_rows($result) > 0) {
    echo "Masz już zasób o takiej nazwie!";
  }
}

mysqli_close($conn);
?>
