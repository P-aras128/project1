
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Data</title>

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
            background-color: #ddd;
        }

        #message {
            margin-top: 15px;
        }

        .edit-input {
            width: 90%;
            box-sizing: border-box;
            padding: 7px;
        }

        .insert-form {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <h2>Insert New User</h2>

    <div class="insert-form">
        <input type="number" id="newId" placeholder="Enter User ID" min="1">
        <input type="text" id="newUsername" placeholder="Username">
        <input type="text" id="newFirstname" placeholder="First Name">
        <input type="email" id="newEmail" placeholder="Email">
        <input type="text" id="newName" placeholder="Name">

        <button type="button" onclick="insertData()">Insert User</button>
    </div>

    <hr>

    <h2>Fetch User Data</h2>

    <input
        type="number"
        id="userId"
        placeholder="Enter User ID"
        min="1"
    >

    <button type="button" onclick="fetchData()">Fetch Data</button>
    <button type="button" onclick="clearData()">Clear Data</button>
    <button type="button" onclick="updateData()">Update Data</button>
    <button type="button" onclick="deleteData()">Delete User</button>

    <p id="message"></p>

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Field</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody id="tableBody"></tbody>
    </table>

    <script>
        let currentUserData = null;
        let currentDepartmentData = null;

        // INSERT USER
        async function insertData() {
            const id = document.getElementById("newId").value.trim();
            const username = document.getElementById("newUsername").value.trim();
            const firstname = document.getElementById("newFirstname").value.trim();
            const email = document.getElementById("newEmail").value.trim();
            const name = document.getElementById("newName").value.trim();
            const message = document.getElementById("message");

            if (!id || Number(id) < 1 || !username || !firstname || !email || !name) {
                message.textContent = "Please fill in all fields with a valid ID";
                return;
            }

            try {
                const response = await fetch("/project1/users", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: Number(id),
                        username: username,
                        firstname: firstname,
                        email: email,
                        name: name
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    message.textContent = result.message || "Insert failed";
                    return;
                }

                message.textContent = result.message;

                document.getElementById("newId").value = "";
                document.getElementById("newUsername").value = "";
                document.getElementById("newFirstname").value = "";
                document.getElementById("newEmail").value = "";
                document.getElementById("newName").value = "";

                document.getElementById("userId").value = id;
                await fetchData();

            } catch (error) {
                console.error(error);
                message.textContent = "Insert failed. Server ya route check karo.";
            }
        }

        // FETCH USER
        async function fetchData() {
            const id = document.getElementById("userId").value.trim();
            const tableBody = document.getElementById("tableBody");
            const message = document.getElementById("message");

            tableBody.innerHTML = "";
            message.textContent = "";
            currentUserData = null;
            currentDepartmentData = null;

            if (!id || Number(id) < 1) {
                message.textContent = "Please enter a valid User ID";
                return;
            }

            try {
                const response = await fetch(
                    `/project1/users/${encodeURIComponent(id)}`
                );

                const responseText = await response.text();
                const jsonStart = responseText.indexOf('{"success"');

                if (jsonStart === -1) {
                    throw new Error("JSON response not found");
                }

                const result = JSON.parse(responseText.substring(jsonStart));

                if (!response.ok || !result.success) {
                    message.textContent = result.message || "User not found";
                    return;
                }

                const data = result.data;

                function addRows(type, object) {
                    if (!object || typeof object !== "object") {
                        return;
                    }

                    Object.entries(object).forEach(([key, value]) => {
                        const row = document.createElement("tr");

                        const typeCell = document.createElement("td");
                        typeCell.textContent = type;

                        const fieldCell = document.createElement("td");
                        fieldCell.textContent = key;

                        const valueCell = document.createElement("td");

                        if (type === "User" && key !== "id") {
                            const input = document.createElement("input");
                            input.type = "text";
                            input.className = "edit-input";
                            input.value = value ?? "";
                            input.dataset.field = key;
                            valueCell.appendChild(input);
                        } else {
                            valueCell.textContent =
                                value === null ? "" :
                                typeof value === "object" ?
                                JSON.stringify(value) : String(value);
                        }

                        row.appendChild(typeCell);
                        row.appendChild(fieldCell);
                        row.appendChild(valueCell);

                        tableBody.appendChild(row);
                    });
                }

                if (data.user) {
                    currentUserData = data.user;
                    currentDepartmentData = data.department;

                    addRows("User", data.user);

                    if (Array.isArray(data.department)) {
                        data.department.forEach(department => {
                            addRows("Department", department);
                        });
                    } else {
                        addRows("Department", data.department);
                    }
                } else {
                    currentUserData = data;
                    addRows("User", data);
                }

                if (tableBody.children.length === 0) {
                    message.textContent = "No data found";
                }

            } catch (error) {
                console.error(error);
                message.textContent =
                    "Data fetch nahi ho saka. Route ya server response check karo.";
            }
        }

        // UPDATE USER
        async function updateData() {
            if (!currentUserData) {
                document.getElementById("message").textContent =
                    "Pehle Fetch Data button dabao";
                return;
            }

            const updatedUser = { ...currentUserData };

            document.querySelectorAll(".edit-input").forEach(input => {
                updatedUser[input.dataset.field] = input.value.trim();
            });

            const message = document.getElementById("message");
            const id = currentUserData.id;

            try {
                const response = await fetch(
                    `/project1/users/${encodeURIComponent(id)}`,
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
                    message.textContent = result.message || "Update failed";
                    return;
                }

                message.textContent = result.message;
                await fetchData();

            } catch (error) {
                console.error(error);
                message.textContent =
                    "Update failed. Server aur route check karo.";
            }
        }

        // DELETE USER
        async function deleteData() {
            if (!currentUserData) {
                document.getElementById("message").textContent =
                    "Pehle Fetch Data button dabao";
                return;
            }

            const id = currentUserData.id;

            if (!confirm("Kya tum waqai is user ko delete karna chahte ho?")) {
                return;
            }

            const message = document.getElementById("message");

            try {
                const response = await fetch(
                    `/project1/users/${encodeURIComponent(id)}`,
                    {
                        method: "DELETE"
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    message.textContent =
                        result.message || "User delete nahi hua";
                    return;
                }

                message.textContent = result.message;
                currentUserData = null;
                currentDepartmentData = null;

                document.getElementById("userId").value = "";
                document.getElementById("tableBody").innerHTML = "";

            } catch (error) {
                console.error(error);
                message.textContent =
                    "Delete failed. Server aur route check karo.";
            }
        }

        // CLEAR DATA
        function clearData() {
            document.getElementById("userId").value = "";
            document.getElementById("tableBody").innerHTML = "";
            document.getElementById("message").textContent = "";

            currentUserData = null;
            currentDepartmentData = null;
        }
    </script>

</body>
</html>
 