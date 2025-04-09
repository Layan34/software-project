<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'databaseConnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // نبحث فقط باستخدام البريد الإلكتروني
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // تحقق إذا المستخدم موجود
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // تحقق من كلمة المرور باستخدام password_verify
        if (password_verify($password, $user['password'])) {
            $_SESSION['userId'] = $user['userId'];
            $_SESSION['userName'] = $user['fullName'];
            header("Location: Dashboard.php");
            exit();
        } else {
            $error = "wrong password";
        }
    } else {
        $error = "The email address is not registered";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <style>
        /* Reset and Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #ffdff4 0%, #b78d8c 50%);
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Header */
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

        /* Login Container */
        .login-container {
            background: white;
            padding: 35px;
            width: 380px;
            border-radius: 12px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 120px; /* Ensures it appears below the header */
        }

        .login-container h1 {
            margin-bottom: 20px;
            font-size: 26px;
            color: #4a4a4a;
        }

        .login-container label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin: 10px 0 5px;
            font-size: 14px;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s ease-in-out;
        }

        .login-container input:focus {
            border-color: #876967;
            outline: none;
            box-shadow: 0px 0px 5px rgba(135, 105, 103, 0.5);
        }

        /* Button Styles */
        .login-container .btn {
            background-color: #876967;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s, transform 0.3s;
        }

        .login-container .btn:hover {
            background: #6b5654;
            transform: scale(1.05);
        }

        /* Link Styles */
        .login-container p {
            margin-top: 15px;
            font-size: 14px;
        }

        .login-container a {
            color: #876967;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .login-container a:hover {
            text-decoration: underline;
            color: #6b5654;
        }

/* Footer */
footer {
    
    padding: 20px;
    width: 100%;
    position: fixed;
    bottom: 0;
    left: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
   
}

/* Copyright text aligned in center */
.footer-content p {
    color: rgba(71, 62, 62, 0.9);
    margin: 0 auto;
}

/* Footer links aligned to the right */
.footer-links {
    margin-left: auto;
}

.footer-links a {
    color: rgba(71, 62, 62, 0.9);
    text-decoration: none;
    margin-left: 15px;
    transition: color 0.3s;
}

.footer-links a:hover {
    color: #937c7c;
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
            <li><a href="logout.php">Logout</a></li>

            </ul>
        </nav>
    </header>

    <div class="login-container">
    <h1>Log In</h1>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form id="loginForm" method="POST" action="">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required>

        <button type="submit" class="btn">Log In</button>
        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
    </form>
</div>



     <footer>
        <p style="color:rgba(71, 62, 62, 0.9)">&copy; 2025 ShelfTrade. All Rights Reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </footer>

</body>
</html>
