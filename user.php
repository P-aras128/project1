
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
    </style>
</head>

<body>

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
                message.textContent = "Data fetch nahi ho saka. Route ya server response check karo.";
                console.error(error);
            }
        }

      
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
   