<?php

include 'databaseConnection.php';


session_start();
$userId = $_SESSION['userId'] ?? 2; // Default to 2 for testing

/*** Delete book and its image if 'delete' is in URL ***/
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];

    /*** Get image name from DB ***/
    $stmt = $conn->prepare("SELECT image FROM book WHERE bookId = ? AND userId = ?");
    $stmt->bind_param("ii", $deleteId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $image = $row['image'];
        $imagePath = "imags/$image";

        /*** Delete image file if exists ***/
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    /*** Delete book record from Our DB ***/
    $stmt = $conn->prepare("DELETE FROM book WHERE bookId = ? AND userId = ?");
    $stmt->bind_param("ii", $deleteId, $userId);
    $stmt->execute();

    /*** Refresh page after deletion ***/
    header("Location: MyBooks.php");
    exit();
}


$stmt = $conn->prepare("SELECT * FROM book WHERE userId = ? ORDER BY bookId DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Books</title>
  <link rel="stylesheet" href="Style/MyBooks.css">
  <style>
    .status-text {
      color: #c0392b;
      font-size: 14px;
      margin-top: 6px;
    }
    .book-price {
      color: #636161;
      font-weight: bold;
      font-size: 16px;
      margin-top: 8px;
    }
  </style>
</head>
<body>


  <a href="AddMyBook.php" class="add-book-btn">+ Add New Book</a>
  <a href="Dashboard.php" class="dashboard-btn">Back To Dashboard</a>

  <!-- *** Book List *** -->
  <div class="container">
    <h2>My Added Books</h2>
    <div id="booksList" class="books-list">

      <?php if (count($books) > 0): ?>
        <?php foreach ($books as $book): ?>
          <div class="book-item">
            <img src="imags/<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
            <div class="book-details">
              <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
              <div class="book-description"><?= htmlspecialchars($book['description']) ?></div>
              <div class="book-price"><?= htmlspecialchars($book['price']) ?> SAR</div>
              <?php if ($book['bookStatus'] === 'Out of stock'): ?>
                <p class="status-text">❌ This book is out of stock and cannot be edited.</p>
              <?php endif; ?>
            </div>
            <div class="buttons">
              <?php if ($book['bookStatus'] !== 'Out of stock'): ?>
                <a href="AddMyBook.php?id=<?= $book['bookId'] ?>" class="edit-btn">Edit</a>
              <?php endif; ?>
              <a href="MyBooks.php?delete=<?= $book['bookId'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="margin-top:20px;">You haven't added any books yet.</p>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
