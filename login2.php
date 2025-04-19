<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'koneksi.php';
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $query = "SELECT * FROM users WHERE email = ? AND password = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            header("Location: account.php");
            exit();
        } else {
            $error = "Email atau password salah!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Mengatur background image */
        body {
            font-family: Arial, sans-serif;
            position: relative;
            background: url('image/ID.png') no-repeat;
            background-size: cover;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Membuat box login */
        .login-box {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            color: black;
            width: 350px;
            text-align: center;
        }

        /* Style input dan button */
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .link {
            font-size: 14px;
            margin-top: 15px;
        }

        .link a {
            color: #007bff;
            text-decoration: none;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #333;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            font-size: 14px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .remember-forgot input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <div class="remember-forgot">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="#" class="link">Forgot Password?</a>
            </div>

            <button type="submit">Login</button>
            <p class="link">Don't have any accounts? <a href="register.php">Register</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
