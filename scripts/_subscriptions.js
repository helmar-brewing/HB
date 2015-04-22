var stripeResponseHandler = function(status, response) {
	var $form = $('#payment-form');
	if (response.error) {
		// Show the errors on the form
		$form.find('.payment-errors').text(response.error.message);
		$form.find('button').prop('disabled', false);
		document.getElementById('fullscreenload').style.display = 'none';
	} else {
		var token = response.id;
		// AJAX send token to server for processing
		$.post(
			"/account/ajax/updatecard/",
			{ t:token },
			function( data ) {
				if(data.error === '0'){
					$form.find('.payment-errors').text(data.msg);

					document.getElementById('card_number').value = '\xB7\xB7\xB7\xB7 \xB7\xB7\xB7\xB7 \xB7\xB7\xB7\xB7 ' + data.last4;
					document.getElementById('exp_month').value = data.exp_month;
					document.getElementById('exp_year').value = data.exp_year;
					document.getElementById('cvc').value = '';

					document.getElementById('delete_card').disabled = false;
					document.getElementById('add_update_card').innerHTML = 'Update Card';

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
jQuery(function($) {
	$('#payment-form').submit(function(e) {
		document.getElementById('fullscreenload').style.display = 'block';
		var $form = $(this);
		// Disable the submit button to prevent repeated clicks
		$form.find('button').prop('disabled', true);
		Stripe.card.createToken($form, stripeResponseHandler);
		// Prevent the form from submitting with the default action
		return false;
	});
});







function deleteCard(){
	document.getElementById('fullscreenload').style.display = 'block';
	$.post(
		"/account/ajax/deletecard/",
		{ a:1 },
		function( data ) {
			if(data.error === '0'){

				document.getElementById('card_number').value = '';
				document.getElementById('exp_month').value = '';
				document.getElementById('exp_year').value = '';
				document.getElementById('cvc').value = '';
				document.getElementById('delete_card').disabled = true;
				document.getElementById('add_update_card').innerHTML = 'Add Card';

				document.getElementById('payment-errors').innerHTML = data.msg;

				document.getElementById('fullscreenload').style.display = 'none';
			}else{
				document.getElementById('payment-errors').innerHTML = data.msg;
				document.getElementById('fullscreenload').style.display = 'none';
			}
		},
		"json"
	)
	.fail(function() {
		alert('ajax failure');
		document.getElementById('fullscreenload').style.display = 'none';
	});
}

function toggleSub(){
	document.getElementById('fullscreenload').style.display = 'block';
	$.post(
		"/account/ajax/togglesub/",
		{ a:1 },
		function( data ) {
			if(data.error === '0'){
				document.getElementById('sub_status').innerHTML = data.status;
				document.getElementById('sub_button').innerHTML = data.button;
				document.getElementById('sub_error').innerHTML = data.msg;
				document.getElementById('fullscreenload').style.display = 'none';
			}else{
				document.getElementById('sub_error').innerHTML = data.msg;
				document.getElementById('fullscreenload').style.display = 'none';
			}
		},
		"json"
	)
	.fail(function() {
		alert('ajax failure');
		document.getElementById('fullscreenload').style.display = 'none';
	});
}
