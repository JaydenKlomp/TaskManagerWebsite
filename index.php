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
        <input type="text" id="searchBar" placeholder="Search Achievements">
        <button id="exportAchievementsBtn" disabled>Export Achievements</button>
        <form id="importForm" method="POST" enctype="multipart/form-data">
            <div id="fileInputContainer">
                <input type="file" name="importFile" id="importFile" accept=".txt" required>
                <label for="importFile">Choose File</label>
                <span id="fileName"></span>
            </div>
            <button type="submit" id="importAchievementsBtn" disabled>Import Achievements</button>
        </form>
        <form id="deleteAllForm" method="POST" onsubmit="return confirmDelete();">
            <input type="hidden" name="deleteAll" value="true">
            <button type="submit">Delete All Achievements</button>
        </form>
        <button id="sortTitleAZBtn">Sort Title (A-Z)</button>
        <button id="sortTitleZABtn">Sort Title (Z-A)</button>
        <button id="sortDateOldestBtn">Sort Date (Oldest to Newest)</button>
        <button id="sortDateNewestBtn">Sort Date (Newest to Oldest)</button>
    </div>
    <script>
        const achievements = <?php echo json_encode($achievements); ?>;
    </script>
    <script src="js/main.js"></script>
</body>
</html>



