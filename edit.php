<!DOCTYPE html>
<html>
<head>
  <title>Aplikacja zarządzająca danymi tekstowymi</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#resource_name').on('input', function() {
        var name = $(this).val();
        $.ajax({
          url: 'check_name_edit.php',
          type: 'POST',
          data: { name: name, id: '<?php echo $_GET['id'] ?>' },
          success: function(response) {
            $('#name-error').text(response);
          }
        });
      });
    });
  </script>
</head>
<body>
  <?php
  session_start();

  $conn = mysqli_connect('localhost', 'root', '', 'pai');

  if (!$conn) die("Błąd połączenia z bazą danych: " . mysqli_connect_error());

  if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
  }

  if (isset($_GET['id'])) {
    $resourceId = $_GET['id'];

    $username = $_SESSION['username'];
    $query = "SELECT * FROM resources WHERE id = '$resourceId' AND username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 0) {
      header("Location: resource.php");
      exit;
    }

    $row = mysqli_fetch_assoc($result);
    $resourceName = $row['name'];
    $resourceContent = $row['content'];
    $resourceTags = $row['tags'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
      $newResourceName = $_POST['resource_name'];
      $newResourceContent = $_POST['resource_content'];
      $newResourceTags = implode(',', $_POST['tags']);

      $query = "SELECT * FROM resources WHERE username = '$username' AND name = '$newResourceName' AND id != '$resourceId'";
      $result = mysqli_query($conn, $query);
      if (mysqli_num_rows($result) > 0) {
        echo "Masz już zasób o takiej nazwie!";
      }
      else {
        $updateQuery = "UPDATE resources SET name = '$newResourceName', content = '$newResourceContent', tags = '$newResourceTags' WHERE id = '$resourceId'";
        if (mysqli_query($conn, $updateQuery)) {
          header("Location: resource.php?id=$resourceId");
          exit;
        } else {
          $_SESSION['error'] = "Błąd podczas edycji zasobu.";
        }
      }  
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
      echo "<script>";
      echo "if (confirm('Czy na pewno chcesz usunąć zasób?')) {";
      echo "  window.location.href = 'edit.php?id=$resourceId&action=delete';";
      echo "} else {";
      echo "  window.location.href = 'resource.php?id=$resourceId';";
      echo "}";
      echo "</script>";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
      $query = "DELETE FROM resources WHERE id = '$resourceId'";
      if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit;
      } else {
        $_SESSION['error'] = "Błąd podczas usuwania zasobu.";
        header("Location: resource.php?id=$resourceId");
        exit;
      }
    }

    $tagsQuery = "SELECT * FROM tags";
    $tagsResult = mysqli_query($conn, $tagsQuery);

    mysqli_close($conn);
    ?>

    <div class="container">
      <h1>Edytuj zasób</h1>
      <form class="form-container" method="POST" action="">
        <label for="resource_name">Nazwa zasobu:</label>
        <input type="text" id="resource_name" name="resource_name" value="<?php echo $resourceName; ?>" required>
        <span id="name-error"></span><br>

        <label for="resource_content">Treść zasobu:</label>
        <textarea id="resource_content" name="resource_content" rows="5" required><?php echo $resourceContent; ?></textarea>

        <label for="tags">Tagi:</label>
        <?php
        while ($tagRow = mysqli_fetch_assoc($tagsResult)) {
          $tagId = $tagRow['id'];
          $tagName = $tagRow['name'];
          $checked = in_array($tagId, explode(',', $resourceTags)) ? 'checked' : '';
          echo "<div><input type='checkbox' name='tags[]' value='$tagId' $checked> $tagName</div>";
        }
        ?>

        <div class="button-container">
          <input type="submit" name="save" value="Zapisz">
          <input type="submit" name="delete" value="Usuń zasób">
          <a href="resource.php?id=<?php echo $resourceId; ?>" class="btn-back">Powrót</a>
        </div>
      </form>
    </div>

  <?php
  } else {
    header("Location: index.php");
    exit;
  }
  ?>
</body>
</html>
