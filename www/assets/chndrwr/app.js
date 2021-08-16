function msg(title, text, dismissable, type = 'success', timeout = '') {
    timeout = timeout === '' ? 5000 : timeout;
    text = $.parseHTML(text);
    let opts = {
        "closeButton": dismissable,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": timeout,
        "extendedTimeOut": 1000,
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut",
        "tapToDismiss": false
    };
    switch (type) {
        case 'error':
            toastr.error(text, title, opts);
            break;
        case 'info':
            toastr.info(text, title, opts);
            break;
        case 'warning':
            toastr.warning(text, title, opts);
            break;
        default:
            toastr.success(text, title, opts);
            break;
    }
}