<?php
include 'inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $profile_picture = 'default.jpg';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture = 'uploads/' . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    }

    if (registerUser($username, $password, $email, $profile_picture)) {
        header('Location: login.php');
        exit;
    } else {
        echo "Registration failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Register</title>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <form action="register.php" method="post" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="file" name="profile_picture" accept="image/*">
            <button type="submit">Register</button>
        </form>
        <a href="login.php">Already have an account? Login here.</a>
    </div>
</body>
</html>
