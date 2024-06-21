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
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    // Transfer temporary achievements to the new user
    if (isset($_SESSION['temp_achievements'])) {
        transferAchievementsToUser($userId, $_SESSION['temp_achievements']);
        unset($_SESSION['temp_achievements']);
    }

    return $userId;
}

function loginUser($username, $password) {
    global $conn;
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Transfer temporary achievements to the logged-in user
        if (isset($_SESSION['temp_achievements'])) {
            transferAchievementsToUser($user['id'], $_SESSION['temp_achievements']);
            unset($_SESSION['temp_achievements']);
        }
        return $user;
    } else {
        return false;
    }
}

function transferAchievementsToUser($userId, $tempAchievements) {
    global $conn;
    foreach ($tempAchievements as $achievement) {
        $stmt = $conn->prepare("UPDATE achievements SET user_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $userId, $achievement['id']);
        $stmt->execute();
        $stmt->close();
    }
}

function addAchievement($userId, $title, $description) {
    global $conn;
    $position = getMaxPosition($userId) + 1;
    $stmt = $conn->prepare("INSERT INTO achievements (user_id, title, description, position, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("issi", $userId, $title, $description, $position);
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

function getAchievementsByUser($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY position ASC");
    $stmt->bind_param("i", $userId);
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
    $sql = "SELECT * FROM achievements WHERE user_id = ? ORDER BY position ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $achievements = [];
    while ($row = $result->fetch_assoc()) {
        $achievements[] = $row;
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


function importAchievements($user_id, $file) {
    global $conn;

    if ($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
        $content = file_get_contents($file['tmp_name']);
        $lines = explode("\n", $content);

        // Step 1: Import the new achievements
        $newPosition = getMaxPosition($user_id) + 1;
        foreach ($lines as $line) {
            if (trim($line) != '') {
                list($title, $description, $state) = explode('|', $line); // Removed $position from list
                addAchievementWithState($user_id, trim($title), trim($description), trim($state), $newPosition++);
            }
        }

        // Step 2: Get the current max position after imports
        $currentMaxPosition = $newPosition - 1;

        // Step 3: Reassign positions to existing achievements starting after the max position
        $sql = "SELECT * FROM achievements WHERE user_id = ? AND position >= $newPosition ORDER BY position ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $updatePosition = ++$currentMaxPosition;
            $updateSql = "UPDATE achievements SET position=$updatePosition WHERE id=" . $row['id'];
            $conn->query($updateSql);
        }
    }
}

function deleteAllAchievements($user_id) {
    global $conn;
    $sql = "DELETE FROM achievements WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
}

function deleteAchievement($id) {
    global $conn;
    $sql = "DELETE FROM achievements WHERE id=$id";
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

function getMaxPosition($userId) {
    global $conn;
    $sql = "SELECT MAX(position) AS max_position FROM achievements WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
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
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function updateUser($user_id, $username, $email, $profile_picture) {
    global $conn;
    $sql = "UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $username, $email, $profile_picture, $user_id);
    $stmt->execute();
    $stmt->close();
}
