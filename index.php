<!DOCTYPE html>
<html>
<head>
  <title>Aplikacja zarządzająca danymi tekstowymi</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <?php
  session_start();

  $conn = mysqli_connect('localhost', 'root', '', 'pai');

  if (!$conn) die("Błąd połączenia z bazą danych: " . mysqli_connect_error());

  if(!isset($_SESSION["title"])) $_SESSION["title"] = "Gościu";

  if(isset($_SESSION["error"])) {
    echo "<h3>" . $_SESSION["error"] . "</h3>";
    unset($_SESSION["error"]);
  }

  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $title = $_SESSION["title"];

    echo "<h1>Witaj, $title !</h1>";
    echo "<a href='auth.php?logout=1' class='btn-back'>Wyloguj się</a><br><br>";

    $tagsQuery = "SELECT * FROM tags";
    $tagsResult = mysqli_query($conn, $tagsQuery);

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $content = isset($_GET['content']) ? $_GET['content'] : '';

    if (mysqli_num_rows($tagsResult) > 0) {
      echo "<h2>Filtry:</h2>";
      echo "<form method='GET' action=''>";
      echo "<label for='tag'>Wybierz tag:</label>";
      echo "<select name='tag' id='tag'>";
      
      $selectedTag = isset($_GET['tag']) ? $_GET['tag'] : 'all';
      
      echo "<option value='all'" . ($selectedTag === 'all' ? ' selected' : '') . ">Wszystkie zasoby</option>";

      while ($tagRow = mysqli_fetch_assoc($tagsResult)) {
        $tagId = $tagRow['id'];
        $tagName = $tagRow['name'];
        echo "<option value='$tagId'" . ($selectedTag === $tagId ? ' selected' : '') . ">$tagName</option>";
      }

      echo "</select><br>";
      echo "<label for='search'>Nazwa zasobu:</label>";
      echo "<input type='text' id='search' name='search' placeholder='Wpisz nazwę zasobu' value='$search'><br>";
      echo "<label for='search'>Treść zasobu:</label>";
      echo "<input type='text' id='content' name='content' placeholder='Wpisz treść zasobu' value='$content'><br>";
      echo "<input class='btn_add' type='submit' value='Filtruj'>";
      echo "</form>";
    }

    $query = "SELECT * FROM resources WHERE username = '$username'";
    if($selectedTag !== 'all') $query .= " AND tags LIKE '%$selectedTag%'";
    if (!empty($search)) $query .= " AND name LIKE '%$search%'";
    if (!empty($content)) $query .= " AND content LIKE '%$content%'";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
      echo "<h2>Lista zasobów:</h2>";
      echo "<div class='resource-list'>";
      while ($row = mysqli_fetch_assoc($result)) {
        $resourceId = $row['id'];
        $resourceName = $row['name'];

        echo "<p><a href='resource.php?id=$resourceId' class='btn_zas'>$resourceName</a></p><br>";
      }
      echo "</div>";
    } else {
      echo "<p>Brak zasobów spełniających powyższe wymogi.</p>";
    }

    echo "<a href='create.php' class='btn_add'>Dodaj nowy zasób</a>";    
  } else {
    echo "<h1>Witaj, Gościu!</h1>";
    echo '<h2>Rejestracja</h2>';
    echo '<form method="POST" action="auth.php">';
    echo '  <label for="name">Imię:</label>';
    echo '  <input type="text" id="name" name="name" required><br><br>';
    echo '  <label for="username">Login:</label>';
    echo '  <input type="text" id="username" name="username" required><br><br>';
    echo '  <label for="password">Hasło:</label>';
    echo '  <input type="password" id="password" name="password" required><br><br>';
    echo '  <input type="hidden" name="action" value="register">';
    echo '  <input type="submit" name="register" value="Zarejestruj">';
    echo '</form>';

    echo '<h2>Logowanie</h2>';
    echo '<form method="POST" action="auth.php">';
    echo '  <label for="username">Login:</label>';
    echo '  <input type="text" id="username" name="username" required><br><br>';
    echo '  <label for="password">Hasło:</label>';
    echo '  <input type="password" id="password" name="password" required><br><br>';
    echo '  <input type="hidden" name="action" value="login">';
    echo '  <input type="submit" name="login" value="Zaloguj">';
    echo '</form>';
  }

  mysqli_close($conn);
  ?>
</body>
</html>
