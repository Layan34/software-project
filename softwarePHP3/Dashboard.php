<?php
    include'databaseConnection.php';
    
    
    
    session_start();
    $userId = $_SESSION['userId'] ?? 2; // Default to 2 for testing

    
    
    $bookSql = "SELECT bookId, image, title, description, price, category, userId
                FROM book
                WHERE bookStatus = 'In stock' AND userId != $userId";
    $books = mysqli_query($conn,$bookSql);
    
    
    
    $usernameSql = "SELECT fullName 
                    FROM user
                    WHERE userId = $userId";
    $usernameResult = mysqli_query($conn, $usernameSql);
    $username = mysqli_fetch_assoc($usernameResult);
    $fullName = $username['fullName'];
    $firstName = explode(" ", $fullName)[0];
    
    
    
    $cartSql = "SELECT cartId
                FROM usercart
                WHERE userId = $userId";
    $cartResult = mysqli_query($conn,$cartSql);
    $cartSet = mysqli_fetch_assoc($cartResult);
    $cart = $cartSet['cartId'];
    
unset($_SESSION['book_added_' .'2']);
    
    $bookId = isset($_GET['bookId']) ? $_GET['bookId'] : "null";
    if($bookId !== null && !isset($_SESSION['book_added_' . $bookId])) {
        $buySql = "UPDATE book
                   SET cartId = $cart, bookStatus = 'Out of stock'
                   WHERE bookId = $bookId";
        mysqli_query($conn, $buySql);
        
        $_SESSION['book_added_' . $bookId] = true;
        
        echo "<script>
                alert('Book added to cart successfully!');
                window.location.href = 'Dashboard.php';
              </script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Book Exchange & Purchase</title>
    <link rel="stylesheet" href="Style/dashStyle.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="imags/logo.jpg" alt="ShelfTrade Logo">
        </div>
        <nav>
            <a href="CartPage.php" class="to-cart">🛒</a>
            <ul>
                <li><a href="AddMyBook.php">Add Book</a></li>
                <li><a href="MyBooks.php">My Book</a></li>
                <li><a href="ExchangeRequests.php">Exchange Requests</a></li>
                <li><a href="shelfTrade.html">Logout</a></li>
               
            </ul>
        </nav>
    </header>

    <div class="welcome-container">
        <p class="welcome-message">Welcome <?php echo $firstName; ?></p>
    </div>

    <!-- Banner Section -->
    <div class="banner">
        <img src="imags/panner2.png" alt="Book Exchange">
        <h1>Discover & Exchange Books</h1>
    </div>


    <div class="container">
        <div class="Search-Filter">
            <input type="text" class="search-bar" placeholder="Search for a book...">
            <select class="filter">
                <option value="all">All Categories</option>
                <option value="literature">Literature</option>
                <option value="history">History</option>
                <option value="novels">Novels</option>
                <option value="philosophy">Philosophy</option>
            </select>
        </div>
        
        <main class="books-container">
            <?php while ($row = mysqli_fetch_assoc($books)) { ?>
                <div class="book-card">
                    <a href="Profile.php?id=<?php echo $row['userId']; ?>"> <img src="imags/profile.jpg" alt="profile img" style="width: 40px; height: 40px; border-radius: 50%; position: relative; right: 110px;"> </a>
                    <img src="imags/<?php echo $row['image']; ?>" alt="Book img">
                    <h3 class="book-title" dir="rtl"><?php echo $row['title']; ?></h3>
                    <p class="book-price"><?php echo $row['price']; ?> SAR</p>
                    <p class="book-category">Category: <?php echo $row['category']; ?></p>
                    <p class="book-description"><?php echo $row['description']; ?></p>
                    <div>
                        <a href="Dashboard.php?bookId=<?php echo $row['bookId']; ?>">
                            <button>Buy</button>
                        </a>
                        <a href="SendRequest.php?bookOwnerId=<?php echo $row['userId']; ?>&bookId=<?php echo $row['bookId']; ?>">
                            <button>Request Exchange</button>
                        </a>
                    </div>
                </div>
            <?php } ?>
        </main>

    </div>

    <!--search bar-->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const searchBar = document.querySelector(".search-bar");
            const books = document.querySelectorAll(".book-card");
    
            searchBar.addEventListener("keyup", function () {
                const searchText = searchBar.value.toLowerCase();
    
                books.forEach(book => {
                    const title = book.querySelector(".book-title").textContent.toLowerCase();
                    
                    if (title.includes(searchText)) {
                        book.style.display = "block";
                    } else {
                        book.style.display = "none";
                    }
                });
                const visibleBooks = Array.from(books).some(book => book.style.display === "block");
                if (!visibleBooks) {
                    document.querySelector(".no-results")?.remove();
                    const noResults = document.createElement("p");
                    noResults.classList.add("no-results");
                    noResults.textContent = "no result";
                    noResults.style.color = "plack";
                    noResults.style.fontSize = "18px";
                    noResults.style.fontWeight = "bold";
                    document.querySelector(".books-container").appendChild(noResults);
                } else {
                    document.querySelector(".no-results")?.remove();
                }
            });
        });
    </script>

      <!--filter-->
      <script>
        document.addEventListener("DOMContentLoaded", function () {
            const searchBar = document.querySelector(".search-bar");
            const filterSelect = document.querySelector(".filter");
            const books = document.querySelectorAll(".book-card");
    
            function filterBooks() {
                const searchText = searchBar.value.toLowerCase();
                const selectedCategory = filterSelect.value.toLowerCase();
    
                let hasResults = false;
    
                books.forEach(book => {
                    const title = book.querySelector(".book-title").textContent.toLowerCase();
                    const category = book.querySelector(".book-category").textContent.toLowerCase();
                    const description = book.querySelector(".book-description").textContent.toLowerCase();
    
                    // Check if the book matches the search and category filter
                    const matchesSearch = title.includes(searchText) || description.includes(searchText);
                    const matchesCategory = selectedCategory === "all" || category.includes(selectedCategory);
    
                    if (matchesSearch && matchesCategory) {
                        book.style.display = "block";
                        hasResults = true;
                    } else {
                        book.style.display = "none";
                    }
                });
    
                // Display "No results found" message if no books match
                document.querySelector(".no-results")?.remove();
                if (!hasResults) {
                    const noResults = document.createElement("p");
                    noResults.classList.add("no-results");
                    noResults.textContent = "No books found.";
                    noResults.style.color = "plack";
                    noResults.style.fontSize = "18px";
                    noResults.style.fontWeight = "bold";
                    document.querySelector(".books-container").appendChild(noResults);
                }
            }
    
            // Trigger filtering when typing in the search bar or selecting a category
            searchBar.addEventListener("keyup", filterBooks);
            filterSelect.addEventListener("change", filterBooks);
        });
    </script>

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
