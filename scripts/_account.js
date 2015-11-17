






function recoverCleanup(){
    $('#ajax-modal').removeClass('recover-modal');
    $('#modal_close').unbind('click');
    document.getElementById('modal_h1').innerHTML = '';
    document.getElementById('modal_content').innerHTML = '';
}
function recover(a){
    document.getElementById('fullscreenload').style.display = 'block';
    $('#modal_close').click(function() {
        recoverCleanup();
    });
    $('#ajax-modal').addClass('recover-modal');
    if(a === 'pword'){
        var u = "/account/ajax/password/";
        var e = document.getElementById('recover_pword_email').value;
    }else if(a === 'uname'){
        var u = "/account/ajax/username/";
        var e = document.getElementById('recover_uname_email').value;
    }
    $.get(
        u,
        {email:e},
        function( data ) {
            document.getElementById('modal_h1').innerHTML = data.h1;
            document.getElementById('modal_content').innerHTML = data.content;
            document.getElementById('fullscreenload').style.display = 'none';
            showModal('ajax-modal');
        },
        "json"
    )
    .fail(function() {
        $('#ajax-modal').removeClass('change-email');
        document.getElementById('modal_h1').innerHTML = 'Error';
        document.getElementById('modal_content').innerHTML = '<p>There was an error updating your account. Please refresh the page and try again.</p><p>(ref. ajax fail)<p>';
        document.getElementById('fullscreenload').style.display = 'none';
        showModal('ajax-modal');
    });
}



$(function() {
    $('#ebay-check').change(function() {
        if(this.checked) {
            document.getElementById('ebay-hide').style.display = 'block';
        }else{
            document.getElementById('ebay-hide').style.display = 'none';
            document.getElementById('ebay').value = '';
        }
    });
});





function changeEmailUnBindForms(){
    $('#change-email-field').unbind('keypress');
    $('#change-email-button').unbind('click');
}
function changeEmailCleanup(){
    $('#ajax-modal').removeClass('change-email');
    $('#modal_close').unbind('click');
    changeEmailUnBindForms();
    document.getElementById('modal_h1').innerHTML = '';
    document.getElementById('modal_content').innerHTML = '';
}
function changeEmail(s, d1){
    changeEmailUnBindForms();
    document.getElementById('fullscreenload').style.display = 'block';
    $('#modal_close').click(function() {
        changeEmailCleanup();
    });
    $.get(
        "/account/ajax/email/",
        { step:s,  data1:d1},
        function( data ) {
            document.getElementById('modal_h1').innerHTML = data.h1;
            document.getElementById('modal_content').innerHTML = data.content;
            $('#ajax-modal').addClass('change-email');
            if(s === 1){
                if(data.error === '0'){
                    $('#change-email-field').keypress(function(event) {
                        if (event.keyCode === 13) {
                            var dd;
                            dd = document.getElementById('change-email-field').value;
                            changeEmail(2, dd);
                        }
                    });
                    $('#change-email-button').click(function() {
                        var dd;
                        dd = document.getElementById('change-email-field').value;
                        changeEmail(2, dd);
                    });
                }
                showModal('ajax-modal');
            }else if(s === 2){
                if(data.error === '0'){
                    document.getElementById('account-email').innerHTML = data.email;
                }else{
                    $('#change-email-field').keypress(function(event) {
                        if (event.keyCode === 13) {
                            var dd;
                            dd = document.getElementById('change-email-field').value;
                            changeEmail(2, dd);
                        }
                    });
                    $('#change-email-button').click(function() {
                        var dd;
                        dd = document.getElementById('change-email-field').value;
                        changeEmail(2, dd);
                    });
                }
            }
            document.getElementById('fullscreenload').style.display = 'none';
        },
        "json"
    )
    .fail(function() {
        $('#ajax-modal').removeClass('change-email');
        document.getElementById('modal_h1').innerHTML = 'Error';
        document.getElementById('modal_content').innerHTML = '<p>There was an error updating your account. Please refresh the page and try again.</p><p>(ref. ajax fail)<p>';
        document.getElementById('fullscreenload').style.display = 'none';
        if(s === 1){
            showModal('ajax-modal');
        }
    });
}





function updateEbayUnBindForms(){
    $('#change-email-field').unbind('keypress');
    $('#change-email-button').unbind('click');
}
function updateEbayCleanup(){
    $('#ajax-modal').removeClass('change-email');
    $('#modal_close').unbind('click');
    updateEbayUnBindForms();
    document.getElementById('modal_h1').innerHTML = '';
    document.getElementById('modal_content').innerHTML = '';
}
function updateEbay(s, d1){
    changeEmailUnBindForms();
    document.getElementById('fullscreenload').style.display = 'block';
    $('#modal_close').click(function() {
        updateEbayCleanup();
    });
    $.get(
        "/account/ajax/ebay/",
        { step:s,  data1:d1},
        function( data ) {
            document.getElementById('modal_h1').innerHTML = data.h1;
            document.getElementById('modal_content').innerHTML = data.content;
            $('#ajax-modal').addClass('change-email');
            if(s === 1){
                if(data.error === '0'){
                    $('#change-email-field').keypress(function(event) {
                        if (event.keyCode === 13) {
                            var dd;
                            dd = document.getElementById('change-email-field').value;
                            updateEbay(2, dd);
                        }
                    });
                    $('#change-email-button').click(function() {
                        var dd;
                        dd = document.getElementById('change-email-field').value;
                        updateEbay(2, dd);
                    });
                }
                showModal('ajax-modal');
            }else if(s === 2){
                if(data.error === '0'){
                    document.getElementById('account-ebay').innerHTML = data.ebay;
                }else{
                    $('#change-email-field').keypress(function(event) {
                        if (event.keyCode === 13) {
                            var dd;
                            dd = document.getElementById('change-email-field').value;
                            updateEbay(2, dd);
                        }
                    });
                    $('#change-email-button').click(function() {
                        var dd;
                        dd = document.getElementById('change-email-field').value;
                        updateEbay(2, dd);
                    });
                }
            }
            document.getElementById('fullscreenload').style.display = 'none';
        },
        "json"
    )
    .fail(function() {
        $('#ajax-modal').removeClass('change-email');
        document.getElementById('modal_h1').innerHTML = 'Error';
        document.getElementById('modal_content').innerHTML = '<p>There was an error updating your account. Please refresh the page and try again.</p><p>(ref. ajax fail)<p>';
        document.getElementById('fullscreenload').style.display = 'none';
        if(s === 1){
            showModal('ajax-modal');
        }
    });
}














function changeInfoUnBindForms(){
    $('#change-info-button').unbind('click');
}
function changeInfoCleanup(){
    $('#ajax-modal').removeClass('change-info');
    $('#modal_close').unbind('click');
    changeInfoUnBindForms();
    document.getElementById('modal_h1').innerHTML = '';
    document.getElementById('modal_content').innerHTML = '';
}
function changeInfo(s, d1, d2){
    changeInfoUnBindForms();
    document.getElementById('fullscreenload').style.display = 'block';
    $('#modal_close').click(function() {
        changeInfoCleanup();
    });
    $.get(
        "/account/ajax/info/",
        { step:s, data1:d1, data2:d2},
        function( data ) {

            if(data.error === '0' || data.error === '1'){

                document.getElementById('modal_h1').innerHTML = data.h1;
                document.getElementById('modal_content').innerHTML = data.html;
                $('#ajax-modal').addClass('change-info');


                if(s === 1){
                    showModal('ajax-modal');
                }


                if(s === 1 || s === 2){
                    $('#change-info-button').click(function() {
                        var dd = {
                            "firstname" : document.getElementById('change-info-firstname').value,
                            "lastname" : document.getElementById('change-info-lastname').value,
                            "firmname" : document.getElementById('change-info-firmname').value,
                            "unit" : document.getElementById('change-info-unit').value,
                            "address" : document.getElementById('change-info-address').value,
                            "city" : document.getElementById('change-info-city').value,
                            "state" : document.getElementById('change-info-state').value,
                            "zip5" : document.getElementById('change-info-zip5').value,
                            "zip4" : document.getElementById('change-info-zip4').value
                        };
                        var ddd = {
                            "firstname" : document.getElementById('change-info-firstname').dataset.original,
                            "lastname" : document.getElementById('change-info-lastname').dataset.original,
                            "firmname" : document.getElementById('change-info-firmname').dataset.original,
                            "unit" : document.getElementById('change-info-unit').dataset.original,
                            "address" : document.getElementById('change-info-address').dataset.original,
                            "city" : document.getElementById('change-info-city').dataset.original,
                            "state" : document.getElementById('change-info-state').dataset.original,
                            "zip5" : document.getElementById('change-info-zip5').dataset.original,
                            "zip4" : document.getElementById('change-info-zip4').dataset.original
                        };
                        changeInfo(2, dd, ddd);
                    });
                }


                if(s === 2){
                    $('#change-info-use').click(function() {
                        var dd = {
                            "firstname" : document.getElementById('change-info-firstname').value,
                            "lastname" : document.getElementById('change-info-lastname').value,
                            "firmname" : document.getElementById('change-info-firmname').value,
                            "unit" : document.getElementById('change-info-unit').value,
                            "address" : document.getElementById('change-info-address').value,
                            "city" : document.getElementById('change-info-city').value,
                            "state" : document.getElementById('change-info-state').value,
                            "zip5" : document.getElementById('change-info-zip5').value,
                            "zip4" : document.getElementById('change-info-zip4').value
                        };
                        var ddd = {
                            "firstname" : document.getElementById('change-info-firstname').dataset.original,
                            "lastname" : document.getElementById('change-info-lastname').dataset.original,
                            "firmname" : document.getElementById('change-info-firmname').dataset.original,
                            "unit" : document.getElementById('change-info-unit').dataset.original,
                            "address" : document.getElementById('change-info-address').dataset.original,
                            "city" : document.getElementById('change-info-city').dataset.original,
                            "state" : document.getElementById('change-info-state').dataset.original,
                            "zip5" : document.getElementById('change-info-zip5').dataset.original,
                            "zip4" : document.getElementById('change-info-zip4').dataset.original
                        };
                        changeInfo(3, dd, ddd);
                    });

                    $('#change-info-save').click(function() {
                        var dd = {
                            "firstname" : document.getElementById('change-info-firstname').value,
                            "lastname" : document.getElementById('change-info-lastname').value,
                            "firmname" : document.getElementById('change-info-firmname').value,
                            "unit" : document.getElementById('change-info-unit').value,
                            "address" : document.getElementById('change-info-address').value,
                            "city" : document.getElementById('change-info-city').value,
                            "state" : document.getElementById('change-info-state').value,
                            "zip5" : document.getElementById('change-info-zip5').value,
                            "zip4" : document.getElementById('change-info-zip4').value
                        };
                        var ddd = {
                            "firstname" : document.getElementById('change-info-firstname').dataset.original,
                            "lastname" : document.getElementById('change-info-lastname').dataset.original,
                            "firmname" : document.getElementById('change-info-firmname').dataset.original,
                            "unit" : document.getElementById('change-info-unit').dataset.original,
                            "address" : document.getElementById('change-info-address').dataset.original,
                            "city" : document.getElementById('change-info-city').dataset.original,
                            "state" : document.getElementById('change-info-state').dataset.original,
                            "zip5" : document.getElementById('change-info-zip5').dataset.original,
                            "zip4" : document.getElementById('change-info-zip4').dataset.original
                        };
                        changeInfo(4, dd, ddd);
                    });

                    document.getElementById('account-name').innerHTML = data.return.firstname + ' ' + data.return.lastname;
                }

                if(s === 3 || s === 4){
                    document.getElementById('account-name').innerHTML = data.return.firstname + ' ' + data.return.lastname;
                    document.getElementById('account-address').innerHTML = data.return.fulladdress;
                }



                document.getElementById('fullscreenload').style.display = 'none';

            }else if(data.error === '2'){
                window.location=data.redir;
            }
        },
        "json"
    )
    .fail(function() {
        $('#ajax-modal').removeClass('change-info');
        document.getElementById('modal_h1').innerHTML = 'Error';
        document.getElementById('modal_content').innerHTML = '<p>There was an error updating your account. Please refresh the page and try again.</p><p>(ref. ajax fail)<p>';
        document.getElementById('fullscreenload').style.display = 'none';
        if(s === 1){
            showModal('ajax-modal');
        }
    });
}



function changeEmailSub(){
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/account/ajax/email/subscription/",
        {a:1},
        function( data ) {
            if(data.error === '0'){

            }else{

            }
            if( data.check === 'yes' ){
                document.getElementById('email-sub-checkbox').check = true;
            }
            if( data.check === 'no' ){
                document.getElementById('email-sub-checkbox').check = false;
            }
            document.getElementById('fullscreenload').style.display = 'none';
        },
        "json"
    )
    .fail(function() {
        alert('ajax fail');
        document.getElementById('fullscreenload').style.display = 'none';
    });
}

function logoutDevice(lid){
    document.getElementById('fullscreenload').style.display = 'block';
    $.post(
        "/account/ajax/logout/device/",
        {login:lid},
        function( data ) {
            if(data.error === '0'){
                document.getElementById('login-list').innerHTML = data.list_html;
                document.getElementById('fullscreenload').style.display = 'none';
            }else{
                // need to add error handling
                alert('error');
                document.getElementById('fullscreenload').style.display = 'none';
            }
        },
        "json"
    )
    .fail(function() {
        alert('ajax fail');
        document.getElementById('fullscreenload').style.display = 'none';
    });
}
