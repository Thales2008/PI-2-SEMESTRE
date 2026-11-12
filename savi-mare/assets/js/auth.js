document.addEventListener("DOMContentLoaded", () => {
    const registerForm = document.querySelector('form[action="criar-conta.php"]');

    if (registerForm) {
        registerForm.addEventListener("submit", (event) => {
            const password = registerForm.querySelector('[name="senha"]');
            const confirmation = registerForm.querySelector('[name="confirmar_senha"]');

            if (password && confirmation && password.value !== confirmation.value) {
                event.preventDefault();
                confirmation.setCustomValidity("As senhas não coincidem.");
                confirmation.reportValidity();
                confirmation.addEventListener("input", () => {
                    confirmation.setCustomValidity("");
                }, { once: true });
            }
        });
    }
});
