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

function registerUser($username, $password, $email, $profile_picture) {
    global $conn;
    $sql = "INSERT INTO users (username, password, email, profile_picture) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $username, $password, $email, $profile_picture);
    return $stmt->execute();
}

function loginUser($username, $password) {
    global $conn;
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        return $user;
    } else {
        return false;
    }
}

function addAchievement($user_id, $title, $description) {
    global $conn;
    $position = getMaxPosition($user_id) + 1;
    $stmt = $conn->prepare("INSERT INTO achievements (user_id, title, description, position, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("issi", $user_id, $title, $description, $position);
    $stmt->execute();
    $stmt->close();
}

function addAchievementWithState($user_id, $title, $description, $state, $position) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO achievements (user_id, title, description, state, position, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isssi", $user_id, $title, $description, $state, $position);
    $stmt->execute();
    $stmt->close();
}

function getAchievementsByUser($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY position ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $achievements = [];
    while ($row = $result->fetch_assoc()) {
        $achievements[] = $row;
    }
    $stmt->close();
    return $achievements;
}

function exportAchievements($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY position ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $achievements = [];
    while ($row = $result->fetch_assoc()) {
        $achievements[] = $row;
    }
    $stmt->close();

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

function importAchievements($user_id, $file) {
    global $conn;

    if ($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
        $content = file_get_contents($file['tmp_name']);
        $lines = explode("\n", $content);

        $position = getMaxPosition($user_id) + 1;
        foreach ($lines as $line) {
            if (trim($line) != '') {
                list($title, $description, $state, $position) = explode('|', $line);
                addAchievementWithState($user_id, trim($title), trim($description), trim($state), $position++);
            }
        }
    }
}

function deleteAllAchievements($user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM achievements WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

function deleteAchievement($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM achievements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function updateAchievementState($id, $state) {
    global $conn;
    $stmt = $conn->prepare("UPDATE achievements SET state = ? WHERE id = ?");
    $stmt->bind_param("si", $state, $id);
    $stmt->execute();
    $stmt->close();
}

function updateAchievementsOrder($achievements) {
    global $conn;
    foreach ($achievements as $position => $id) {
        $stmt = $conn->prepare("UPDATE achievements SET position = ? WHERE id = ?");
        $stmt->bind_param("ii", $position, $id);
        $stmt->execute();
    }
}

function getMaxPosition($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT MAX(position) AS max_position FROM achievements WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
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

// Handle delete achievement request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    deleteAchievement($id);
    echo "Achievement deleted.";
    exit;
}

// Handle edit achievement request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit']) && isset($_POST['id']) && isset($_POST['title']) && isset($_POST['description'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $stmt = $conn->prepare("UPDATE achievements SET title = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $description, $id);
    $stmt->execute();
    $stmt->close();
    echo "Achievement updated.";
    exit;
}

function getUserById($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function updateUser($user_id, $username, $email, $profile_picture, $new_password = null) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $profile_picture, $user_id);
    $result = $stmt->execute();
    $stmt->close();

    if ($new_password) {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $user_id);
        $result = $result && $stmt->execute();
        $stmt->close();
    }

    return $result;
}