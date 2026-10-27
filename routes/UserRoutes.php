
<?php

require_once __DIR__ . "/../controller/UserController.php";
require_once __DIR__ . "/../models/UserModel.php";

function handleUserRoutes($conn)
{
    // Step 1: Request method aur URL path lena
    $method = $_SERVER["REQUEST_METHOD"];
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    // Step 2: Project folder ka naam remove karna
    $path = preg_replace("#^/project1#", "", $path);

    // Step 3: GET /users
    if ($method === "GET" && preg_match("#^/users/?$#", $path)) {
        getUsers($conn);
        return;
    }

    // Step 4: GET /users/1
    if ($method === "GET" && preg_match("#^/users/([0-9]+)/?$#", $path, $matches)) {
        getUser($conn, (int) $matches[1]);
        return;
    }
    // Step 4.1: PUT /users/1 (Update user)
if ($method === "PUT" && preg_match("#^/users/([0-9]+)/?$#", $path, $matches)) {
    updateUserController($conn, (int) $matches[1]);
    return;
} 

    // Step 5: GET /users-with-departments
    if ($method === "GET" && preg_match("#^/users-with-departments/?$#", $path)) {
        getUsersWithDepartmentsController($conn);
        return;
    }
    
if (
    $method === "GET" &&
    preg_match("#^/all-details/?$#", $path)
) {
    getUsersWithAllDetailsController($conn);
    return;
}


    // Step 6: Route nahi mili
    http_response_code(404);
    header("Content-Type: application/json");

    echo json_encode([
        "success" => false,
        "message" => "Route not found"
    ]);
}
 