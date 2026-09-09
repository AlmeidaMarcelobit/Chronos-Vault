document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePwd');
    const modal = document.getElementById('forgotModal');
    const opener = document.getElementById('forgotBtn');
    const closeButton = document.getElementById('modalClose');
    const lastButton = document.getElementById('modalCloseBtn');
    const content = document.querySelector('.login-wrap');
    const submit = document.getElementById('btnLogin');
    const blocked = submit.disabled;

    toggle.addEventListener('click', function () {
        const visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        toggle.querySelector('i').className = visible ? 'fas fa-eye-slash' : 'fas fa-eye';
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.setAttribute('aria-label', visible ? 'Ocultar senha' : 'Mostrar senha');
        toggle.title = toggle.getAttribute('aria-label');
    });
    opener.addEventListener('click', function (event) {
        event.preventDefault();
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        content.inert = true;
        closeButton.focus();
    });
    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        content.inert = false;
        opener.focus();
    }
    closeButton.addEventListener('click', closeModal);
    lastButton.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (event) {
        if (!modal.classList.contains('open')) return;
        if (event.key === 'Escape') closeModal();
        if (event.key === 'Tab') {
            if (event.shiftKey && document.activeElement === closeButton) {
                event.preventDefault();
                lastButton.focus();
            } else if (!event.shiftKey && document.activeElement === lastButton) {
                event.preventDefault();
                closeButton.focus();
            }
        }
    });
    form.addEventListener('submit', function (event) {
        if (submit.disabled) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        form.setAttribute('aria-busy', 'true');
        document.getElementById('btnIcon').className = 'fas fa-spinner fa-spin';
        document.getElementById('btnLabel').textContent = 'Entrando…';
    });
    window.addEventListener('pageshow', function () {
        submit.disabled = blocked;
        form.removeAttribute('aria-busy');
        document.getElementById('btnIcon').className = 'fas fa-sign-in-alt';
        document.getElementById('btnLabel').textContent = 'Entrar';
    });
});
