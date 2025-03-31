document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = button.previousElementSibling;
            const icon = button.querySelector('i');
            
            // Toggle password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Form validation
    const registerForm = document.querySelector('.register-form');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const adminRole = document.getElementById('admin_role').value;
            const terms = document.getElementById('terms').checked;
            
            let isValid = true;
            
            // Name validation
            if (firstName.trim() === '') {
                showError('first_name', 'First name is required');
                isValid = false;
            } else {
                clearError('first_name');
            }
            
            if (lastName.trim() === '') {
                showError('last_name', 'Last name is required');
                isValid = false;
            } else {
                clearError('last_name');
            }
            
            // Email validation
            if (!validateEmail(email)) {
                showError('email', 'Please enter a valid email address');
                isValid = false;
            } else {
                clearError('email');
            }
            
            // Password validation
            if (password.length < 8) {
                showError('password', 'Password must be at least 8 characters long');
                isValid = false;
            } else {
                clearError('password');
            }
            
            // Confirm password validation
            if (password !== confirmPassword) {
                showError('confirm_password', 'Passwords do not match');
                isValid = false;
            } else {
                clearError('confirm_password');
            }
            
            // Role validation
            if (adminRole === '') {
                showError('admin_role', 'Please select an admin role');
                isValid = false;
            } else {
                clearError('admin_role');
            }
            
            // Terms validation
            if (!terms) {
                showError('terms', 'You must agree to the terms and conditions');
                isValid = false;
            } else {
                clearError('terms');
            }
            
            // Prevent form submission if validation fails
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
    
    // Real-time password matching
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (passwordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener('keyup', function() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                showError('confirm_password', 'Passwords do not match');
            } else {
                clearError('confirm_password');
            }
        });
    }
    
    // Email validation function
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Show error message
    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        input.classList.add('is-invalid');
        
        // For checkbox, handle differently
        if (input.type === 'checkbox') {
            let errorMessage = input.nextElementSibling.nextElementSibling;
            if (!errorMessage || !errorMessage.classList.contains('invalid-feedback')) {
                errorMessage = document.createElement('div');
                errorMessage.classList.add('invalid-feedback', 'd-block');
                input.nextElementSibling.after(errorMessage);
            }
            errorMessage.textContent = message;
        } else {
            // Check if error message exists
            let errorMessage = input.parentElement.nextElementSibling;
            if (!errorMessage || !errorMessage.classList.contains('invalid-feedback')) {
                errorMessage = document.createElement('div');
                errorMessage.classList.add('invalid-feedback');
                input.parentElement.after(errorMessage);
            }
            errorMessage.textContent = message;
        }
    }
    
    // Clear error message
    function clearError(inputId) {
        const input = document.getElementById(inputId);
        input.classList.remove('is-invalid');
        
        // For checkbox, handle differently
        if (input.type === 'checkbox') {
            const errorMessage = input.nextElementSibling.nextElementSibling;
            if (errorMessage && errorMessage.classList.contains('invalid-feedback')) {
                errorMessage.textContent = '';
            }
        } else {
            // Check if error message exists
            const errorMessage = input.parentElement.nextElementSibling;
            if (errorMessage && errorMessage.classList.contains('invalid-feedback')) {
                errorMessage.textContent = '';
            }
        }
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-success)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});