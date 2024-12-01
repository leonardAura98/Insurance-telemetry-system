document.addEventListener('DOMContentLoaded', () => {
    fetchAdminStats();
});

function fetchAdminStats() {
    fetch('../php/admin.php?action=fetch_dashboard_stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-users').textContent = `${data.totalUsers} Users`;
            document.getElementById('total-vehicles').textContent = `${data.totalVehicles} Vehicles`;
            document.getElementById('pending-claims').textContent = `${data.pendingClaims} Claims`;
        })
        .catch(error => console.error('Error fetching admin stats:', error));
}
