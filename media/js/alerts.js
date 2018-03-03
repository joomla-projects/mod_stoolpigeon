function request_pack() {
    var confirm_pack = window.confirm('Do you want to create the package with the stored changes?.');
    if (confirm_pack) {
        window.alert('Your request has been sended.\nNote that after create it the stored changes are not deleteds.');
        document.cookie = 'confirm_pack=1';
        document.cookie = 'confirm_discart_diff_tags=0';
        document.cookie = 'confirm_discart_coordinated_task=0';
        window.location.reload();
    } else {
        window.alert('Canceled');
        document.cookie = 'confirm_pack=0';
        document.cookie = 'confirm_discart_diff_tags=0';
        document.cookie = 'confirm_discart_coordinated_task=0';
    }
}


function request_discart_diff_tags() {
    var confirm_discart_diff_tags = window.confirm('Discard the stored changes?\nWARNING!! this one delete all the stored changes that you have present in edit mode and no one package will be created.\nPress Cancel if you wanna to choise other option.');
    if (confirm_discart_diff_tags) {
        window.alert('Your request has been sended.');
        document.cookie = 'confirm_pack=0';
        document.cookie = 'confirm_discart_diff_tags=1';
        document.cookie = 'confirm_discart_coordinated_task=0';
        window.location.reload();
    } else {
        window.alert('Canceled');
        document.cookie = 'confirm_pack=0';
        document.cookie = 'confirm_discart_diff_tags=0';
        document.cookie = 'confirm_discart_coordinated_task=0';
    }
}

function request_discart_coordinated_task() {
    var confirm_discart_coordinated_task = window.confirm('Discard the stored changes?\nWARNING!! this one delete all the stored changes that you have done at the fields comming from the coordinated task and all the coordinated task info -No one package will be created-.\nPress Cancel if you wanna to choise other option.');
    if (confirm_discart_coordinated_task) {
        window.alert('Your request has been sended.');
        document.cookie = 'confirm_pack=0';
        document.cookie = 'confirm_discart_diff_tags=0';
        document.cookie = 'confirm_discart_coordinated_task=1';
        window.location.reload();
    } else {
        window.alert('Canceled');
        document.cookie = 'confirm_pack=0';
        document.cookie = 'confirm_discart_diff_tags=0';
        document.cookie = 'confirm_discart_coordinated_task=0';
    }
}
