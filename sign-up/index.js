document.addEventListener("DOMContentLoaded", () => {
    const signUpButton = document.getElementById("signUp");
    const signInButton = document.getElementById("signIn");
    const container = document.getElementById("container");

    if (signUpButton && signInButton && container) {
        signUpButton.addEventListener("click", () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener("click", () => {
            container.classList.remove("right-panel-active");
        });
    }

    const forgotPasswordForm = document.getElementById("forgotPasswordForm");
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const email = document.getElementById("email").value;
            alert(`Reset link sent to ${email}.`);
            setTimeout(() => {
                window.location.href = "resetpassword.html";
            }, 1000);
        });
    }

    const resetPasswordForm = document.getElementById("resetPasswordForm");
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            if (newPassword !== confirmPassword) {
                alert("Passwords do not match!");
                return;
            }
            alert("Password updated successfully.");
            setTimeout(() => {
                window.location.href = "index.html";
            }, 1000);
        });
    }
});
