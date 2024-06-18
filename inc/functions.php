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
    $position = getMaxPosition() + 1;
    $sql = "INSERT INTO achievements (title, description, position) VALUES ('$title', '$description', $position)";
    if ($conn->query($sql) === TRUE) {
        return "New achievement created successfully";
    } else {
        return "Error: " . $sql . "<br>" . $conn->error;
    }
}

function addAchievementWithState($title, $description, $state, $position) {
    global $conn;
    $sql = "INSERT INTO achievements (title, description, state, position) VALUES ('$title', '$description', '$state', $position)";
    $conn->query($sql);
}

function getAchievements() {
    global $conn;
    $sql = "SELECT * FROM achievements ORDER BY position ASC";
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
    $sql = "SELECT * FROM achievements ORDER BY position ASC";
    $result = $conn->query($sql);

    $achievements = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $achievements[] = $row;
        }
    }

    $file = fopen('achievements.txt', 'w');
    foreach ($achievements as $achievement) {
        fwrite($file, $achievement['title'] . '|' . $achievement['description'] . '|' . $achievement['state'] . '|' . $achievement['position'] . "\n");
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

        $maxPosition = getMaxPosition();
        foreach ($lines as $line) {
            if (trim($line) != '') {
                list($title, $description, $state, $position) = explode('|', $line);
                $position = ++$maxPosition; // Ensure new positions are assigned
                addAchievementWithState(trim($title), trim($description), trim($state), $position);
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

function updateAchievementsOrder($achievements) {
    global $conn;
    foreach ($achievements as $position => $id) {
        $sql = "UPDATE achievements SET position=$position WHERE id=$id";
        $conn->query($sql);
    }
}

function getMaxPosition() {
    global $conn;
    $sql = "SELECT MAX(position) AS max_position FROM achievements";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['max_position'];
}

// Handle state update request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['state'])) {
    $id = $_POST['id'];
    $state = $_POST['state'];
    updateAchievementState($id, $state);
    echo "Achievement state updated.";
    exit;
}

// Handle order update request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order'])) {
    $order = json_decode($_POST['order'], true);
    updateAchievementsOrder($order);
    echo "Order updated.";
    exit;
}
?>
