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
    deleteAllAchievements();
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Achievement Manager</title>
</head>
<body>
    <div class="container">
        <h1>Achievement Manager</h1>
        <div id="achievementsList">
            <!-- Achievements will be loaded here -->
        </div>
    </div>
    <div class="controls-container">
        <button id="addAchievementBtn">Add Achievement</button>
        <div id="achievementForm" style="display: none;">
            <input type="text" id="title" placeholder="Title">
            <textarea id="description" placeholder="Description"></textarea>
            <button id="saveAchievementBtn">Add</button>
        </div>
        <button id="exportAchievementsBtn">Export Achievements</button>

        <form id="importForm" method="POST" enctype="multipart/form-data">
            <div id="fileInputContainer">
                <input type="file" name="importFile" id="importFile" accept=".txt" required>
                <label for="importFile">Choose File</label>
                <span id="fileName"></span>
            </div>
            <button type="submit">Import Achievements</button>
        </form>

        <form id="deleteAllForm" method="POST">
            <input type="hidden" name="deleteAll" value="true">
            <button type="submit">Delete All Achievements</button>
        </form>
    </div>
    <script>
        const achievements = <?php echo json_encode($achievements); ?>;
    </script>
    <script src="js/main.js"></script>
</body>
</html>
