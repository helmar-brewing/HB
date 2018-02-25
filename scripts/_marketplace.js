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


function userToUser(){
    var defaultSubject = 'I\'m interested in a card you have on the Helmar Brewing Marketplace!';
    showFullScreenLoad();
    $.get(
        "ajax/user_to_user/",
        null,
        function( data ) {
            document.getElementById('name').value = data.name;
            document.getElementById('email').value = data.email;
            document.getElementById('subject').value = defaultSubject;
            showModal('user-to-user');
            hideFullScreenLoad();
        },
        "json"
    ).fail(function() {
        var h1 = 'Error';
        var content = '<p>There was an error displaying the contact form.<br>[ref: ajax fail]</p>';
        userToUserError(h1, content);

    });
}
