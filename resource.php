<!DOCTYPE html>
<html>
<head>
  <title>Aplikacja zarządzająca danymi tekstowymi - Zasób</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <?php
  session_start();

  $conn = mysqli_connect('localhost', 'root', '', 'pai');

  if (!$conn) die("Błąd połączenia z bazą danych: " . mysqli_connect_error());

  if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
  }

  $resourceId = $_GET['id'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    
    $query = "SELECT shared FROM resources WHERE username = '$username' AND id = '$resourceId'";
    $result = mysqli_query($conn, $query);

    $row = mysqli_fetch_assoc($result);
    $shared = $row["shared"];

    $newShared = '1';
    if($shared === '1') $newShared = '0';

    $query = "UPDATE resources SET shared = $newShared WHERE username = '$username' AND id = '$resourceId'";
    if (mysqli_query($conn, $query)) {
      header('Location: resource.php?id='.$resourceId);
      exit;
    } else {
      echo "Błąd: " . mysqli_error($conn);
    }
  }

  $query = "SELECT r.name, r.content, u.name AS author, r.username AS username, r.tags, shared
            FROM resources r
            JOIN users u ON r.username = u.username
            WHERE r.id = $resourceId";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) == 0) {
    echo "Zasób nie istnieje.";
    echo '<a href="index.php">Strona główna</a>';
    mysqli_close($conn);
    exit;
  }

  $row = mysqli_fetch_assoc($result);
  $name = $row['name'];
  $content = $row['content'];
  $author = $row['author'];
  $authorUsername = $row["username"];
  $tags = $row["tags"];
  $shared = $row["shared"];

  if ($shared === '0') {
    echo "Zasób nie został udostępniony! <br>Poproś właściciela o udostępnienie zasobu!";
    echo '<a href="index.php">Strona główna</a>';
    mysqli_close($conn);
    exit;
  }
  ?>

  <div class="container">
    <?php
      if(isset($_SESSION["username"])) {
          echo "<h1>Witaj ".$_SESSION["title"]."</h1>";
      }
      else echo "<h1>Witaj Gościu</h1>";
    ?>
    
    <h1>Zasób</h1>
    <p>Nazwa: <?php echo $name; ?></p>
    <p>Treść: <?php echo $content; ?></p>
    <p>Autor: <?php echo $author; ?></p>

    <?php
      $tagList = '';
      if($tags !== '') {
        $tagIds = explode(",", $tags);
        $tagNames = [];
    
        foreach ($tagIds as $tagId) {
          $tagQuery = "SELECT name FROM tags WHERE id = $tagId";
          $tagResult = mysqli_query($conn, $tagQuery);
          $tagRow = mysqli_fetch_assoc($tagResult);
          $tagNames[] = $tagRow['name'];
        }
    
        $tagList = implode(", ", $tagNames);
      }
      
      echo "<p>Tagi: $tagList</p>";
    ?>

    <?php
      if(isset($_SESSION["username"]) && $authorUsername == $_SESSION["username"]) {
          echo "<a href='edit.php?id=".$_GET['id']."' class='btn_edit_del'>Edytuj lub usuń zasób</a>";
      }
      mysqli_close($conn);
    ?>

    <?php
      if($shared === '1') echo "<p>Adres URL zasobu: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."</p>";
      if(isset($_SESSION["username"]) && $authorUsername == $_SESSION["username"]) {
        $v = "Udostępnij zasób";
        if($shared === '1') $v = "Przestań udostępniać zasób";
        echo '<form method="POST" action="">';
        echo '<input type="submit" class="btn_add" value="'.$v.'">';
        echo '</form>';
      }
    ?>
    <br><br>
    <a href="index.php" class="btn-back">Powrót</a>
  </div>
</body>
</html>
