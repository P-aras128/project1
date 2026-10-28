
<?php

require_once __DIR__ . "/../database.php";

function getAllUsers($conn) {
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    if (!$result) {
        return false;
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUserById($conn, $id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $users;
}

function getUsersWithDepartments($conn) {
    $sql = "SELECT users.id, users.username,
                   departments.department_name
            FROM users
            INNER JOIN departments
                ON users.department_id = departments.id";

    $result = $conn->query($sql);

    if (!$result) {
        return false;
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUsersWithAllDetails($conn) {
    $sql = "SELECT
                users.username,
                departments.department_name,
                courses.course_name,
                teachers.teacher_name
            FROM users
            INNER JOIN departments
                ON users.department_id = departments.id
            INNER JOIN courses
                ON courses.department_id = departments.id
            INNER JOIN teachers
                ON teachers.course_id = courses.id";

    $result = $conn->query($sql);

    if (!$result) {
        return false;
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDepartByIDFromModel($department_id) {
    global $conn;

    $sql = "SELECT * FROM departments WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $department_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $departments = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $departments;
}

// Update existing user
function updateUser($conn, $id, $username, $firstname, $email, $name) {
    $sql = "UPDATE users
            SET username = ?,
                firstname = ?,
                email = ?,
                name = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "ssssi",
        $username,
        $firstname,
        $email,
        $name,
        $id
    );

    $success = $stmt->execute();

    $stmt->close();

    return $success;
}
 
 
// Delete existing user
function deleteUser($conn, $id) {
    $sql = "DELETE FROM users WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id);

    $success = $stmt->execute();

    $stmt->close();

    return $success;
}

 
// Insert new user; database automatically generates ID
function insertUser($conn, $username, $firstname, $email, $name)
{
    $sql = "INSERT INTO users (username, firstname, email, name)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Insert prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param(
        "ssss",
        $username,
        $firstname,
        $email,
        $name
    );

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();
        return $newId;
    }

    error_log("Insert execute failed: " . $stmt->error);
    $stmt->close();

    return false;
}
 
   
 