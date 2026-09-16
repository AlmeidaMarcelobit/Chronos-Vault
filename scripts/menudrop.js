$(document).ready(function () {
    // Mostrar o submenu ao passar o mouse
    $(".drop").on('mouseenter', function () {
        $(this).attr('aria-expanded', 'true');
        $(this).find(".dropdown").stop(true, true).slideDown(300);
    });

    // Ocultar o submenu ao retirar o mouse
    $(".drop").on('mouseleave', function () {
        $(this).attr('aria-expanded', 'false');
        $(this).find(".dropdown").stop(true, true).slideUp(300);
    });
});