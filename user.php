
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User CRUD</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        input, button {
            padding: 10px;
            margin: 5px;
        }

        button {
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #ddd;
        }

        #message {
            margin-top: 15px;
        }

        .edit-input {
            width: 90%;
            box-sizing: border-box;
            padding: 7px;
        }
    </style>
</head>

<body>

    <h2>Insert New User</h2>

    <div>
        <input type="text" id="newUsername" placeholder="Username">
        <input type="text" id="newFirstname" placeholder="First Name">
        <input type="email" id="newEmail" placeholder="Email">
        <input type="text" id="newName" placeholder="Name">

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
                <th>Username</th>
                <th>First Name</th>
                <th>Email</th>
                <th>Name</th>
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

        // INSERT USER: ID database khud generate karega.
        async function insertData() {
            const username = document.getElementById("newUsername").value.trim();
            const firstname = document.getElementById("newFirstname").value.trim();
            const email = document.getElementById("newEmail").value.trim();
            const name = document.getElementById("newName").value.trim();
            const message = document.getElementById("message");

            if (!username || !firstname || !email || !name) {
                message.textContent = "Please fill in all fields.";
                return;
            }

            try {
                const response = await fetch("/project1/users", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        username,
                        firstname,
                        email,
                        name
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    message.textContent = result.message || "Insert failed.";
                    return;
                }

                document.getElementById("newUsername").value = "";
                document.getElementById("newFirstname").value = "";
                document.getElementById("newEmail").value = "";
                document.getElementById("newName").value = "";

                document.getElementById("userId").value = result.id;

                message.textContent =
                    "User inserted successfully. New ID: " + result.id;

                // List ko foran refresh karo, page reload nahi hoga.
                await loadAllUsers();

                // Naye user ki editable details bhi dikhao.
                await fetchData();

            } catch (error) {
                console.error(error);
                message.textContent =
                    "Insert failed. PHP controller aur route check karo.";
            }
        }

        // LOAD ALL USERS
        async function loadAllUsers() {
            const tableBody = document.getElementById("tableBody");
            const message = document.getElementById("message");

            try {
                const response = await fetch("/project1/users");
                const result = await response.json();

                if (!response.ok || !result.success) {
                    message.textContent =
                        result.message || "Could not load users.";
                    return;
                }

                const users = Array.isArray(result.data)
                    ? result.data
                    : [];

                tableBody.innerHTML = "";

                users.forEach(user => {
                    const row = document.createElement("tr");

                    ["id", "username", "firstname", "email", "name"]
                        .forEach(key => {
                            const cell = document.createElement("td");
                            cell.textContent = user[key] ?? "";
                            row.appendChild(cell);
                        });

                    tableBody.appendChild(row);
                });

                if (users.length === 0) {
                    tableBody.innerHTML =
                        '<tr><td colspan="5">No users found.</td></tr>';
                }

            } catch (error) {
                console.error(error);
                message.textContent =
                    "User list load nahi hui. GET /users route check karo.";
            }
        }

        // FETCH ONE USER
        async function fetchData() {
            const id = document.getElementById("userId").value.trim();
            const detailsBody = document.getElementById("detailsBody");
            const message = document.getElementById("message");

            detailsBody.innerHTML = "";
            currentUserData = null;

            if (!id || Number(id) < 1) {
                message.textContent = "Please enter a valid User ID.";
                return;
            }

            try {
                const response = await fetch(
                    "/project1/users/" + encodeURIComponent(id)
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    message.textContent = result.message || "User not found.";
                    return;
                }

                const data = result.data;
                const user = data.user || data;

                currentUserData = user;

                function addRows(type, object, editable = false) {
                    if (!object || typeof object !== "object") return;

                    Object.entries(object).forEach(([key, value]) => {
                        const row = document.createElement("tr");
                        const typeCell = document.createElement("td");
                        const fieldCell = document.createElement("td");
                        const valueCell = document.createElement("td");

                        typeCell.textContent = type;
                        fieldCell.textContent = key;

                        if (editable && key !== "id") {
                            const input = document.createElement("input");
                            input.type = "text";
                            input.className = "edit-input";
                            input.dataset.field = key;
                            input.value = value ?? "";
                            valueCell.appendChild(input);
                        } else {
                            valueCell.textContent = value ?? "";
                        }

                        row.append(typeCell, fieldCell, valueCell);
                        detailsBody.appendChild(row);
                    });
                }

                addRows("User", user, true);

                if (data.department) {
                    if (Array.isArray(data.department)) {
                        data.department.forEach(department =>
                            addRows("Department", department)
                        );
                    } else {
                        addRows("Department", data.department);
                    }
                }

                message.textContent = "User data loaded.";

            } catch (error) {
                console.error(error);
                message.textContent =
                    "Fetch failed. GET /users/:id route check karo.";
            }
        }

        // UPDATE USER
        async function updateData() {
            const message = document.getElementById("message");

            if (!currentUserData) {
                message.textContent = "Pehle Fetch Data karo.";
                return;
            }

            const updatedUser = { ...currentUserData };

            document.querySelectorAll(".edit-input").forEach(input => {
                updatedUser[input.dataset.field] = input.value.trim();
            });

            try {
                const response = await fetch(
                    "/project1/users/" +
                    encodeURIComponent(currentUserData.id),
                    {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            username: updatedUser.username,
                            firstname: updatedUser.firstname,
                            email: updatedUser.email,
                            name: updatedUser.name
                        })
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    message.textContent = result.message || "Update failed.";
                    return;
                }

                message.textContent = result.message || "User updated.";
                await loadAllUsers();
                await fetchData();

            } catch (error) {
                console.error(error);
                message.textContent = "Update failed. Server check karo.";
            }
        }

        // DELETE USER
        async function deleteData() {
            const message = document.getElementById("message");

            if (!currentUserData) {
                message.textContent = "Pehle Fetch Data karo.";
                return;
            }

            const id = currentUserData.id;

            if (!confirm("Kya tum is user ko delete karna chahti ho?")) {
                return;
            }

            try {
                const response = await fetch(
                    "/project1/users/" + encodeURIComponent(id),
                    { method: "DELETE" }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    message.textContent = result.message || "Delete failed.";
                    return;
                }

                currentUserData = null;
                document.getElementById("userId").value = "";
                document.getElementById("detailsBody").innerHTML = "";

                message.textContent = result.message || "User deleted.";
                await loadAllUsers();

            } catch (error) {
                console.error(error);
                message.textContent = "Delete failed. Server check karo.";
            }
        }

        // CLEAR SELECTED USER
        function clearData() {
            document.getElementById("userId").value = "";
            document.getElementById("detailsBody").innerHTML = "";
            document.getElementById("message").textContent = "";
            currentUserData = null;
        }

        // Page khulte hi all users show karo.
        document.addEventListener("DOMContentLoaded", loadAllUsers);
    </script>

</body>
</html>
