var stripeResponseHandlerModal2 = function(status, response) {
	var $form = $('#payment-form-modal');

	if (response.error) {
		// Show the errors on the form
		$form.find('.payment-errors').text(response.error.message);
		$form.find('button').prop('disabled', false);
		document.getElementById('fullscreenload').style.display = 'none';
	} else {
		var token = response.id;
		// AJAX send token to server for processing
		$.post(
			"/subscription/ajax/updatecard/",
			{ t:token },
			function( data ) {
				if(data.error === '0'){
					$form.find('.payment-errors').text(data.msg);

					document.getElementById('card_number').value = '\xB7\xB7\xB7\xB7 \xB7\xB7\xB7\xB7 \xB7\xB7\xB7\xB7 ' + data.last4;
					document.getElementById('exp_month').value = data.exp_month;
					document.getElementById('exp_year').value = data.exp_year;
					document.getElementById('cvc').value = '';

					document.getElementById('modal-add-card').style.display = 'none';
					document.getElementById('modal-add-card-success').style.display = 'block';

					var a = $('#modal-add-card-button').attr("data-action");
					$('#modal-add-card-button').on("click", function(){subUpdate2(a);} );


					document.getElementById('fullscreenload').style.display = 'none';
				}else if(data.error === '1'){
					$form.find('.payment-errors').text(data.msg);
					document.getElementById('fullscreenload').style.display = 'none';
				}else if(data.error === '2'){
					$form.find('.payment-errors').text(data.msg);
					document.getElementById('fullscreenload').style.display = 'none';
				}else if(data.error === '3'){
					$form.find('.payment-errors').text(data.json.error.type + '-' + data.json.error.message + '-' + data.json.error.param);
					document.getElementById('fullscreenload').style.display = 'none';
				}else{
					alert('There was an error, but no error type was returned.');
				}
			},
			"json"
		)
		.fail(function() {
			alert('ajax failure');
			document.getElementById('fullscreenload').style.display = 'none';
		});

		$form.find('button').prop('disabled', false);
	}
};






function subUpdate2(a){
    subUnbind();
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/subscription/ajax/changesub/",
        {
            action: a
        },
        function( data ) {
            document.getElementById('modal_h1').innerHTML = data.h1;
            document.getElementById('modal_content').innerHTML = data.content;
            if(data.error === '2'){
                window.location.href = "/account/login/?redir=subscription/";
			}else if(data.error === '3'){

				$('#payment-form-modal').submit(function(e) {
					document.getElementById('fullscreenload').style.display = 'block';
					var $form = $(this);

					// Disable the submit button to prevent repeated clicks
					$form.find('button').prop('disabled', true);
					Stripe.card.createToken($form, stripeResponseHandlerModal2);
					// Prevent the form from submitting with the default action
					return false;
				});

				document.getElementById('fullscreenload').style.display = 'none';

			}else if(data.error === '4'){

				// do the address update is successful show the hidden continue button

				document.getElementById('fullscreenload').style.display = 'none';

            }else if(data.error === '0'){

				//refresh page
				window.location.href = "/subscription/";

            }else{
                document.getElementById('fullscreenload').style.display = 'none';
            }
        },
        "json"
    )
    .fail(function() {
        $('#ajax-modal').removeClass('sub_modal');
        document.getElementById('modal_h1').innerHTML = 'Error';
        document.getElementById('modal_content').innerHTML = '<p>There was an error changing your subscription. Please try again.</p><p>(ref. ajax fail)<p>';
        document.getElementById('fullscreenload').style.display = 'none';
    });
}
function subPreview2(a){
    subUnbind();
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/subscription/ajax/previewsub/",
        {
            action: a
        },
        function( data ) {
            document.getElementById('modal_h1').innerHTML = data.h1;
            document.getElementById('modal_content').innerHTML = data.content;
            if(data.error === '2'){
                window.location.href = "/account/login/?redir=subscription/";
            }else if(data.error === '0'){
                document.getElementById('fullscreenload').style.display = 'none';
            }else{
                document.getElementById('fullscreenload').style.display = 'none';
            }
        },
        "json"
    )
    .fail(function() {
        $('#ajax-modal').removeClass('sub_modal');
        document.getElementById('modal_h1').innerHTML = 'Error';
        document.getElementById('modal_content').innerHTML = '<p>There was an error changing your subscription. Please try again.</p><p>(ref. ajax fail)<p>';
        document.getElementById('fullscreenload').style.display = 'none';
    });
}
function sub2(a){
    document.getElementById('fullscreenload').style.display = 'block';
    $('#modal_close').click(function() {
        subCleanup();
    });
    $('#ajax-modal').addClass('sub_modal');
    showModal('ajax-modal');
	subPreview2(a);
}





function address2(){
	document.getElementById('fullscreenload').style.display = 'block';
	var aa = document.getElementById('sub-address').value;
	var cc = document.getElementById('sub-city').value;
	var ss = document.getElementById('sub-state').value;
	var z5 = document.getElementById('sub-zip5').value;
	var z4 = document.getElementById('sub-zip4').value;
    $.get(
        "/subscription/ajax/address/",
        {
			address: aa,
			city: cc,
			state: ss,
			zip5: z5,
			zip4: z4
		},
        function( data ) {
            if(data.error === '2'){
                window.location.href = "/account/login/?redir=subscription/";
            }else if(data.error === '0'){
				document.getElementById('modal-address-form').style.display = 'none';
				document.getElementById('modal-add-card-success').style.display = 'block';
				var a = $('#modal-add-card-button').attr("data-action");
				$('#modal-add-card-button').on("click", function(){subUpdate2(a);});
				document.getElementById('fullscreenload').style.display = 'none';
            }else{
				document.getElementById('modal_h1').innerHTML = data.h1;
	            document.getElementById('modal_content').innerHTML = data.content;
                document.getElementById('fullscreenload').style.display = 'none';
            }
        },
        "json"
    )
    .fail(function() {
        $('#ajax-modal').removeClass('sub_modal');
        document.getElementById('modal_h1').innerHTML = 'Error';
        document.getElementById('modal_content').innerHTML = '<p>There was an error updating your address. Please try again.</p><p>(ref. ajax fail)<p>';
        document.getElementById('fullscreenload').style.display = 'none';
    });
}
