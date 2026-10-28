
<?php

require_once __DIR__ . "/../controller/UserController.php";

function handleUserRoutes($conn)
{
    $method = $_SERVER["REQUEST_METHOD"];
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    // Remove project folder name
    $path = preg_replace("#^/project1(?=/|$)#", "", $path);

    // GET all users
    if ($method === "GET" && preg_match("#^/users/?$#", $path)) {
        getUsers($conn);
        return;
    }

    // INSERT user
    if ($method === "POST" && preg_match("#^/users/?$#", $path)) {
        insertUserController($conn);
        return;
    }

    // GET one user
    if ($method === "GET" && preg_match("#^/users/([0-9]+)/?$#", $path, $matches)) {
        getUser($conn, (int) $matches[1]);
        return;
    }

    // UPDATE user
    if (
        in_array($method, ["POST", "PUT"], true) &&
        preg_match("#^/users/([0-9]+)/?$#", $path, $matches)
    ) {
        updateUserController($conn, (int) $matches[1]);
        return;
    }

    // DELETE user
    if ($method === "DELETE" && preg_match("#^/users/([0-9]+)/?$#", $path, $matches)) {
        deleteUserController($conn, (int) $matches[1]);
        return;
    }

    // GET users with departments
    if ($method === "GET" && preg_match("#^/users-with-departments/?$#", $path)) {
        getUsersWithDepartmentsController($conn);
        return;
    }

    // GET users with all details
    if ($method === "GET" && preg_match("#^/all-details/?$#", $path)) {
        getUsersWithAllDetailsController($conn);
        return;
    }

    // Route not found
    http_response_code(404);
    header("Content-Type: application/json");

    echo json_encode([
        "success" => false,
        "message" => "Route not found",
        "requested_method" => $method,
        "requested_path" => $path
    ]);
}
 