function contactus(){
    $('body').addClass('stop-scroll');
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/contact/ajax/contact/",
        { name: document.getElementById('contact_name').value, email: document.getElementById('contact_email').value, comment: document.getElementById('contact_comment').value },
        function( data ) {
            if(data.error === '0'){
                document.getElementById('contact_name').value = '';
                document.getElementById('contact_email').value = '';
                document.getElementById('contact_comment').value = '';
                $('body').removeClass('stop-scroll');
                document.getElementById('fullscreenload').style.display = 'none';
                alert(data.msg);
            }else{
                alert(data.msg);
            }
        },
        "json"
    );
}
