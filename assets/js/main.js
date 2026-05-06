'use strict';

function validateRequired(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    if (!field || !error) return true;
    const value = field.value.trim();
    if (value === '') {
        error.textContent = 'This field is required.';
        field.style.borderColor = '#c0392b';
        field.style.borderWidth = '2px';
        return false;
    }
    error.textContent = '';
    field.style.borderColor = '';
    field.style.borderWidth = '';
    return true;
}

function validateEmail(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    if (!field || !error) return true;
    const value = field.value.trim();
    if (value === '') {
        error.textContent = 'Email address is required.';
        field.style.borderColor = '#c0392b';
        field.style.borderWidth = '2px';
        return false;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    if (!emailRegex.test(value)) {
        error.textContent = 'Please enter a valid email address.';
        field.style.borderColor = '#c0392b';
        field.style.borderWidth = '2px';
        return false;
    }
    error.textContent = '';
    field.style.borderColor = '';
    field.style.borderWidth = '';
    return true;
}

function validateMinLength(fieldId, errorId, min) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    if (!field || !error) return true;
    const value = field.value.trim();
    if (value.length < min) {
        error.textContent = 'Must be at least ' + min + ' characters long.';
        field.style.borderColor = '#c0392b';
        field.style.borderWidth = '2px';
        return false;
    }
    error.textContent = '';
    field.style.borderColor = '';
    field.style.borderWidth = '';
    return true;
}

function validateFileType(fieldId, errorId, allowedTypes) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    if (!field || !error) return true;
    if (!field.files || field.files.length === 0) {
        error.textContent = '';
        return true;
    }
    const fileName  = field.files[0].name;
    const extension = fileName.split('.').pop().toLowerCase();
    if (!allowedTypes.includes(extension)) {
        error.textContent = 'Invalid file type. Allowed: ' + allowedTypes.join(', ').toUpperCase() + '.';
        return false;
    }
    error.textContent = '';
    return true;
}

function validateFileSize(fieldId, errorId, maxSizeMB) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    if (!field || !error) return true;
    if (!field.files || field.files.length === 0) {
        error.textContent = '';
        return true;
    }
    const fileSizeBytes = field.files[0].size;
    const maxBytes      = maxSizeMB * 1024 * 1024;
    if (fileSizeBytes > maxBytes) {
        error.textContent = 'File is too large. Maximum size is ' + maxSizeMB + 'MB.';
        return false;
    }
    error.textContent = '';
    return true;
}

document.addEventListener('DOMContentLoaded', function () {

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            let isValid = true;
            if (!validateRequired('name', 'name-error')) isValid = false;
            if (!validateEmail('email', 'email-error')) isValid = false;
            if (!validateRequired('password', 'password-error')) isValid = false;
            if (!validateMinLength('password', 'password-error', 8)) isValid = false;
            const passwordField = document.getElementById('password');
            const confirmField  = document.getElementById('confirm_password');
            const confirmError  = document.getElementById('confirm-password-error');
            if (confirmField && confirmError && passwordField) {
                if (confirmField.value.trim() === '') {
                    confirmError.textContent = 'Please confirm your password.';
                    confirmField.style.borderColor = '#c0392b';
                    confirmField.style.borderWidth = '2px';
                    isValid = false;
                } else if (confirmField.value !== passwordField.value) {
                    confirmError.textContent = 'Passwords do not match.';
                    confirmField.style.borderColor = '#c0392b';
                    confirmField.style.borderWidth = '2px';
                    isValid = false;
                } else {
                    confirmError.textContent = '';
                    confirmField.style.borderColor = '';
                    confirmField.style.borderWidth = '';
                }
            }
            if (!isValid) e.preventDefault();
        });
        ['name', 'email', 'password', 'confirm_password'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function () {
                    this.style.borderColor = '';
                    this.style.borderWidth = '';
                });
            }
        });
    }

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            let isValid = true;
            if (!validateEmail('email', 'email-error')) isValid = false;
            if (!validateRequired('password', 'password-error')) isValid = false;
            if (!isValid) e.preventDefault();
        });
        ['email', 'password'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function () {
                    this.style.borderColor = '';
                    this.style.borderWidth = '';
                });
            }
        });
    }

    const menuItemForm = document.getElementById('menu-item-form');
    if (menuItemForm) {
        menuItemForm.addEventListener('submit', function (e) {
            let isValid = true;
            if (!validateRequired('name', 'name-error'))         isValid = false;
            if (!validateRequired('price', 'price-error'))       isValid = false;
            if (!validateRequired('category', 'category-error')) isValid = false;
            const imageField = document.getElementById('image');
            if (imageField && imageField.files && imageField.files.length > 0) {
                if (!validateFileType('image', 'image-error', ['jpg', 'jpeg', 'png'])) isValid = false;
                if (!validateFileSize('image', 'image-error', 2)) isValid = false;
            }
            if (!isValid) e.preventDefault();
        });
    }

    const pdfForm = document.getElementById('pdf-upload-form');
    if (pdfForm) {
        pdfForm.addEventListener('submit', function (e) {
            let isValid = true;
            const pdfField = document.getElementById('menu_pdf');
            if (pdfField && pdfField.files && pdfField.files.length > 0) {
                if (!validateFileType('menu_pdf', 'pdf-error', ['pdf'])) isValid = false;
                if (!validateFileSize('menu_pdf', 'pdf-error', 5)) isValid = false;
            } else {
                const pdfError = document.getElementById('pdf-error');
                if (pdfError) {
                    pdfError.textContent = 'Please select a PDF file to upload.';
                    isValid = false;
                }
            }
            if (!isValid) e.preventDefault();
        });
    }

    const orderForm = document.getElementById('order-form');
    if (orderForm) {
        orderForm.addEventListener('submit', function (e) {
            let isValid = true;
            const qtyField = document.getElementById('quantity');
            const qtyError = document.getElementById('quantity-error');
            if (qtyField && qtyError) {
                const qty = parseInt(qtyField.value, 10);
                if (isNaN(qty) || qty < 1 || qty > 20) {
                    qtyError.textContent = 'Quantity must be between 1 and 20.';
                    qtyField.style.borderColor = '#c0392b';
                    qtyField.style.borderWidth = '2px';
                    isValid = false;
                } else {
                    qtyError.textContent = '';
                    qtyField.style.borderColor = '';
                    qtyField.style.borderWidth = '';
                }
            }
            if (!isValid) e.preventDefault();
        });
    }

    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.classList.add('dismissing');
            setTimeout(function () {
                if (alert && alert.parentNode) alert.parentNode.removeChild(alert);
            }, 420);
        }, 4000);
    });

    const dangerButtons = document.querySelectorAll('.btn-danger[type="submit"], .btn-danger[data-confirm]');
    dangerButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const message = btn.getAttribute('data-confirm')
                || 'Are you sure you want to delete this item? This action cannot be undone.';
            if (!window.confirm(message)) e.preventDefault();
        });
    });

    const animatedElements = document.querySelectorAll('.card, .form-card, .stat-card, .menu-card');
    animatedElements.forEach(function (el, index) {
        el.style.animationDelay = (index * 0.05) + 's';
        el.style.animationFillMode = 'both';
    });

    const itemSelect    = document.getElementById('menu_item_id');
    const quantityInput = document.getElementById('quantity');
    const pricePreview  = document.getElementById('price-preview');

    function updatePricePreview() {
        if (!itemSelect || !quantityInput || !pricePreview) return;
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const price    = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const quantity = parseInt(quantityInput.value, 10) || 0;
        if (price > 0 && quantity > 0) {
            const total = (price * quantity).toFixed(2);
            pricePreview.textContent = 'Estimated Total: $' + total;
            pricePreview.style.display = 'block';
        } else {
            pricePreview.textContent = '';
            pricePreview.style.display = 'none';
        }
    }

    if (itemSelect)    itemSelect.addEventListener('change', updatePricePreview);
    if (quantityInput) quantityInput.addEventListener('input', updatePricePreview);
    updatePricePreview();

    const imageInput   = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                imagePreview.style.display = 'none';
                imagePreview.src = '';
                return;
            }
            if (!file.type.startsWith('image/')) {
                imagePreview.style.display = 'none';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                imagePreview.style.maxWidth = '200px';
                imagePreview.style.marginTop = '10px';
                imagePreview.style.border = '1.5px solid #BBBFCA';
                imagePreview.style.filter = 'none';
                imagePreview.style.borderRadius = '6px';
            };
            reader.readAsDataURL(file);
        });
    }

});
