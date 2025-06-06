function sleep(time) {
    return new Promise((resolve) => setTimeout(resolve, time));
}

/*Loading personalizado*/
Swal.loading = function (
    title = "In processing",
    content = "Please Wait...",
    callback = function(){ }
) {
    Swal.close();
    let timerInterval;
    Swal.fire({
        showCloseButton: false,
        allowOutsideClick: false,
        title: title,
        html: content,
        timer: false,
        timerProgressBar: true,
        customClass: {
            title: 'swal2-custom-title',
            html: 'swal2-custom-title'
        },
        didOpen: () => {
            Swal.showLoading();
        },
        willClose: () => {
            clearInterval(timerInterval);
        },
        didClose: () => {
            callback();
        }
    })
};

/*Alerta personalizado*/
Swal.alert = async function (title = "", html = "", icon = "info") {
    Swal.close();
    await sleep(100);
    Swal.fire({
        icon: icon,
        title: title,
        html: html,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: true,
        confirmButtonColor: "#3085d6",
        customClass: {
            title: 'swal2-custom-title',
            html: 'swal2-custom-title'
        }
    })
};

Swal.alert_auto_close = async function (title = "", html = "", icon = "info", callback = function(){ }) {

    Swal.close();
    await sleep(100);
    let timerInterval;
    Swal.fire({
        icon: icon,
        title: title,
        html: html,
        timer: 2000,
        timerProgressBar: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            title: 'swal2-custom-title',
            html: 'swal2-custom-title'
        },
        didOpen: () => {
            Swal.showLoading();
            const timer = Swal.getPopup().querySelector("b");
            timerInterval = setInterval(() => {
                //timer.textContent = '${Swal.getTimerLeft()}';
            }, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            callback();
        }
    });

}

/*Alerta Dialogo personalizado*/
Swal.alert_dialog = async function (title = "Are you sure?", html = "", icon = "warning", btnCancel = false, callback = function(){ }) {
    Swal.close();
    await sleep(100);
    Swal.fire({
        title: title,
        html: html,
        icon: icon,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: btnCancel,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        customClass: {
            title: 'swal2-custom-title',
            html: 'swal2-custom-title'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
};


Swal.alert_dialog_confirmation = async function (title = "Do you want to save the changes?", html = "", icon = "question", callback_ok = function () { }, callback_cancel = function () { }, confirmButtonText = "OK", cancelButtonText = "Cancel") {
    Swal.close();
    await sleep(100);
    Swal.fire({
        title: title,
        html: html,
        icon: icon,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        customClass: {
            title: 'swal2-custom-title',
            html: 'swal2-custom-title'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            callback_ok();
        } else {
            callback_cancel();
        }
    });
};


Swal.alert_dialog_confirmation_whit_dany = async function (title = "Do you want to save the changes?", html = "", icon = "question", callback_ok = function () { }, callback_deny = function () { }, callback_cancel = function () { }, confirmButtonText = "OK", cancelButtonText = "Cancel", denyButtonText = "Deny") {
    Swal.close();
    await sleep(100);
    Swal.fire({
        title: title,
        html: html,
        icon: icon,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        denyButtonText: denyButtonText,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#636464FF",
        denyButtonColor: "#044F94FF",
        customClass: {
            title: 'swal2-custom-title',
            html: 'swal2-custom-title'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            callback_ok();
        } else if (result.isDenied) {
            callback_deny();
        } else {
            callback_cancel();
        }
    });
};

