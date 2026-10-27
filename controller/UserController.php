



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
 

?>
  