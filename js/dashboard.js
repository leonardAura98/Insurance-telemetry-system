document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardStats();
});

function fetchDashboardStats() {
    fetch('../php/user.php?action=get_dashboard_stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('vehicle-count').textContent = `${data.vehicles} Registered`;
            document.getElementById('premium-count').textContent = `${data.premiums} Active`;
            document.getElementById('next-payment').textContent = data.next_payment;

            let activityHtml = '<ul style="list-style: none;">';
            data.recent_activity.forEach(activity => {
                activityHtml += `<li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                    ${activity.description}
                    <span style="float: right; color: #666;">${activity.date}</span>
                </li>`;
            });
            activityHtml += '</ul>';
            document.getElementById('recent-activity').innerHTML = activityHtml;
        })
        .catch(error => console.error('Error fetching dashboard stats:', error));
}
