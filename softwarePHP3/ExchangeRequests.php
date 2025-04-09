<?php
    include'databaseConnection.php';
    
    
    session_start();
    $userId = $_SESSION['userId'] ?? 2; // Default to 2 for testing
    
    
    
    $requestReceivedSql = "SELECT requestId, bookToExchange, bookToExchangeWith
                           FROM exchangerequest
                           WHERE receiverId = $userId AND status = 'Pending' ";
    $requestReceived = mysqli_query($conn,$requestReceivedSql);
    
    

    $requestSentSql = "SELECT requestId, status, bookToExchange, bookToExchangeWith
                       FROM exchangerequest
                       WHERE senderId = $userId";
    $requestSent = mysqli_query($conn,$requestSentSql);
    
    
    
    if(isset($_POST['submit'])) {
        $requestId = $_POST['requestId'];
        $requestStatus = $_POST['requestStatus'];
        $bookToExchange = $_POST['bookToExchange'];
        $bookToExchangeWith = $_POST['bookToExchangeWith'];
        $senderId = $_POST['senderId'];
        
        
        
        $cartSql = "SELECT cartId
                    FROM usercart
                    WHERE userId = $userId";
        $cartResult = mysqli_query($conn,$cartSql);
        $cartId = mysqli_fetch_assoc($cartResult);
        $userCart = $cartId['cartId'];
        
        
        
        $cartSql = "SELECT cartId
                    FROM usercart
                    WHERE userId = $senderId";
        $cartResult = mysqli_query($conn,$cartSql);
        $cartId = mysqli_fetch_assoc($cartResult);
        $senderCart = $cartId['cartId'];
        
        
        
        if($requestStatus == 'Rejected') {
            $requestStatusSql = "UPDATE exchangerequest
                                 SET status = 'Rejected'
                                 WHERE requestId = $requestId";
            mysqli_query($conn,$requestStatusSql);
        } else {
            $requestStatusSql = "UPDATE exchangerequest
                                 SET status = 'Accepted'
                                 WHERE requestId = $requestId";
            mysqli_query($conn,$requestStatusSql);
            
            $buySql = "UPDATE book
                       SET cartId = $userCart, bookStatus = 'Out of stock'
                       WHERE bookId = $bookToExchange";
            mysqli_query($conn, $buySql);

            $buySql = "UPDATE book
                       SET cartId = $senderCart, bookStatus = 'Out of stock'
                       WHERE bookId = $bookToExchangeWith";
            mysqli_query($conn, $buySql);
        } 
        echo "<script>
                alert('Request status updated successfully!');
                window.location.href = 'ExchangeRequests.php';
              </script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exchange Requests</title>
    <link rel="stylesheet" href="Style/ExchangeRequests.css">
    <style>

        main {
            margin: 5rem;
            display: flex;
            justify-content: space-between;
        }

        .books-container {
            display: flex;
            width: 650px;
            flex-direction: column;
            align-items: center;
            max-width: 100%;
            padding: 1rem;
            border-radius: 12px;
            border: 3px solid rgb(182, 140, 139);
            background:rgba(255, 255, 255, 0.605);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: 1rem;
        }
        /*form*/
        #receivedRequests, #sentRequests{
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 100%;
            padding: 1rem;
            border-radius: 12px;
            border: 3px solid rgb(182, 140, 139);
            background:white;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: 1rem;
        }
        
        #receivedRequests div, #sentRequests div {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        
        #receivedRequests div div, #sentRequests div div{
            margin: 10px;
        }

        .title {
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 30px;
            margin: 1rem;
        }
    </style>
</head>
<body>
    <header>
	<div class="logo">
            <img src="imags/logo.jpg" alt="ShelfTrade Logo">
        </div>
		<nav>
            <ul>
                <li><a href="AddMyBook.php">Add Book</a></li>
                <li><a href="MyBooks.php">My Book</a></li>
                <li><a href="Dashboard.php">Dashboard</a></li>
                <li><a href="shelfTrade.html">Logout</a></li>
            </ul>
        </nav>
    </header>
	
    <main>
        <section class="books-container">
            <h2>Exchange Requests Received</h2>
            <?php if(mysqli_num_rows($requestReceived) != 0) {?>
                <?php while ($row = mysqli_fetch_assoc($requestReceived)) { ?>
                    <form action="ExchangeRequests.php" method="POST" id="receivedRequests">
                        <div>
                            <?php
                                $bookId = $row['bookToExchangeWith'];
                                $bookSql = "SELECT bookId, image, title, description, price, category, userId
                                            FROM book
                                            WHERE bookId = $bookId";
                                $bookset = mysqli_query($conn,$bookSql);
                                $book = mysqli_fetch_assoc($bookset);
                            ?>
                            <div class="book-card">
                                <a href="Profile.php?id=<?php echo $book['userId']; ?>"> <img src="imags/profile.jpg" alt="profile img" style="width: 40px; height: 40px; border-radius: 50%; position: relative; right: 90px;"> </a>
                                <img src="imags/<?php echo $book['image']; ?>" alt="Book img">
                                <h3 class="book-title" dir="rtl"><?php echo $book['title']; ?></h3>
                                <p class="book-price"><?php echo $book['price']; ?> SAR</p>
                                <p class="book-category">Category: <?php echo $book['category']; ?></p>
                                <p class="book-description"><?php echo $book['description']; ?></p>
                            </div>

                            <?php
                                $bookId = $row['bookToExchange'];
                                $bookSql = "SELECT bookId, image, title, description, price, category, userId
                                            FROM book
                                            WHERE bookId = $bookId";
                                $bookset = mysqli_query($conn,$bookSql);
                                $book = mysqli_fetch_assoc($bookset);
                            ?>
                            <div class="book-card">
                                <a href="Profile.php?id=<?php echo $book['userId']; ?>"> <img src="imags/profile.jpg" alt="profile img" style="width: 40px; height: 40px; border-radius: 50%; position: relative; right: 90px;"> </a>
                                <img src="imags/<?php echo $book['image']; ?>" alt="Book img">
                                <h3 class="book-title" dir="rtl"><?php echo $book['title']; ?></h3>
                                <p class="book-price"><?php echo $book['price']; ?> SAR</p>
                                <p class="book-category">Category: <?php echo $book['category']; ?></p>
                                <p class="book-description"><?php echo $book['description']; ?></p>
                            </div>
                        </div>

                        <div>
                            <ul style="list-style-type: none;">
                                <li><input type="radio" name="requestStatus" value="Accepted" checked> Accept</li>
                                <li><input type="radio" name="requestStatus" value="Rejected"> Reject</li>
                            </ul>
                        </div>

                        <input type="hidden" name="requestId" value="<?php echo $row['requestId']; ?>">
                        <input type="hidden" name="senderId" value="<?php echo $book['userId']; ?>">
                        <input type="hidden" name="bookToExchange" value="<?php echo $row['bookToExchange']; ?>">
                        <input type="hidden" name="bookToExchangeWith" value="<?php echo $row['bookToExchangeWith']; ?>">

                        <input type="submit" name="submit" value="Submit">
                    </form>
                <?php } ?>
            <?php } else { echo "<p style=\"text-align: center; padding-top: 120px;\">No exchange requests received.</p>" ; }?>
        </section>

        <section class="books-container">
            <h2>Exchange Requests Sent</h2>
            <?php if(mysqli_num_rows($requestSent) != 0) {?>
                <?php while ($row = mysqli_fetch_assoc($requestSent)) { ?>
                    <div id="sentRequests">
                        <div>
                            <?php
                                $bookId = $row['bookToExchangeWith'];
                                $bookSql = "SELECT bookId, image, title, description, price, category, userId
                                            FROM book
                                            WHERE bookId = $bookId";
                                $bookset = mysqli_query($conn,$bookSql);
                                $book = mysqli_fetch_assoc($bookset);
                            ?>
                            <div class="book-card">
                                <a href="Profile.php?id=<?php echo $book['userId']; ?>"> <img src="imags/profile.jpg" alt="profile img" style="width: 40px; height: 40px; border-radius: 50%; position: relative; right: 90px;"> </a>
                                <img src="imags/<?php echo $book['image']; ?>" alt="Book img">
                                <h3 class="book-title" dir="rtl"><?php echo $book['title']; ?></h3>
                                <p class="book-price"><?php echo $book['price']; ?> SAR</p>
                                <p class="book-category">Category: <?php echo $book['category']; ?></p>
                                <p class="book-description"><?php echo $book['description']; ?></p>
                            </div>

                            <?php
                                $bookId = $row['bookToExchange'];
                                $bookSql = "SELECT bookId, image, title, description, price, category, userId
                                            FROM book
                                            WHERE bookId = $bookId";
                                $bookset = mysqli_query($conn,$bookSql);
                                $book = mysqli_fetch_assoc($bookset);
                            ?>
                            <div class="book-card">
                                <a href="Profile.php?id=<?php echo $book['userId']; ?>"> <img src="imags/profile.jpg" alt="profile img" style="width: 40px; height: 40px; border-radius: 50%; position: relative; right: 90px;"> </a>
                                <img src="imags/<?php echo $book['image']; ?>" alt="Book img">
                                <h3 class="book-title" dir="rtl"><?php echo $book['title']; ?></h3>
                                <p class="book-price"><?php echo $book['price']; ?> SAR</p>
                                <p class="book-category">Category: <?php echo $book['category']; ?></p>
                                <p class="book-description"><?php echo $book['description']; ?></p>
                            </div>                       
                        </div>
                        <button id="state"><?php echo $row['status']; ?></button>
                    </div>
                <?php } ?>
            <?php } else { echo "<p style=\"text-align: center; padding: 120px;\">No exchange requests sent.</p>" ; }?>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p style="color:rgba(71, 62, 62, 0.9)">&copy; 2025 ShelfTrade. All Rights Reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </footer>
</body>
</html>
