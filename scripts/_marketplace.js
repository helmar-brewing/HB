function userToUserError(modal_h1, modal_content){
    document.getElementById('modal_h1').innerHTML = modal_h1;
    document.getElementById('modal_content').innerHTML = modal_content;
    hideModal('user-to-user');
    showModal('ajax-modal');
    hideFullScreenLoad();
}


function clearUserToUserForm(){
    document.getElementById('email').value='';
    document.getElementById('name').value='';
    document.getElementById('subject').value='';
    document.getElementById('message_body').value='';
    document.getElementById('disclaimer').checked = false;
    document.getElementById('send').disabled = true;
}


function userToUserCancel(){
    hideModal('user-to-user');
    clearUserToUserForm();
}


function userToUserSend(){
    hideModal('user-to-user');
    clearUserToUserForm();
}


function userToUserAcceptDisclaimer(){
    if(document.getElementById('disclaimer').checked){
        document.getElementById('send').disabled = false;
        $('#send').on('click', function(){
            userToUserSend();
        });
    }else{
        document.getElementById('send').disabled = true;
        $('send').off();
    }
}


function userToUser(send_to_user_id, email_to_wanter_instead){
    var subject;
    var emailToOwnerSubject = 'I\'m interested in a card you have on the Helmar Brewing Marketplace!';
    var emailToWanterSubject = 'I have a card you are interested in on Helmar Brewing Marketplace!';
    if(email_to_wanter_instead === undefined){ email_to_wanter_instead = false; }
    if(email_to_wanter_instead){
        subject = emailToWanterSubject;
    }else{
        subject = emailToOwnerSubject;
    }
    showFullScreenLoad();
    $.get(
        "ajax/user_to_user/",
        {
            send_to_user_id: send_to_user_id
        },
        function(data) {
            document.getElementById('name').value = data.from_name;
            document.getElementById('email').value = data.from_email;
            document.getElementById('to').value = data.to_line;

            document.getElementById('subject').value = subject;
            showModal('user-to-user');
            hideFullScreenLoad();
        },
        "json"
    ).fail(function(response) {
        var h1 = 'Error';
        var content = '<p>There was an error displaying the contact form.<br>[ref: '+response.status+']</p>';
        userToUserError(h1, content);
    });
}
