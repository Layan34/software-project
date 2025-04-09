<?php
    include'databaseConnection.php';
    
    
    session_start();
    if (!isset($_SESSION['userId'])) {
        header("Location: login.html"); // أو أي صفحة تسجيل دخول عندك
        exit();
    }
    
    $userId = $_SESSION['userId'];
    
    $bookOwnerId = $_GET['bookOwnerId'];
    $bookId = $_GET['bookId'];
    
    
    $bookSql = "SELECT bookId, image, title
            FROM book
            WHERE userId = $userId AND bookStatus = 'In stock' ";
    $books = mysqli_query($conn,$bookSql);
    
    
    if( $_POST['selectedBookId'] != null) {
        $selectedBookId = $_POST['selectedBookId'];
        $exchangeRequestSql = "INSERT INTO `exchangerequest`(`senderId`, `receiverId`, `bookToExchange`, `bookToExchangeWith`)
                               VALUES ($userId,$bookOwnerId,$selectedBookId,$bookId)";
        mysqli_query($conn,$exchangeRequestSql);
        
        echo "<script>
                alert('Request sent successfully!');
                window.location.href = 'Dashboard.php';
              </script>";
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books</title>
    <link rel="stylesheet" href="Style/MyBooks.css">
    <style>
        .book-item {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .book-item div{
            display: flex;
            padding: 15px;
        }
    </style>
</head>
<body>
    <a href="Dashboard.php" class="dashboard-btn">Back To Dashboard</a>

    <div class="container">
        <h2>Select a book to exchange with</h2>
        <form action="" method="POST"" class="books-list">
            <div class="book-item">
                <?php while ($row = mysqli_fetch_assoc($books)) { ?>
                    <div>
                        <input type="radio" name="selectedBookId" value="<?php echo $row['bookId']; ?>" required>
                        <img src="imags/<?php echo $row['image']; ?>" alt="Book img" style="padding: 15px;">
                        <h3 class="book-title" dir="rtl"><?php echo $row['title']; ?></h3>
                    </div>
                <?php } ?>
            </div>
            <input type="submit" value="Submit" class="delete-all-btn">
        </form>
    </div>

</body>
</html>
