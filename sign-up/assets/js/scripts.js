// Get DOM elements for sign-in and sign-up transitions
const signUpButton = document.getElementById("signUp");
const signInButton = document.getElementById("signIn");
const container = document.getElementById("container");

// Event listeners to handle the form transitions
signUpButton.addEventListener("click", () => {
    container.classList.add("right-panel-active");
});

signInButton.addEventListener("click", () => {
    container.classList.remove("right-panel-active");
});

// Functionality for Reset Password
const resetPasswordForm = document.getElementById("resetPasswordForm");
if (resetPasswordForm) {
    resetPasswordForm.addEventListener("submit", (event) => {
        event.preventDefault();
        const newPassword = resetPasswordForm.querySelector('input[type="password"]').value;
        const confirmPassword = resetPasswordForm.querySelectorAll('input[type="password"]')[1].value;

        if (newPassword === confirmPassword) {
            alert("Password reset successfully!");
            // Here you would typically send the new password to the server
        } else {
            alert("Passwords do not match!");
        }
    });
}

// Functionality for Forgot Password
const forgotPasswordForm = document.getElementById("forgotPasswordForm");
if (forgotPasswordForm) {
    forgotPasswordForm.addEventListener("submit", (event) => {
        event.preventDefault();
        const email = forgotPasswordForm.querySelector('input[type="email"]').value;

        if (email) {
            alert("Reset link sent to " + email);
            // Here you would typically send the email to the server
        } else {
            alert("Please enter your registered email!");
        }
    });
}
