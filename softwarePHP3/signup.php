<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'databaseConnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // تحقق إذا البريد مسجل مسبقاً
    $checkQuery = "SELECT * FROM user WHERE email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $error = "This email is already registered";
    } else {
        // تشفير كلمة المرور
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // إدخال المستخدم الجديد
        $insertQuery = "INSERT INTO user (fullName, email, password) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sss", $fullName, $email, $hashedPassword);

        if ($insertStmt->execute()) {
            session_start(); // لتشغيل الجلسة
            $_SESSION['userId'] = $insertStmt->insert_id; // نخزن رقم المستخدم
            $_SESSION['userName'] = $fullName; // نخزن الاسم
            header("Location: Dashboard.php"); // نحوله مباشرة
            exit();
        } else {
            $error = "An error occurred while creating the account";
        }
        
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        /* Styling for a modern signup container */
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #ffdff4 0%, #b78d8c 50%);
    color: #333;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    transition: background 0.3s;
}


.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px;
}

.logo img {
    height: 100px;
    border: 1px solid #e8c5d5; 
    border-radius: 10px; 
    box-shadow: 0 0 10px 5px rgba(255, 223, 244, 0.5);
}

.logo span {
    font-size: 24px;
    font-weight: bold;
    color: rgba(71, 62, 62, 0.9);
}



nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
}

nav ul li a {
    color: rgba(71, 62, 62, 0.9);
    text-decoration: none;
    font-size: 16px;
    transition: color 0.3s;
}

nav ul li a:hover {
    color: #937c7c;
}

/* Hero Section */
.hero {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    flex-direction: column;
    padding-top: 80px;
}

.hero h1 {
    font-size: 36px;
    margin-bottom: 20px;
}

.hero span {
    color: #876967;
}

.hero p {
    font-size: 18px;
    margin-bottom: 20px;
}

.btn {
    padding: 10px 20px;
    background:  #876967;
    color: #fff8f8;
    text-decoration: none;
    font-size: 18px;
     width: 100%;

    border-radius: 5px;
    transition: background 0.3s, transform 0.3s;
}

.btn:hover {
    background: #6b5654;
    transform: scale(1.05);
}

/* Footer */
footer {
    
    padding: 20px;
    position: fixed;
    bottom: 0;
    width: 100%;
    transition: background 0.3s;
    color: #ffdff4;
}


.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    flex-wrap: wrap;
}

.footer-links a {
    color:  rgba(71, 62, 62, 0.9);
    text-decoration: none;
    margin-left: 15px;
    transition: color 0.3s;
}

.footer-links a:hover {
    color: #937c7c;
}
        .signup-container {
            background: white;
            padding: 30px;
            width: 350px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .signup-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .signup-container label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin: 8px 0 5px;
        }

        .signup-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        
        .signup-container p {
            margin-top: 15px;
            font-size: 14px;
        }

        .signup-container a {
            color: #876967;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .signup-container a:hover {
            text-decoration: underline;
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
            <li><a href="logout.php">signout</a></li>

            </ul>
        </nav>
    </header>

    <div class="signup-container">
        <h1>Create an Account</h1>
        <form id="signupForm" method="POST" action="">
    <label for="name">Full Name</label>
    <input type="text" name="fullName" id="fullName" required>

    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>

    <label for="password">Password</label>
    <input type="password" name="password" id="password" required>

    <label for="confirmPassword">Confirm Password</label>
    <input type="password" name="confirmPassword" id="confirmPassword" required>

    <button type="submit" class="btn">Sign Up</button>
    <p>Already have an account? <a href="login.php">Log in</a></p>
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

</body>
</html>
