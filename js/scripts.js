// Sidebar toggle functionality
document.getElementById('sidebar').addEventListener('mouseover', function() {
    this.style.width = '250px';
});
document.getElementById('sidebar').addEventListener('mouseout', function() {
    this.style.width = '60px';
});

// Fetch and display dashboard stats
function fetchDashboardStats() {
    fetch('../php/fetch_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('vehicle-count').textContent = `${data.vehicles} Registered`;
            document.getElementById('active-premiums').textContent = `${data.active_premiums} Active`;
            document.getElementById('recent-claims').textContent = `${data.recent_claims} Recent`;
        })
        .catch(error => console.error('Error fetching dashboard stats:', error));
}

// Initialize dashboard stats
document.addEventListener('DOMContentLoaded', fetchDashboardStats);
