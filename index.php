
<?php

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/models/UserModel.php";
require_once __DIR__ . "/controller/UserController.php";
require_once __DIR__ . "/routes/UserRoutes.php";

handleUserRoutes($conn);
 