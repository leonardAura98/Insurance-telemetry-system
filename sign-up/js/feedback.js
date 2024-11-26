function showMessage(type, message) {
    const messageDiv = document.getElementById('message-container');
    if (!messageDiv) {
        const div = document.createElement('div');
        div.id = 'message-container';
        document.querySelector('.container').prepend(div);
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    document.getElementById('message-container').appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Parse URL parameters for error messages
function handleUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    
    const errorMessages = {
        'passwords_mismatch': 'Passwords do not match',
        'file_upload': 'Error uploading ID photo',
        'user_exists': 'Username or email already exists',
        'database': 'Database error occurred',
        'server': 'Server error occurred',
        'email_not_found': 'Email not found',
        'email_sent': 'Password reset email sent'
    };
    
    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        if (errorMessages[error]) {
            showMessage('error', errorMessages[error]);
        }
    }
    
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        if (errorMessages[status]) {
            showMessage('success', errorMessages[status]);
        }
    }
}

// Call this when the page loads
document.addEventListener('DOMContentLoaded', handleUrlParams); 