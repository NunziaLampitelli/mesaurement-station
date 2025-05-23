<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // verify the mail
    $stmt = $conn->prepare("SELECT id, password, is_confirmed, token FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $is_confirmed, $existing_token);
        $stmt->fetch();

        if ($is_confirmed == 0) {
            echo "You need to confirm your email to access.";
            exit();
        }

        // verify password
        if (password_verify($password, $hashed_password)) {
            // it generates a unique token
            $token = bin2hex(random_bytes(32)); // 

            // updates token in the database
            $update_stmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
            $update_stmt->bind_param("si", $token, $id);
            $update_stmt->execute();

            // 
            $_SESSION['user_id'] = $id;
            $_SESSION['token'] = $token; // saves the token

            // redirects to protected page
            header("Location: index.php"); 
            exit();
        } else {
            echo "Wrong password.";
        }
    } else {
        echo "Email address not found.";
    }
}
?>

<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
