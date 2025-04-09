<?php
    include'databaseConnection.php';

    
    
    $userId = $_GET['id'];

    
    
    $userInfoSql = "SELECT fullName, email
                    FROM user
                    WHERE userId = $userId";
    $userInfoResult = mysqli_query($conn, $userInfoSql);
    $userInfo = mysqli_fetch_assoc($userInfoResult);
    
    
    
    $userRatings = "SELECT ratingValue
                     FROM rating
                     WHERE userId = $userId";
    $userRatingResult = mysqli_query($conn, $userRatings);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="Style/MyBooks.css">
    <style>
        .ratings {
            text-align: left;
            background: white;
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #b78d8c;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .rating {
            padding: 5px;
            display: flex;
        }
        
        .rating div {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .rating div div{
            display: flex;
            flex-direction: row;
        }
    </style>
</head>
<body>
    <a href="Dashboard.php" class="dashboard-btn">Back To Dashboard</a>

    <div class="container">
        <h2>Book Owner Profile</h2>
        <div id="booksList" class="books-list">
            <div class="book-item">
                <img src="imags/profile.jpg" alt="profile img" style="box-shadow: 0 0 10px 5px rgba(255, 223, 244, 0.5);">
                <div class="book-details">
                    <div class="book-title"><?php echo $userInfo['fullName'];?></div>
                    <div class="book-description"><strong>Email: </strong><?php echo $userInfo['email'];?></div>
                </div>
            </div>
            
            <div class="ratings">
                <?php if(mysqli_num_rows($userRatingResult) != 0) {?>
                    <?php $row = mysqli_fetch_assoc($userRatingResult) ?>
                        <div class="rating">
                            <img src="imags/userProfile.jpg" alt="profile img" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                            <div>
                                **** ****
                                <div>
                                    <?php
                                       $totalStars = 5;
                                       $filledStars = $row['ratingValue'];
                                       $emptyStars = $totalStars - $filledStars;

                                       echo str_repeat('<span style="color: gold; font-size: 20px;">★</span>', $filledStars);
                                       echo str_repeat('<span style="color: gray; font-size: 20px;">☆</span>', $emptyStars);
                                   ?>
                                </div>
                            </div>
                        </div>   

                    <?php while($row = mysqli_fetch_assoc($userRatingResult)) { ?>
                        <hr>
                        <div class="rating">
                            <img src="imags/userProfile.jpg" alt="profile img" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                            <div>
                                **** ****
                                <div>
                                    <?php
                                       $totalStars = 5;
                                       $filledStars = $row['ratingValue'];
                                       $emptyStars = $totalStars - $filledStars;

                                       echo str_repeat('<span style="color: gold; font-size: 20px;">★</span>', $filledStars);
                                       echo str_repeat('<span style="color: gray; font-size: 20px;">☆</span>', $emptyStars);
                                   ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { echo "<p style=\"text-align: center;\">No ratings found for this owner.</p>" ; }?>
            </div>
        </div>
    </div>

</body>
</html>
