/**
 * IT1208 - Web Technologies
 * Client-Side Form Validation for Event Registration
 */

document.addEventListener('DOMContentLoaded', () => {
    // Select the registration form
    const registrationForm = document.getElementById('registrationForm');
    
    // Guard clause: Only run if the form exists on the page
    if (!registrationForm) {
        return;
    }

    // Add a submit listener to intercept form submission
    registrationForm.addEventListener('submit', function(event) {
        // Prevent default form submission initially
        event.preventDefault();

        // Perform validation
        if (validateForm()) {
            // If validation passes, allow the form to submit to the server (PHP)
            this.submit();
        } else {
            // If validation fails, alert the user or show a general error message
            // (Note: individual error messages are shown next to fields)
            alert('Please correct the errors in the form before submitting.');
        }
    });

    /**
     * The main validation function.
     * @returns {boolean} True if the form is valid, false otherwise.
     */
    function validateForm() {
        let isValid = true;

        // 1. Validate Student Name (Mandatory)
        const nameInput = document.getElementById('student_name');
        isValid &= validateMandatory(nameInput, 'Student Name is required.');

        // 2. Validate Student ID (Mandatory and format check - e.g., 6 alphanumeric)
        const idInput = document.getElementById('student_id');
        isValid &= validateMandatory(idInput, 'Student ID is required.');
        if (idInput.value && !/^[A-Z0-9]{5,10}$/i.test(idInput.value)) {
            displayError(idInput, 'Student ID must be 5-10 alphanumeric characters.');
            isValid = false;
        }

        // 3. Validate Email (Mandatory and format check)
        const emailInput = document.getElementById('email');
        isValid &= validateMandatory(emailInput, 'Email is required.');
        // Simple regex for email format validation
        if (emailInput.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            displayError(emailInput, 'Please enter a valid email format (e.g., name@uom.lk).');
            isValid = false;
        }

        // 4. Validate Contact Number (Mandatory and basic numerical check)
        const contactInput = document.getElementById('contact_number');
        isValid &= validateMandatory(contactInput, 'Contact Number is required.');
        // Basic check for 8-15 digits (allows + and spaces for international format)
        if (contactInput.value && !/^[0-9\s\+\-]{8,15}$/.test(contactInput.value)) {
            displayError(contactInput, 'Please enter a valid contact number (8-15 digits).');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Checks if a field is not empty.
     * @param {HTMLElement} inputElement The input field to check.
     * @param {string} errorMessage The error message to display if invalid.
     * @returns {boolean} True if valid.
     */
    function validateMandatory(inputElement, errorMessage) {
        if (inputElement.value.trim() === '') {
            displayError(inputElement, errorMessage);
            return false;
        }
        clearError(inputElement);
        return true;
    }
    
    /**
     * Handles displaying error messages and styling the input.
     */
    function displayError(inputElement, message) {
        inputElement.classList.add('input-error');
        // Find or create the error message span
        let errorSpan = inputElement.nextElementSibling;
        if (!errorSpan || !errorSpan.classList.contains('error-message')) {
            errorSpan = document.createElement('span');
            errorSpan.classList.add('error-message');
            inputElement.parentNode.insertBefore(errorSpan, inputElement.nextSibling);
        }
        errorSpan.textContent = message;
    }

    /**
     * Clears error messages and styling.
     */
    function clearError(inputElement) {
        inputElement.classList.remove('input-error');
        const errorSpan = inputElement.nextElementSibling;
        if (errorSpan && errorSpan.classList.contains('error-message')) {
            errorSpan.textContent = '';
        }
    }

    // Add real-time input listeners to clear errors as the user types
    const inputs = registrationForm.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            // Clear error when user starts typing again
            clearError(input);
        });
    });
});