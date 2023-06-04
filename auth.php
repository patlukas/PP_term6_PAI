<?php
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'pai');

if (!$conn) die("Błąd połączenia z bazą danych: " . mysqli_connect_error());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'register') {
  $name = $_POST['name'];
  $username = $_POST['username'];
  $password = $_POST['password'];

  $query = "SELECT * FROM users WHERE username = '$username'";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) > 0) {
    $_SESSION['error'] = "Taki login już istnieje.";
    header("Location: index.php");
    exit;
  } else {
    $query = "INSERT INTO users (name, username, password) VALUES ('$name', '$username', '$password')";
    if (mysqli_query($conn, $query)) {
      echo "Rejestracja zakończona sukcesem. Witaj nowy użytkowniku!";
      $_SESSION['username'] = $username;
      $_SESSION["title"] = "Użytkowniku zarejestrowany";
      header("Location: index.php");
      exit;
    } else {
        $_SESSION['error'] = "Błąd rejestracji: " . mysqli_error($conn);
        header("Location: index.php");
        exit;
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'login') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) > 0) {
    $_SESSION['username'] = $username;
    $_SESSION["title"] = "Użytkowniku zalogowany";
    header("Location: index.php");
    exit;
  } else {
    $_SESSION['error'] = "Błędne dane logowania.";
    header("Location: index.php");
    exit;
  }
}

if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: index.php");
  exit;
}

mysqli_close($conn);
?>
