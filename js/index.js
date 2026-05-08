document.addEventListener('DOMContentLoaded', function () {
    const globalAlerts = document.querySelectorAll('.global-alert');
    globalAlerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function () {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});
