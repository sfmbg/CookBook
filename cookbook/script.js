function checkPasswordMatch() {
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();
    const feedback = document.getElementById('passwordMatchFeedback');

    if (password !== confirmPassword) {
        feedback.textContent = 'Hesla se neshodují';
    } else {
        feedback.textContent = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('password').addEventListener('input', checkPasswordMatch);
    document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
});

function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Validace username AJAX
async function validateUsername() {
    const username = document.getElementById('username').value.trim();
    const feedback = document.getElementById('usernameFeedback');

    if (!username) {
        feedback.textContent = '';
        feedback.classList.remove('valid', 'invalid');
        return;
    }

    // csrf
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch('proajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin', // Přidá cookies do požadavku
            body: `username=${encodeURIComponent(username)}&csrf_token=${encodeURIComponent(csrfToken)}`
        });

        const data = await response.json();

        if (data.status === 'available') {
            feedback.style.color = 'green';
            feedback.textContent = data.message;
            feedback.classList.add('valid');
            feedback.classList.remove('invalid');
        } else if (data.status === 'exists') {
            feedback.style.color = 'red';
            feedback.textContent = data.message;
            feedback.classList.add('invalid');
            feedback.classList.remove('valid');
        } else {
            feedback.style.color = 'orange'; // Pro případ jiných stavů
            feedback.textContent = data.message;
            feedback.classList.add('invalid');
            feedback.classList.remove('valid');
        }
    } catch (error) {
        console.error('Error:', error);
        feedback.style.color = 'red';
        feedback.textContent = 'Nastala chyba při validaci.';
        feedback.classList.add('invalid');
        feedback.classList.remove('valid');
    }
}

// Kontrola shody hesel
function checkPasswordMatch() {
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();
    const feedback = document.getElementById('passwordMatchFeedback');
    const submitBtn = document.getElementById('submitBtn');

    if (password === "" && confirmPassword === "") {
        feedback.textContent = '';
        feedback.classList.remove('valid', 'invalid');
        submitBtn.disabled = false;
        return;
    }

    if (password === confirmPassword) {
        feedback.textContent = 'Hesla se shodují.';
        feedback.style.color = 'green';
        feedback.classList.add('valid');
        feedback.classList.remove('invalid');
        submitBtn.disabled = false;
    } else {
        feedback.textContent = 'Hesla se neshodují.';
        feedback.style.color = 'red';
        feedback.classList.add('invalid');
        feedback.classList.remove('valid');
        submitBtn.disabled = true;
    }
}

const debouncedValidateUsername = debounce(validateUsername, 300);

document.addEventListener('DOMContentLoaded', () => {
    const usernameElement = document.getElementById('username');
    usernameElement.addEventListener('keyup', debouncedValidateUsername);

    const passwordElement = document.getElementById('password');
    const confirmPasswordElement = document.getElementById('confirm_password');

    passwordElement.addEventListener('input', checkPasswordMatch);
    confirmPasswordElement.addEventListener('input', checkPasswordMatch);
});
