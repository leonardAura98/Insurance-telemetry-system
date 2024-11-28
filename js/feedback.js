js
class FeedbackSystem {
    constructor() {
        this.container = this.createContainer();
        this.messageQueue = [];
        this.isProcessing = false;
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'message-container';
        document.body.appendChild(container);
        return container;
    }

    showMessage(type, message, duration = 5000) {
        this.messageQueue.push({ type, message, duration });
        if (!this.isProcessing) {
            this.processQueue();
        }
    }

    async processQueue() {
        if (this.messageQueue.length === 0) {
            this.isProcessing = false;
            return;
        }

        this.isProcessing = true;
        const { type, message, duration } = this.messageQueue.shift();

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        this.container.appendChild(alertDiv);

        await new Promise(resolve => setTimeout(resolve, duration));
        alertDiv.style.animation = 'slideOut 0.5s ease-in forwards';
        await new Promise(resolve => setTimeout(resolve, 500));
        alertDiv.remove();
        this.processQueue();
    }

    handleUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const messageMap = {
            'signup_success': { type: 'success', message: 'Registration successful! Please login' },
            'login_required': { type: 'warning', message: 'Please login to continue' },
            'invalid_credentials': { type: 'error', message: 'Invalid username or password' },
            'user_exists': { type: 'error', message: 'Username or email already exists' },
            'passwords_mismatch': { type: 'error', message: 'Passwords do not match' },
            'file_upload_error': { type: 'error', message: 'Error uploading file' },
            'claim_submitted': { type: 'success', message: 'Claim submitted successfully' },
            'submission_failed': { type: 'error', message: 'Failed to submit claim' },
            'logout_success': { type: 'success', message: 'Logged out successfully' }
        };

        for (const [param, value] of urlParams.entries()) {
            if (messageMap[value]) {
                this.showMessage(messageMap[value].type, messageMap[value].message);
            }
        }
    }
}

const feedback = new FeedbackSystem();
document.addEventListener('DOMContentLoaded', () => feedback.handleUrlParams());
window.showMessage = (type, message) => feedback.showMessage(type, message);