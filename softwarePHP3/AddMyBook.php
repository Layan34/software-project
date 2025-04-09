<?php
session_start();
include 'databaseConnection.php';



if (!isset($_SESSION['userId'])) {
    header("Location: login.php"); // أو أي صفحة تسجيل دخول عندك
    exit();
}

$userId = $_SESSION['userId'];



/***  check if editing ***/
$editing = false;
$book = [
    'title' => '',
    'description' => '',
    'price' => '',
    'category' => '',
    'image' => ''
];

/*** Handle book editing if an ID is passed ***/
if (isset($_GET['id'])) {
    $editing = true;
    $bookId = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM book WHERE bookId = ? AND userId = ?");
    $stmt->bind_param("ii", $bookId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $book = $result->fetch_assoc();
    } else {
        echo "<p>⚠️ This book could not be found or you do not have permission to edit it.</p>";
        exit();
    }
}

/*** Handle form submission (Add or Update) ***/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['bookTitle'];
    $desc = $_POST['bookDescription'];
    $price = $_POST['bookPrice'];
    $category = $_POST['bookCategory'];
    $status = 'In stock';

    /*** Handle image upload ***/
    $image = $book['image'];
    if (!empty($_FILES['bookImage']['name'])) {
        $image = basename($_FILES['bookImage']['name']);
        move_uploaded_file($_FILES['bookImage']['tmp_name'], "imags/$image");
    }

    if ($editing) {
        $stmt = $conn->prepare("UPDATE book SET title=?, description=?, price=?, category=?, image=? WHERE bookId=? AND userId=?");
        $stmt->bind_param("ssdssii", $title, $desc, $price, $category, $image, $bookId, $userId);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO book (title, description, price, category, image, bookStatus, userId) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsssi", $title, $desc, $price, $category, $image, $status, $userId);
        $stmt->execute();
    }

    header("Location: MyBooks.php");
    exit();
}
?>

<!-- *** HTML  *** -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $editing ? 'Edit Book' : 'Add a Book' ?></title>
  <link rel="stylesheet" href="Style/AddMyBook.css">
</head>
<body>
  <header>
    <div class="logo">
      <img src="imags/logo.jpg" alt="ShelfTrade Logo">
    </div>
    <nav>
      <ul>
        <li><a href="Dashboard.php">Dashboard</a></li>
        <li><a href="MyBooks.php">My Book</a></li>
        <li><a href="ExchangeRequests.php">Exchange Requests</a></li>
        <li><a href="logout.php">Logout</a></li>


      </ul>
    </nav>
  </header>

  <div class="container">
    <h2><?= $editing ? 'Edit Your Book' : 'Add a New Book' ?></h2>
    <?php
    /*** Determine image preview path ***/
    $imagePath = (!empty($book['image']) && $editing) ? 'imags/' . $book['image'] : 'imags/upload_icon_transparent.png';
    ?>

    <form method="POST" enctype="multipart/form-data">
      <label for="bookImage">Book Image:</label>
      <label class="image-upload" for="bookImage">
        <img id="previewImage" src="<?= $imagePath ?>" alt="Upload Icon">
        <span id="imageText">Click to upload an image</span>
      </label>
      <input type="file" id="bookImage" name="bookImage" accept="image/*">

      <label for="bookTitle">Book Title:</label>
      <input type="text" name="bookTitle" value="<?= htmlspecialchars($book['title']) ?>" required>

      <label for="bookDescription">Book Description:</label>
      <textarea name="bookDescription" required><?= htmlspecialchars($book['description']) ?></textarea>

      <label for="bookCategory">Book Category:</label>
      <select name="bookCategory" required>
        <option value="">-- Select Category --</option>
        <?php
          $categories = ['Literature', 'History', 'Novels', 'Philosophy'];
          foreach ($categories as $cat) {
              $selected = $book['category'] == $cat ? 'selected' : '';
              echo "<option value='$cat' $selected>$cat</option>";
          }
        ?>
      </select>

      <label for="bookPrice">Book Price (SAR):</label>
      <input type="number" name="bookPrice" value="<?= htmlspecialchars($book['price']) ?>" required>

      <button type="submit" class="btn"><?= $editing ? 'Update Book' : 'Add Book' ?></button>
    </form>
  </div>

  <footer>
    <div class="footer-content">
        <p style="color:rgba(71, 62, 62, 0.9)">&copy; 2025 ShelfTrade. All Rights Reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </div>
  </footer>

 
  <script>
    document.getElementById("bookImage").addEventListener("change", function (event) {
      const file = event.target.files[0];
      const preview = document.getElementById("previewImage");
      const text = document.getElementById("imageText");

      if (file) {
          const reader = new FileReader();
          reader.onload = function (e) {
              preview.src = e.target.result;
              text.style.display = "none";
          };
          reader.readAsDataURL(file);
      } else {
          preview.src = "imags/upload_icon_transparent.png";
          text.style.display = "block";
      }
    });
  </script>
</body>
</html>
