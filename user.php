
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User CRUD</title>

    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        input, button { padding: 10px; margin: 5px; }
        button { cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            overflow-wrap: anywhere;
        }
        th { background: #ddd; }
        #message { margin-top: 15px; }
        .edit-input { width: 90%; box-sizing: border-box; padding: 7px; }
        .user-picture {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 6px;
        }
        .detail-picture {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ccc;
            display: block;
            margin-bottom: 8px;
        }
        .form-section { margin-bottom: 20px; }

        @media (max-width: 700px) {
            body { margin: 12px; }
            table { font-size: 12px; }
            th, td { padding: 6px; }
        }
    </style>
</head>

<body>
    <h2>Insert New User</h2>

    <div class="form-section">
        <input type="text" id="newUsername" placeholder="Username">
        <input type="text" id="newFirstname" placeholder="First Name">
        <input type="email" id="newEmail" placeholder="Email">
        <input type="text" id="newName" placeholder="Name">
        <input type="text" id="newDepartment" placeholder="Department Name">

        <label for="profileImage">Profile Picture:</label>
        <input type="file" id="profileImage"
               accept="image/jpeg,image/png,image/gif,image/webp">

        <div id="insertPicturePreview"></div>
        <button type="button" onclick="insertData()">Insert User</button>
    </div>

    <hr>

    <h2>Fetch / Update / Delete User</h2>

    <input type="number" id="userId" placeholder="Enter User ID" min="1">

    <button type="button" onclick="fetchData()">Fetch Data</button>
    <button type="button" onclick="clearData()">Clear Data</button>
    <button type="button" onclick="updateData()">Update Data</button>
    <button type="button" onclick="deleteData()">Delete User</button>

    <p id="message"></p>

    <h2>All Users</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Picture</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Email</th>
                <th>Name</th>
                <th>Department ID</th>
                <th>Department Name</th>
            </tr>
        </thead>
        <tbody id="tableBody"></tbody>
    </table>

    <h2>Selected User Details</h2>

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Field</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody id="detailsBody"></tbody>
    </table>

    <script>
        let currentUserData = null;
        const API_URL = "/project1/users";

        function showMessage(message) {
            document.getElementById("message").textContent = message;
        }

        function getPictureUrl(path) {
            if (!path) return "";

            const cleanPath = String(path).replaceAll("\\", "/");

            if (
                cleanPath.startsWith("/") ||
                cleanPath.includes("..") ||
                !cleanPath.startsWith("uploads/")
            ) {
                return "";
            }

            return "/project1/" + cleanPath;
        }

        function makeImage(path, className = "user-picture") {
            const url = getPictureUrl(path);
            if (!url) return null;

            const image = document.createElement("img");
            image.src = url;
            image.alt = "User profile picture";
            image.className = className;

            image.onerror = () => {
                image.replaceWith(
                    document.createTextNode("Picture could not be loaded.")
                );
            };

            return image;
        }

        async function readResponse(response) {
            const text = await response.text();

            try {
                return JSON.parse(text);
            } catch {
                console.error("Server response:", text);
                throw new Error("PHP response: " + text.substring(0, 500));
            }
        }

        // INSERT USER
        async function insertData() {
            const username =
                document.getElementById("newUsername").value.trim();
            const firstname =
                document.getElementById("newFirstname").value.trim();
            const email =
                document.getElementById("newEmail").value.trim();
            const name =
                document.getElementById("newName").value.trim();
            const departmentName =
                document.getElementById("newDepartment").value.trim();
            const imageInput = document.getElementById("profileImage");

            if (!username || !firstname || !email || !name || !departmentName) {
                showMessage("Please fill in all user fields and department name.");
                return;
            }

            const formData = new FormData();
            formData.append("username", username);
            formData.append("firstname", firstname);
            formData.append("email", email);
            formData.append("name", name);
            formData.append("department_name", departmentName);

            if (imageInput.files.length > 0) {
                formData.append("profile_image", imageInput.files[0]);
            }

            try {
                const response = await fetch(API_URL, {
                    method: "POST",
                    body: formData
                });

                const result = await readResponse(response);

                if (!response.ok || !result.success) {
                    showMessage(result.message || "Insert failed.");
                    return;
                }

                [
                    "newUsername",
                    "newFirstname",
                    "newEmail",
                    "newName",
                    "newDepartment"
                ].forEach(id => {
                    document.getElementById(id).value = "";
                });

                imageInput.value = "";
                document.getElementById("insertPicturePreview").innerHTML = "";
                document.getElementById("userId").value = result.id;

                showMessage("User inserted successfully. New ID: " + result.id);

                await loadAllUsers();
                await fetchData();

            } catch (error) {
                console.error(error);
                showMessage(error.message || "Insert failed.");
            }
        }

        // PREVIEW INSERT PICTURE
        document.getElementById("profileImage").addEventListener(
            "change",
            function () {
                const preview =
                    document.getElementById("insertPicturePreview");
                preview.innerHTML = "";

                if (!this.files.length) return;

                const image = document.createElement("img");
                image.src = URL.createObjectURL(this.files[0]);
                image.alt = "Selected picture preview";
                image.className = "user-picture";

                preview.appendChild(image);
            }
        );

        // LOAD ALL USERS
        async function loadAllUsers() {
            const tableBody = document.getElementById("tableBody");

            try {
                const response = await fetch(API_URL);
                const result = await readResponse(response);

                if (!response.ok || !result.success) {
                    showMessage(result.message || "Could not load users.");
                    return;
                }

                const users = Array.isArray(result.data) ? result.data : [];
                tableBody.innerHTML = "";

                users.forEach(user => {
                    const row = document.createElement("tr");

                    const idCell = document.createElement("td");
                    idCell.textContent = user.id ?? "";
                    row.appendChild(idCell);

                    const pictureCell = document.createElement("td");
                    const image = makeImage(user.media_path);

                    pictureCell.appendChild(
                        image || document.createTextNode("No picture")
                    );
                    row.appendChild(pictureCell);

                    [
                        "username",
                        "firstname",
                        "email",
                        "name",
                        "department_id",
                        "department_name"
                    ].forEach(key => {
                        const cell = document.createElement("td");
                        cell.textContent = user[key] ?? "";
                        row.appendChild(cell);
                    });

                    tableBody.appendChild(row);
                });

                if (users.length === 0) {
                    tableBody.innerHTML =
                        '<tr><td colspan="8">No users found.</td></tr>';
                }

            } catch (error) {
                console.error(error);
                showMessage(error.message || "Could not load users.");
            }
        }

        // ADD A ROW TO SELECTED USER DETAILS
        function addDetailRow(type, field, value, editable = false) {
            const row = document.createElement("tr");
            const typeCell = document.createElement("td");
            const fieldCell = document.createElement("td");
            const valueCell = document.createElement("td");

            typeCell.textContent = type;
            fieldCell.textContent = field;

            if (editable) {
                const input = document.createElement("input");

                input.type =
                    field === "email" ? "email" :
                    field === "department_id" ? "number" : "text";

                input.className = "edit-input";
                input.dataset.field = field;
                input.value = value ?? "";

                if (field === "department_id") input.min = "1";

                valueCell.appendChild(input);
            } else {
                valueCell.textContent = value ?? "";
            }

            row.append(typeCell, fieldCell, valueCell);
            document.getElementById("detailsBody").appendChild(row);
        }
        async function fetchData() {
            const id = document.getElementById("userId").value.trim();
            const detailsBody = document.getElementById("detailsBody");

            detailsBody.innerHTML = "";
            currentUserData = null;

            if (!id || Number(id) < 1) {
                showMessage("Please enter a valid User ID.");
                return;
            }

            try {
                const response = await fetch(
                    API_URL + "/" + encodeURIComponent(id)
                );
                const result = await readResponse(response);

                if (!response.ok || !result.success) {
                    showMessage(result.message || "User not found.");
                    return;
                }

                const data = result.data;
                const user = data.user || data;
                currentUserData = { ...user };

                const picturePath =
                    user.media_path || data.media?.media_path || "";

                currentUserData.media_path = picturePath;

                // Profile picture row INSIDE Selected User Details table
                const pictureRow = document.createElement("tr");
                const typeCell = document.createElement("td");
                const fieldCell = document.createElement("td");
                const valueCell = document.createElement("td");

                typeCell.textContent = "User";
                fieldCell.textContent = "Profile Picture";

                const preview = document.createElement("div");

                function showCurrentPicture(path) {
                    preview.innerHTML = "";
                    const image = makeImage(path, "detail-picture");

                    preview.appendChild(
                        image || document.createTextNode("No picture available.")
                    );
                }

                showCurrentPicture(picturePath);

                const imageInput = document.createElement("input");
                imageInput.type = "file";
                imageInput.id = "tableProfileImage";
                imageInput.accept = "image/jpeg,image/png,image/gif,image/webp";

                imageInput.addEventListener("change", function () {
                    if (!this.files.length) {
                        showCurrentPicture(currentUserData.media_path);
                        return;
                    }

                    preview.innerHTML = "";

                    const image = document.createElement("img");
                    image.src = URL.createObjectURL(this.files[0]);
                    image.alt = "New picture preview";
                    image.className = "detail-picture";

                    preview.appendChild(image);
                });

                valueCell.appendChild(preview);
                valueCell.appendChild(imageInput);
                pictureRow.append(typeCell, fieldCell, valueCell);
                detailsBody.appendChild(pictureRow);

                const editableFields = [
                    "username",
                    "firstname",
                    "email",
                    "name",
                    "department_id"
                ];

                Object.entries(user).forEach(([key, value]) => {
                    if (key === "media_path" || key === "media_type") return;

                    addDetailRow(
                        "User",
                        key,
                        value,
                        editableFields.includes(key)
                    );
                });

                if (data.department) {
                    const departments = Array.isArray(data.department)
                        ? data.department
                        : [data.department];

                    departments.forEach(department => {
                        Object.entries(department).forEach(([key, value]) => {
                            addDetailRow("Department", key, value);
                        });
                    });
                }

                showMessage("User data loaded.");

            } catch (error) {
                console.error(error);
                showMessage(error.message || "Could not fetch user.");
            }
        }

        // UPDATE USER AND OPTIONAL PROFILE PICTURE
        async function updateData() {
            if (!currentUserData) {
                showMessage("Pehle Fetch Data karo.");
                return;
            }

            const updatedUser = { ...currentUserData };

            document.querySelectorAll(".edit-input").forEach(input => {
                updatedUser[input.dataset.field] = input.value.trim();
            });

            if (
                !updatedUser.username ||
                !updatedUser.firstname ||
                !updatedUser.email ||
                !updatedUser.name
            ) {
                showMessage("Please fill in all user fields.");
                return;
            }

            if (
                !updatedUser.department_id ||
                Number(updatedUser.department_id) < 1
            ) {
                showMessage("Please enter a valid Department ID.");
                return;
            }

            const formData = new FormData();
            formData.append("username", updatedUser.username);
            formData.append("firstname", updatedUser.firstname);
            formData.append("email", updatedUser.email);
            formData.append("name", updatedUser.name);
            formData.append("department_id", updatedUser.department_id);

            const imageInput = document.getElementById("tableProfileImage");

            if (imageInput && imageInput.files.length > 0) {
                formData.append("profile_image", imageInput.files[0]);
            }

            try {
                const response = await fetch(
                    API_URL + "/" + encodeURIComponent(currentUserData.id),
                    {
                        method: "POST",
                        body: formData
                    }
                );

                const result = await readResponse(response);

                if (!response.ok || !result.success) {
                    showMessage(result.message || "Update failed.");
                    return;
                }

                showMessage(result.message || "User updated successfully.");

                await loadAllUsers();
                await fetchData();

            } catch (error) {
                console.error(error);
                showMessage(error.message || "Update failed.");
            }
        }

        // DELETE USER
        async function deleteData() {
            if (!currentUserData) {
                showMessage("Pehle Fetch Data karo.");
                return;
            }

            const id = currentUserData.id;

            if (!confirm("Kya tum is user ko delete karna chahti ho?")) {
                return;
            }

            try {
                const response = await fetch(
                    API_URL + "/" + encodeURIComponent(id),
                    { method: "DELETE" }
                );

                const result = await readResponse(response);

                if (!response.ok || !result.success) {
                    showMessage(result.message || "Delete failed.");
                    return;
                }

                clearData();
                showMessage(result.message || "User deleted successfully.");

                await loadAllUsers();

            } catch (error) {
                console.error(error);
                showMessage(error.message || "Delete failed.");
            }
        }

        // CLEAR SCREEN ONLY; DATABASE DATA REMAINS UNCHANGED
        function clearData() {
            document.getElementById("userId").value = "";
            document.getElementById("detailsBody").innerHTML = "";
            document.getElementById("message").textContent = "";
            currentUserData = null;
        }

        document.addEventListener("DOMContentLoaded", loadAllUsers);
    </script>
</body>  
</html>
