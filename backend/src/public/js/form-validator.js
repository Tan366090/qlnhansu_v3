// Form Validator Class
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.fields = new Map();
        this.errors = new Map();
    }

    // Add validation rules for a field
    addField(fieldId, rules) {
        this.fields.set(fieldId, rules);
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', () => this.validateField(fieldId));
            field.addEventListener('input', () => this.clearError(fieldId));
        }
    }

    // Validate a single field
    validateField(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return true;

        const rules = this.fields.get(fieldId);
        if (!rules) return true;

        const value = field.value.trim();
        let isValid = true;

        for (const [rule, params] of Object.entries(rules)) {
            switch (rule) {
                case 'required':
                    if (!value) {
                        this.addError(fieldId, 'Trường này là bắt buộc');
                        isValid = false;
                    }
                    break;

                case 'minLength':
                    if (value.length < params) {
                        this.addError(fieldId, `Tối thiểu ${params} ký tự`);
                        isValid = false;
                    }
                    break;

                case 'maxLength':
                    if (value.length > params) {
                        this.addError(fieldId, `Tối đa ${params} ký tự`);
                        isValid = false;
                    }
                    break;

                case 'email':
                    if (!this.isValidEmail(value)) {
                        this.addError(fieldId, 'Email không hợp lệ');
                        isValid = false;
                    }
                    break;

                case 'phone':
                    if (!this.isValidPhone(value)) {
                        this.addError(fieldId, 'Số điện thoại không hợp lệ');
                        isValid = false;
                    }
                    break;

                case 'date':
                    if (!this.isValidDate(value)) {
                        this.addError(fieldId, 'Ngày không hợp lệ');
                        isValid = false;
                    }
                    break;

                case 'number':
                    if (!this.isValidNumber(value)) {
                        this.addError(fieldId, 'Giá trị phải là số');
                        isValid = false;
                    }
                    break;

                case 'custom':
                    if (!params(value)) {
                        this.addError(fieldId, 'Giá trị không hợp lệ');
                        isValid = false;
                    }
                    break;
            }

            if (!isValid) break;
        }

        if (isValid) {
            this.clearError(fieldId);
        }

        return isValid;
    }

    // Validate entire form
    validateForm() {
        let isValid = true;
        this.errors.clear();

        for (const [fieldId] of this.fields) {
            if (!this.validateField(fieldId)) {
                isValid = false;
            }
        }

        return isValid;
    }

    // Add error message
    addError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        this.errors.set(fieldId, message);
        field.classList.add('is-invalid');

        let errorElement = field.nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
            errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback';
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        }

        errorElement.textContent = message;
    }

    // Clear error message
    clearError(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        this.errors.delete(fieldId);
        field.classList.remove('is-invalid');

        const errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('invalid-feedback')) {
            errorElement.textContent = '';
        }
    }

    // Helper validation methods
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    isValidPhone(phone) {
        const re = /^[0-9]{10,11}$/;
        return re.test(phone);
    }

    isValidDate(date) {
        const d = new Date(date);
        return d instanceof Date && !isNaN(d);
    }

    isValidNumber(value) {
        return !isNaN(value) && !isNaN(parseFloat(value));
    }

    // Get form data
    getFormData() {
        const formData = {};
        for (const [fieldId] of this.fields) {
            const field = document.getElementById(fieldId);
            if (field) {
                formData[fieldId] = field.value.trim();
            }
        }
        return formData;
    }

    // Reset form
    resetForm() {
        if (this.form) {
            this.form.reset();
        }
        for (const [fieldId] of this.fields) {
            this.clearError(fieldId);
        }
        this.errors.clear();
    }
}

// Example usage:
/*
const validator = new FormValidator('myForm');

validator.addField('email', {
    required: true,
    email: true
});

validator.addField('phone', {
    required: true,
    phone: true
});

validator.addField('password', {
    required: true,
    minLength: 6,
    maxLength: 20
});

document.getElementById('myForm').addEventListener('submit', (e) => {
    e.preventDefault();
    if (validator.validateForm()) {
        // Form is valid, proceed with submission
        const formData = validator.getFormData();
        // ... handle form submission
    }
});
*/ 