<?php
session_start();
include 'databaseConnection.php';


// Get user ID from session (in real app, set during login)
$userId = $_SESSION['userId'] ?? 2; // Default to 2 for testing

// Get user's cart ID and current total
$cartQuery = "SELECT c.cartId, c.totalPrice 
              FROM cart c
              JOIN usercart uc ON c.cartId = uc.cartId
              WHERE uc.userId = $userId";
$cartResult = mysqli_query($conn, $cartQuery);
$cartData = mysqli_fetch_assoc($cartResult);
$cartId = $cartData['cartId'] ?? 0;

// Remove book from cart
if (isset($_POST['selectedToRemoveBooks']) && isset($_POST['remove'])) {
    foreach ($_POST['selectedToRemoveBooks'] as $bookId) {
        $bookId = (int)$bookId; // Ensure it's an integer for security
        $updateBook = "UPDATE book SET cartId = NULL, bookStatus = 'In stock' 
                       WHERE bookId = $bookId AND cartId = $cartId";
        mysqli_query($conn, $updateBook);
        
        unset($_SESSION['book_added_' . $bookId]); // For dashboard
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkoutBookOwnerId'])) {
    $_SESSION['selectedBookOwner'] = $_POST['checkoutBookOwnerId'];
}

// Handle checkout process
if ( isset($_POST['checkout'])) {
    mysqli_begin_transaction($conn);
    $bookOwner = $_SESSION['selectedBookOwner'];

    try {
        // 1. Process all books in cart
        $booksQuery = "SELECT bookId FROM book WHERE cartId = $cartId AND userId = $bookOwner";
        $booksResult = mysqli_query($conn, $booksQuery);

        while ($book = mysqli_fetch_assoc($booksResult)) {
            $bookId = $book['bookId'];

            // Complete related exchange requests
            $completeExchanges = "UPDATE exchangerequest 
                                  SET status = 'Completed' 
                                  WHERE (bookToExchange = $bookId OR bookToExchangeWith = $bookId)
                                  AND status = 'Accepted'";
            mysqli_query($conn, $completeExchanges);

            // Mark book as out of stock
            $updateBook = "UPDATE book SET bookStatus = 'Out of stock' WHERE bookId = $bookId";
            mysqli_query($conn, $updateBook);
        }

        // 2. Clear the cart (instead of creating new one)
        $clearCart = "UPDATE book SET cartId = NULL WHERE cartId = $cartId AND userId = $bookOwner";
        mysqli_query($conn, $clearCart);

        // 3. Reset cart total to zero
        $resetTotal = "UPDATE cart SET totalPrice = 0.00 WHERE cartId = $cartId";
        mysqli_query($conn, $resetTotal);


        mysqli_commit($conn);
        header("Location: CartPage.php?checkout=success");
        echo $_SESSION['selectedBookOwner'];
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: CartPage.php?error=checkout_failed");
        exit();
    }
}

//Handle rating submission
if ($_POST['submit_rating'] == 'Submit Rating') {
    $ratingValue = (int)$_POST['rating'];
    $bookOwner = $_SESSION['selectedBookOwner'];

    // Insert rating
    $insertRating = "INSERT INTO rating (ratingValue, userId) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $insertRating);
    mysqli_stmt_bind_param($stmt, "ii", $ratingValue, $bookOwner);
    mysqli_stmt_execute($stmt);

    header("Location: Dashboard.php?rated=1");
    exit();
}

$bookOwnersQuery = "SELECT userId 
                    FROM book 
                    WHERE cartId = $cartId 
                    GROUP BY userId";
$bookOwnersResult = mysqli_query($conn, $bookOwnersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ShelfTrade</title>
    <link rel="stylesheet" href="Style/cartStyle.css">
</head>
<body>
    <a href="Dashboard.php" class="dashboard-btn">Back To Dashboard</a>
    
    <div class="container">
        <h1>🛒 Your Shopping Cart</h1>
        <form method="POST" class="submition">
            <?php while( $row = mysqli_fetch_assoc($bookOwnersResult)) { 
                $selectedbookOwner = $row['userId'];
                // Get current cart items
                $itemsQuery = "SELECT bookId, image, title, price, userId 
                               FROM book 
                               WHERE cartId = $cartId AND userId = $selectedbookOwner";
                $itemsResult = mysqli_query($conn, $itemsQuery);
                ?>
                <div class="books-container">
                    <input type="radio" name="checkoutBookOwnerId" value="<?= $selectedbookOwner; ?>" required <?= (isset($_SESSION['selectedBookOwner']) && $_SESSION['selectedBookOwner'] == $selectedbookOwner) ? 'checked' : ''; ?> >
                    <div class="bookOwner">
                        <?php if (mysqli_num_rows($itemsResult) > 0): ?>
                            <?php foreach ($itemsResult as $item): ?>
                                <div class="selectedToRemoveBooks">
                                    <input type="checkbox" name="selectedToRemoveBooks[]" value="<?= $item['bookId']; ?>">
                                    <div class="book-card">
                                        <img src="imags/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                        <div class="book-info">
                                            <h3 class="book-title"><?= htmlspecialchars($item['title']) ?></h3>
                                            <p class="book-price"><?= number_format($item['price'], 2) ?> SAR</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-cart">
                                <p>Your cart is empty</p>
                            </div>
                        <?php endif; ?>
                    </div>    
                </div>
            <?php } 
                $totalPrice = 0;
                if(isset($_POST['total'])) {
                    $selectedbookOwner = $_SESSION['selectedBookOwner'];
                    $totalPriceQuery = "SELECT SUM(price) AS total
                                        FROM book 
                                        WHERE cartId = $cartId AND userId = $selectedbookOwner";
                    $totalPriceResult = mysqli_query($conn, $totalPriceQuery);
                    $totalPriceSet = mysqli_fetch_assoc($totalPriceResult);
                    $totalPrice = $totalPriceSet['total'] ?? 0;

                    // Update cart total if items exist
                    if ($cartId > 0) {
                        $updateTotal = "UPDATE cart SET totalPrice = $totalPrice WHERE cartId = $cartId";
                        mysqli_query($conn, $updateTotal);
                    } 
                }
            ?>
            <div class="cart-summary">
                <h2 id="total-price">Total: <?= number_format($totalPrice, 2) ?> SAR</h2>
                <input type="submit" name="remove" value="Remove Selected Books">                
                <input type="submit" name="total" value="Calculate Total Price">
                <input type="submit" name="checkout" value="Checkout (Cash Only)">
            </div>
        </form>
    </div>

    <!-- Rating Modal -->
    <?php if ( $_GET['checkout'] == 'success' ): ?>
        <div id="rating-modal" class="modal" style="display: block;">
            <div class="modal-content">
                <h2>Checkout Completed!</h2>
                <br>
                <h3>Rate Your Experience</h3>
                <p>Please rate your purchase:</p>
                <form method="POST">
                    <div class="stars">
                        <span class="star" data-value="1" onclick="selectRating(1)">★</span>
                        <span class="star" data-value="2" onclick="selectRating(2)">★</span>
                        <span class="star" data-value="3" onclick="selectRating(3)">★</span>
                        <span class="star" data-value="4" onclick="selectRating(4)">★</span>
                        <span class="star" data-value="5" onclick="selectRating(5)">★</span>
                    </div>
                    <br>
                    <input type="hidden" name="rating" id="rating-value" value="0">
                    <input type="submit" name="submit_rating" value="Submit Rating" class="buy-btn">
                    <button type="button" onclick="closeModal()" style="min-width: 140px;">Skip</button>
                </form>
            </div>
        </div>
    <?php         
        endif;         
    ?>

    <script>
        function selectRating(value) {
            document.getElementById('rating-value').value = value;
            const stars = document.querySelectorAll('.star');
            stars.forEach(star => {
                star.classList.toggle('selected', star.getAttribute('data-value') <= value);
            });
        }
        
        function closeModal() {
            window.location.href = 'Dashboard.php';
        }
        
        <?php if (isset($_GET['rated'])): ?>
            alert('Thank you for your rating!');
            window.history.replaceState(null, null, window.location.pathname);
        <?php endif; ?>
    </script>
</body>
</html>