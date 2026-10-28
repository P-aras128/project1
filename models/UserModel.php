
<?php

require_once __DIR__ . "/../database.php";

// GET ALL USERS WITH DEPARTMENT AND PICTURE
function getAllUsers($conn)
{
    $sql = "SELECT
                users.id,
                users.username,
                users.firstname,
                users.email,
                users.name,
                users.department_id,
                departments.department_name,
                media.media_path,
                media.media_type
            FROM users
            LEFT JOIN departments
                ON users.department_id = departments.id
            LEFT JOIN media
                ON users.id = media.user_id";

    $result = $conn->query($sql);

    if (!$result) {
        error_log("Get all users failed: " . $conn->error);
        return false;
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}


// GET ONE USER WITH DEPARTMENT AND PICTURE
function getUserById($conn, $id)
{
    $sql = "SELECT
                users.*,
                departments.department_name,
                media.media_path,
                media.media_type
            FROM users
            LEFT JOIN departments
                ON users.department_id = departments.id
            LEFT JOIN media
                ON users.id = media.user_id
            WHERE users.id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Get user prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        error_log("Get user execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $users;
}


// GET USERS WITH DEPARTMENTS
function getUsersWithDepartments($conn)
{
    $sql = "SELECT
                users.id,
                users.username,
                departments.department_name
            FROM users
            INNER JOIN departments
                ON users.department_id = departments.id";

    $result = $conn->query($sql);

    if (!$result) {
        error_log("Get users with departments failed: " . $conn->error);
        return false;
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}


// GET USERS WITH ALL DETAILS
function getUsersWithAllDetails($conn)
{
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
        error_log("Get all details failed: " . $conn->error);
        return false;
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}


// GET DEPARTMENT BY ID
function getDepartByIDFromModel($department_id)
{
    global $conn;

    $sql = "SELECT * FROM departments WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $department_id);

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $departments = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $departments;
}


// UPDATE USER INCLUDING DEPARTMENT ID
function updateUser(
    $conn,
    $id,
    $username,
    $firstname,
    $email,
    $name,
    $departmentId
) {
    $sql = "UPDATE users
            SET username = ?,
                firstname = ?,
                email = ?,
                name = ?,
                department_id = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Update user prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param(
        "ssssii",
        $username,
        $firstname,
        $email,
        $name,
        $departmentId,
        $id
    );

    $success = $stmt->execute();

    if (!$success) {
        error_log("Update user failed: " . $stmt->error);
    }

    $stmt->close();

    return $success;
}


// DELETE USER
function deleteUser($conn, $id)
{
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Delete user prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("i", $id);
    $success = $stmt->execute();

    if (!$success) {
        error_log("Delete user failed: " . $stmt->error);
    }

    $stmt->close();

    return $success;
}


// INSERT USER
function insertUser(
    $conn,
    $username,
    $firstname,
    $email,
    $name,
    $departmentId
) {
    $sql = "INSERT INTO users
                (username, firstname, email, name, department_id)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Insert prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param(
        "ssssi",
        $username,
        $firstname,
        $email,
        $name,
        $departmentId
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


// FIND DEPARTMENT OR CREATE IT
function getOrCreateDepartment($conn, $departmentName)
{
    $sql = "SELECT id FROM departments WHERE department_name = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Department lookup failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $departmentName);

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $departmentId = (int) $row["id"];
        $stmt->close();

        return $departmentId;
    }

    $stmt->close();

    $sql = "INSERT INTO departments (department_name) VALUES (?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $departmentName);

    if (!$stmt->execute()) {
        error_log("Department insert failed: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $departmentId = $conn->insert_id;
    $stmt->close();

    return $departmentId;
}


// GET USER MEDIA
function getMediaByUserId($conn, $userId)
{
    $sql = "SELECT * FROM media WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $media = $result->fetch_assoc();

    $stmt->close();

    return $media ?: null;
}


// INSERT OR UPDATE USER MEDIA
function saveUserMedia($conn, $userId, $mediaPath, $mediaType)
{
    $sql = "SELECT id FROM media WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $existingMedia = $result->fetch_assoc();

    $stmt->close();

    if ($existingMedia) {
        $sql = "UPDATE media
                SET media_path = ?, media_type = ?
                WHERE user_id = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssi",
            $mediaPath,
            $mediaType,
            $userId
        );
    } else {
        $sql = "INSERT INTO media
                    (user_id, media_path, media_type)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "iss",
            $userId,
            $mediaPath,
            $mediaType
        );
    }

    $success = $stmt->execute();

    if (!$success) {
        error_log("Save user media failed: " . $stmt->error);
    }

    $stmt->close();

    return $success;
}


// DELETE MEDIA DATABASE RECORD
function deleteUserMediaRecord($conn, $userId)
{
    $sql = "DELETE FROM media WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $userId);
    $success = $stmt->execute();

    $stmt->close();

    return $success;
}

?>
 