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
        var u = "/account/ajax/password/"
        var e = document.getElementById('recover_pword_email').value;
    }
    if(a === 'uname'){
        var u = "/account/ajax/username/"
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
function changeInfo(s, d1){
    changeInfoUnBindForms();
    document.getElementById('fullscreenload').style.display = 'block';
    $('#modal_close').click(function() {
        changeInfoCleanup();
    });
    $.get(
        "/account/ajax/info/",
        { step:s,  data1:d1},
        function( data ) {
            document.getElementById('modal_h1').innerHTML = data.h1;
            document.getElementById('modal_content').innerHTML = data.content;
            $('#ajax-modal').addClass('change-info');
            if(s === 1){
                if(data.error === '0'){
                    $('#change-info-button').click(function() {
                        var dd = {
                            "firstname" : document.getElementById('change-info-firstname').value,
                            "lastname" : document.getElementById('change-info-lastname').value,
                            "year1" : document.getElementById('change-info-year1').value,
                            "year2" : document.getElementById('change-info-year2').value,
                            "dept" : document.getElementById('change-info-dept').value,
                            "agency" : document.getElementById('change-info-agency').value,
                            "phone" : document.getElementById('change-info-phone').value
                        };
                        changeInfo(2, dd);
                    });
                }
                showModal('ajax-modal');
            }else if(s === 2){
                if(data.error === '0'){
                    document.getElementById('profile-firstname').innerHTML = data.return.firstname;
                    document.getElementById('profile-lastname').innerHTML = data.return.lastname;
                    document.getElementById('profile-year1').innerHTML = data.return.year1;
                    document.getElementById('profile-year2').innerHTML = data.return.year2;
                    document.getElementById('profile-dept').innerHTML = data.return.dept;
                    document.getElementById('profile-agency').innerHTML = data.return.agency;
                    document.getElementById('profile-phone').innerHTML = data.return.phone;
                    changeInfoCleanup();
                    hideModal('ajax-modal');

                }else{
                    $('#change-info-button').click(function() {
                        var dd = {
                            "firstname" : document.getElementById('change-info-firstname').value,
                            "lastname" : document.getElementById('change-info-lastname').value,
                            "year1" : document.getElementById('change-info-year1').value,
                            "year2" : document.getElementById('change-info-year2').value,
                            "dept" : document.getElementById('change-info-dept').value,
                            "agency" : document.getElementById('change-info-agency').value,
                            "phone" : document.getElementById('change-info-phone').value
                        };
                        changeInfo(2, dd);
                    });
                }
            }
            document.getElementById('fullscreenload').style.display = 'none';
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
