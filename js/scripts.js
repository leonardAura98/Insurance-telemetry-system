// Fetch users from the server
function fetchUsers() {
    fetch('../php/admin.php?action=fetch_users')
        .then(response => {
            // Check if the response is okay (status 200)
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            let usersHtml = '';
            data.forEach(user => {
                usersHtml += `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.fullname}</td>
                        <td>${user.email}</td>
                        <td>${user.username}</td>
                        <td>${user.role}</td>
                        <td>
                            <button onclick="editUser(${user.id})" style="background: #2196F3;">Edit</button>
                            <button onclick="deleteUser(${user.id})" style="background: #f44336;">Delete</button>
                        </td>
                    </tr>
                `;
            });
            document.getElementById('users-list').innerHTML = usersHtml;
        })
        .catch(error => console.error('Error fetching users:', error));
}

// Filter users based on search term and role
function filterUsers() {
    const searchTerm = document.getElementById('search-input').value;
    const roleFilter = document.getElementById('role-filter').value;
    
    fetch(`../php/admin.php?action=fetch_users&search=${encodeURIComponent(searchTerm)}&role=${encodeURIComponent(roleFilter)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Update table with filtered data
            let usersHtml = '';
            data.forEach(user => {
                usersHtml += `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.fullname}</td>
                        <td>${user.email}</td>
                        <td>${user.username}</td>
                        <td>${user.role}</td>
                        <td>
                            <button onclick="editUser(${user.id})" style="background: #2196F3;">Edit</button>
                            <button onclick="deleteUser(${user.id})" style="background: #f44336;">Delete</button>
                        </td>
                    </tr>
                `;
            });
            document.getElementById('users-list').innerHTML = usersHtml;
        })
        .catch(error => console.error('Error filtering users:', error));
}

// Delete user function
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('../php/admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_user&id=${userId}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(result => {
            alert(result);
            fetchUsers(); // Refresh the user list
        })
        .catch(error => console.error('Error deleting user:', error));
}

// Load users on page load
document.addEventListener('DOMContentLoaded', fetchUsers);