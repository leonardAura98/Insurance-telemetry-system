class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (this.form) {
            this.initializeValidation();
        }
    }

    initializeValidation() {
        this.form.addEventListener('submit', (e) => this.validateForm(e));
    }

    validateForm(e) {
        let isValid = true;

        // Password validation
        const password = this.form.querySelector('input[name="password"]');
        const confirmPassword = this.form.querySelector('input[name="confirm_password"]');
        
        if (password && confirmPassword) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                feedback.showMessage('error', 'Passwords do not match');
                isValid = false;
            }

            if (password.value.length < 8) {
                e.preventDefault();
                feedback.showMessage('error', 'Password must be at least 8 characters');
                isValid = false;
            }
        }

        // File validation
        const fileInput = this.form.querySelector('input[type="file"]');
        if (fileInput && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (file.size > maxSize) {
                e.preventDefault();
                feedback.showMessage('error', 'File size must be less than 5MB');
                isValid = false;
            }
        }

        return isValid;
    }
}

// Initialize form validation
document.addEventListener('DOMContentLoaded', () => {
    new FormValidator('signup-form');
    new FormValidator('claim-form');
}); 