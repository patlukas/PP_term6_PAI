<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: index.php');
  exit;
}

$conn = mysqli_connect('localhost', 'root', '', 'pai');

if (!$conn) {
  die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $content = $_POST['content'];
  $username = $_SESSION['username'];
  $tags = isset($_POST['tags']) ? $_POST['tags'] : '';

  $query = "SELECT * FROM resources WHERE username = '$username' AND name = '$name'";
  $result = mysqli_query($conn, $query);
  if (mysqli_num_rows($result) > 0) {
    echo "Masz już zasób o takiej nazwie!";
  } else {
    if($tags === '') {
      $query = "INSERT INTO resources (username, name, content) VALUES ('$username', '$name', '$content')";
    }
    else {
      $tagsString = implode(",", $tags);
      $query = "INSERT INTO resources (username, name, content, tags) VALUES ('$username', '$name', '$content', '$tagsString')";
    }
    if (mysqli_query($conn, $query)) {
      header('Location: index.php');
      exit;
    } else {
      echo "Błąd dodawania zasobu: " . mysqli_error($conn);
    }
  }
}

$tagsQuery = "SELECT * FROM tags";
$tagsResult = mysqli_query($conn, $tagsQuery);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Aplikacja zarządzająca danymi tekstowymi - Dodaj zasób</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#name').on('input', function() {
        var name = $(this).val();
        $.ajax({
          url: 'check_name.php',
          type: 'POST',
          data: { name: name },
          success: function(response) {
            $('#name-error').text(response);
          }
        });
      });
    });
  </script>
</head>
<body>
  <h1>Dodaj zasób</h1>
  <form method="POST" action="">
    <label for="name">Nazwa:</label>
    <input type="text" id="name" name="name" required><br>
    <span id="name-error"></span><br>
    <label for="content">Treść:</label>
    <textarea id="content" name="content" required></textarea><br><br>
    <label for="tags">Tagi:</label>
    <?php
    if (mysqli_num_rows($tagsResult) > 0) {
      while ($row = mysqli_fetch_assoc($tagsResult)) {
        $tagId = $row['id'];
        $tagName = $row['name'];
        echo '<input type="checkbox" name="tags[]" value="' . $tagId . '"> ' . $tagName . '<br>';
      }
    }
    ?>
    <br>
    <input type="submit" class="btn_add" value="Stwórz zasób">
  </form>

  <br><br>
  <a href="index.php" class="btn-back">Powrót</a>
</body>
</html>
