<?php
include 'inc/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title']) && isset($_POST['description'])) {
    $userId = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    addAchievement($userId, $title, $description);
    exit;
}

if (isset($_GET['export'])) {
    if (isset($_SESSION['user_id'])) {
        exportAchievements($_SESSION['user_id']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['importFile'])) {
    $userId = $_SESSION['user_id'];
    importAchievements($userId, $_FILES['importFile']);
    header("Location: index.php");
    exit;
}

if (isset($_POST['deleteAll'])) {
    if (isset($_SESSION['user_id'])) {
        deleteAllAchievements($_SESSION['user_id']);
    }
    header("Location: index.php");
    exit;
}

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
        <div class="user-info">
            <img src="<?php echo htmlspecialchars($_SESSION['profile_picture'] ?? 'default_profile_picture.jpg'); ?>" alt="Profile Picture" class="profile-picture">
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <form action="index.php" method="post">
                <button type="submit" name="logout">Logout</button>
            </form>
            <a href="settings.php">Edit Profile</a>
        </div>
        <input type="text" id="searchBar" placeholder="Search Achievements">
        <div id="achievementsList">
            <!-- Achievements will be loaded here -->
        </div>
        <div id="counter">
            <!-- Counter will be updated here -->
        </div>
    </div>
    <div class="controls-container">
        <button id="addAchievementBtn">Add Achievement</button>
        <div id="achievementForm" style="display: none;">
            <input type="text" id="title" placeholder="Title">
            <textarea id="description" placeholder="Description"></textarea>
            <button id="saveAchievementBtn">Add</button>
        </div>
        <button id="exportAchievementsBtn" disabled>Export Achievements</button>
        <form id="importForm" method="POST" enctype="multipart/form-data">
            <div id="fileInputContainer">
                <input type="file" name="importFile" id="importFile" accept=".txt" required>
            </div>
            <button type="submit" id="importAchievementsBtn" disabled>Import Achievements</button>
            <label for="importFile">Choose File</label>
            <span id="fileName"></span>
        </form>
        <form id="deleteAllForm" method="POST" onsubmit="return confirmDelete();">
            <input type="hidden" name="deleteAll" value="true">
            <button type="submit">Delete All Achievements</button>
        </form>
        <select id="sortOptions">
            <option value="default">Default</option>
            <option value="titleAZ">Sort Title (A-Z)</option>
            <option value="titleZA">Sort Title (Z-A)</option>
            <option value="dateOldest">Sort Date (Oldest to Newest)</option>
            <option value="dateNewest">Sort Date (Newest to Oldest)</option>
        </select>
    </div>
    <script>
        const achievements = <?php echo json_encode($achievements); ?>;
    </script>
    <script src="js/main.js"></script>
</body>
</html>
