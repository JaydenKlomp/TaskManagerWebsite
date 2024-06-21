<?php
include 'inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title']) && isset($_POST['description'])) {
    echo addAchievement($_POST['title'], $_POST['description']);
    exit;
}

if (isset($_GET['export'])) {
    exportAchievements();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['importFile'])) {
    importAchievements($_FILES['importFile']);
    header("Location: index.php");
    exit;
}

if (isset($_POST['deleteAll'])) {
    deleteAllAchievements();
    header("Location: index.php");
    exit;
}

$achievements = getAchievements();
?>
<?php
include 'inc/functions.php';
session_start();

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$achievements = [];
if (isset($_SESSION['user_id'])) {
    $achievements = getAchievementsByUser($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Achievement Manager</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
</head>
<body class="day">
    <button id="toggleModeBtn">Toggle Day/Night</button>
    <div class="container">
        <h1>Achievement Manager</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-info">
                <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture" class="profile-picture">
                <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
                <form action="index.php" method="post">
                    <button type="submit" name="logout">Logout</button>
                </form>
                <a href="settings.php">Edit Profile</a>
            </div>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
        <input type="text" id="searchBar" placeholder="Search Achievements">
        <div id="achievementsList">
            <!-- Achievements will be loaded here -->
        </div>
        <div id="counter">
            <!-- Counter will be updated here -->
        </div>
    </div>
    <div class="controls-container">
        <!-- Existing controls -->
    </div>
    <script>
        const achievements = <?php echo json_encode($achievements); ?>;
    </script>
    <script src="js/main.js"></script>
</body>
</html>
