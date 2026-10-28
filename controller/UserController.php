



<?php

require_once __DIR__ . "/../models/UserModel.php";

function getUsers($conn) {
    header("Content-Type: application/json");

    $users = getAllUsers($conn);

    if ($users === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch users"
        ]);
        return;
    }

    echo json_encode([
        "success" => true,
        "data" => $users
    ]);
}



function getUser($conn, $id) {
    header("Content-Type: application/json");

    $users = getUserById($conn, $id);

    // Check if query failed
    if ($users === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch user"
        ]);
        return;
    }

    // Check if user does not exist
    if (empty($users)) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
        return;
    }

    // Get department ID from user
    $department_id = $users[0]['department_id'];

    // Fetch department data
    $department = getDepartByIDFromModel($department_id);

    // Send user and department together
    echo json_encode([
        "success" => true,
        "data" => [
            "user" => $users[0],
            "department" => $department
        ]
    ]);
}

function getUsersWithDepartmentsController($conn) {
    header("Content-Type: application/json");

    $users = getUsersWithDepartments($conn);

    
    if ($users === false) {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch users with departments"
        ]);
        return;
    }

    echo json_encode([
        "success" => true,
        "data" => $users
    ]);
}
 
function getUsersWithAllDetailsController($conn) {
    header("Content-Type: application/json");

    $data = getUsersWithAllDetails($conn);



    if ($data === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch data"
        ]);
        return;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
}
 
function updateUserController($conn, $id) {
    header("Content-Type: application/json");

    // Accept both JSON requests and the multipart form data used for image uploads.
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";
    $input = stripos($contentType, "application/json") === 0
        ? json_decode(file_get_contents("php://input"), true)
        : $_POST;

    if (
        !is_array($input) ||
        !isset(
            $input["username"],
            $input["firstname"],
            $input["email"],
            $input["name"],
            $input["department_id"]
        )
    ) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "All user fields and department ID are required"
        ]);
        return;
    }

    $username = trim($input["username"]);
    $firstname = trim($input["firstname"]);
    $email = trim($input["email"]);
    $name = trim($input["name"]);
    $departmentId = filter_var(
        $input["department_id"],
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if (
        $username === "" ||
        $firstname === "" ||
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $departmentId === false
    ) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Enter valid user details, email, and department ID"
        ]);
        return;
    }

    // Check whether user exists
    $existingUser = getUserById($conn, $id);

    if ($existingUser === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to check user"
        ]);
        return;
    }

    if (empty($existingUser)) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
        return;
    }

    // Update user in database
    $success = updateUser(
        $conn,
        $id,
        $username,
        $firstname,
        $email,
        $name,
        $departmentId
    );

    if (!$success) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to update user"
        ]);
        return;
    }

    $imageResult = uploadUserImage($id);

    if (!$imageResult["success"]) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => $imageResult["message"]
        ]);
        return;
    }

    if ($imageResult["uploaded"]) {
        $saved = saveUserMedia(
            $conn,
            $id,
            $imageResult["media_path"],
            $imageResult["media_type"]
        );

        if (!$saved) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Image uploaded but media record could not be saved"
            ]);
            return;
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "User updated successfully"
    ]);
} 
function deleteUserController($conn, $id) {
    header("Content-Type: application/json");

    $existingUser = getUserById($conn, $id);

    if ($existingUser === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to check user"
        ]);
        return;
    }

    if (empty($existingUser)) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
        return;
    }

    $success = deleteUser($conn, $id);

    if (!$success) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to delete user"
        ]);
        return;
    }

    echo json_encode([
        "success" => true,
        "message" => "User deleted successfully"
    ]);
}  




function insertUserController($conn)
{
    header("Content-Type: application/json");

    // FormData se aane wala data
    $input = $_POST;

    if (
        !isset(
            $input["username"],
            $input["firstname"],
            $input["email"],
            $input["name"],
            $input["department_name"]
        )
    ) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "All user fields and department name are required"
        ]);
        return;
    }

    $username = trim($input["username"]);
    $firstname = trim($input["firstname"]);
    $email = trim($input["email"]);
    $name = trim($input["name"]);
    $departmentName = trim($input["department_name"]);

    if (
        $username === "" ||
        $firstname === "" ||
        $name === "" ||
        $departmentName === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Enter valid user details, email, and department name"
        ]);
        return;
    }

    $departmentId = getOrCreateDepartment($conn, $departmentName);

    if ($departmentId === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Department creation or lookup failed"
        ]);
        return;
    }

    $id = insertUser(
        $conn,
        $username,
        $firstname,
        $email,
        $name,
        $departmentId
    );

    if ($id === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "User insert failed"
        ]);
        return;
    }

    // User insert hone ke baad image upload karo
    $imageResult = uploadUserImage($id);

    if (!$imageResult["success"]) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => $imageResult["message"],
            "id" => $id
        ]);
        return;
    }

    // Agar image select ki gayi hai to media table mein save karo
    if ($imageResult["uploaded"]) {
        $saved = saveUserMedia(
            $conn,
            $id,
            $imageResult["media_path"],
            $imageResult["media_type"]
        );

        if (!$saved) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Image uploaded but media record could not be saved",
                "id" => $id
            ]);
            return;
        }
    }

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "User inserted successfully",
        "id" => $id,
        "department_id" => $departmentId
    ]);
}
 
 
function uploadUserImage($userId)
{
    if (
        !isset($_FILES["profile_image"]) ||
        $_FILES["profile_image"]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return ["success" => true, "uploaded" => false];
    }

    if ($_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
        return ["success" => false, "message" => "Image upload failed."];
    }

    $file = $_FILES["profile_image"];

    // Maximum image size: 5 MB
    if ($file["size"] > 5 * 1024 * 1024) {
        return ["success" => false, "message" => "Image must be 5 MB or smaller."];
    }

    // Verify actual image content, not just the filename
    $imageInfo = getimagesize($file["tmp_name"]);

    if ($imageInfo === false) {
        return ["success" => false, "message" => "Please select a valid image."];
    }

    $allowedTypes = [
        IMAGETYPE_JPEG => ["ext" => "jpg", "mime" => "image/jpeg"],
        IMAGETYPE_PNG  => ["ext" => "png", "mime" => "image/png"],
        IMAGETYPE_GIF  => ["ext" => "gif", "mime" => "image/gif"],
        IMAGETYPE_WEBP => ["ext" => "webp", "mime" => "image/webp"]
    ];

    $imageType = $imageInfo[2];

    if (!isset($allowedTypes[$imageType])) {
        return ["success" => false, "message" => "Only JPG, PNG, GIF and WEBP images are allowed."];
    }

    $extension = $allowedTypes[$imageType]["ext"];
    $mimeType = $allowedTypes[$imageType]["mime"];

    $uploadDir = dirname(__DIR__) . "/uploads/";

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        return ["success" => false, "message" => "Could not create uploads folder."];
    }

    // A predictable filename lets us replace the same user's image.
    $fileName = "user_" . (int)$userId . "." . $extension;
    $destination = $uploadDir . $fileName;
    $relativePath = "uploads/" . $fileName;

    // Store the new file first. Keep the old image if saving fails.
    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        return ["success" => false, "message" => "Could not save image in uploads folder."];
    }

    return [
        "success" => true,
        "uploaded" => true,
        "media_path" => $relativePath,
        "media_type" => $mimeType
    ];
}
    
?>
  