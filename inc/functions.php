<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "achievement_manager";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function addAchievement($title, $description) {
    global $conn;
    $sql = "INSERT INTO achievements (title, description) VALUES ('$title', '$description')";
    if ($conn->query($sql) === TRUE) {
        return "New achievement created successfully";
    } else {
        return "Error: " . $sql . "<br>" . $conn->error;
    }
}

function addAchievementWithState($title, $description, $state) {
    global $conn;
    $sql = "INSERT INTO achievements (title, description, state) VALUES ('$title', '$description', '$state')";
    $conn->query($sql);
}

function getAchievements() {
    global $conn;
    $sql = "SELECT * FROM achievements ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $achievements = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $achievements[] = $row;
        }
    }
    return $achievements;
}

function exportAchievements() {
    global $conn;
    $sql = "SELECT * FROM achievements";
    $result = $conn->query($sql);

    $achievements = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $achievements[] = $row;
        }
    }

    $file = fopen('achievements.txt', 'w');
    foreach ($achievements as $achievement) {
        fwrite($file, $achievement['title'] . '|' . $achievement['description'] . '|' . $achievement['state'] . "\n");
    }
    fclose($file);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename('achievements.txt'));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize('achievements.txt'));
    readfile('achievements.txt');
    exit;
}

function importAchievements($file) {
    global $conn;

    if ($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
        $content = file_get_contents($file['tmp_name']);
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (trim($line) != '') {
                list($title, $description, $state) = explode('|', $line);
                addAchievementWithState(trim($title), trim($description), trim($state));
            }
        }
    }
}

function deleteAllAchievements() {
    global $conn;
    $sql = "DELETE FROM achievements";
    $conn->query($sql);
}

function updateAchievementState($id, $state) {
    global $conn;
    $sql = "UPDATE achievements SET state='$state' WHERE id=$id";
    $conn->query($sql);
}

// Handle state update request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['state'])) {
    $id = $_POST['id'];
    $state = $_POST['state'];
    updateAchievementState($id, $state);
    echo "Achievement state updated.";
    exit;
}
?>
