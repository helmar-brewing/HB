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

				address2(1);

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





function address2(s, d1, d2){
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/account/ajax/address/",
        { step:s, data1:d1, data2:d2},
        function( data ) {

            if(data.error === '0' || data.error === '1'){


                document.getElementById('modal-address-form').innerHTML = data.html;



                if(s === 1 || s === 2){
                    $('#change-info-button').click(function() {
                        var dd = {
                            "firmname" : document.getElementById('change-info-firmname').value,
                            "unit" : document.getElementById('change-info-unit').value,
                            "address" : document.getElementById('change-info-address').value,
                            "city" : document.getElementById('change-info-city').value,
                            "state" : document.getElementById('change-info-state').value,
                            "zip5" : document.getElementById('change-info-zip5').value,
                            "zip4" : document.getElementById('change-info-zip4').value
                        };
                        var ddd = {
                            "firmname" : document.getElementById('change-info-firmname').dataset.original,
                            "unit" : document.getElementById('change-info-unit').dataset.original,
                            "address" : document.getElementById('change-info-address').dataset.original,
                            "city" : document.getElementById('change-info-city').dataset.original,
                            "state" : document.getElementById('change-info-state').dataset.original,
                            "zip5" : document.getElementById('change-info-zip5').dataset.original,
                            "zip4" : document.getElementById('change-info-zip4').dataset.original
                        };
                        address2(2, dd, ddd);
                    });
                }


                if(s === 2){
                    $('#change-info-use').click(function() {
                        var dd = {
                            "firmname" : document.getElementById('change-info-firmname').value,
                            "unit" : document.getElementById('change-info-unit').value,
                            "address" : document.getElementById('change-info-address').value,
                            "city" : document.getElementById('change-info-city').value,
                            "state" : document.getElementById('change-info-state').value,
                            "zip5" : document.getElementById('change-info-zip5').value,
                            "zip4" : document.getElementById('change-info-zip4').value
                        };
                        var ddd = {
                            "firmname" : document.getElementById('change-info-firmname').dataset.original,
                            "unit" : document.getElementById('change-info-unit').dataset.original,
                            "address" : document.getElementById('change-info-address').dataset.original,
                            "city" : document.getElementById('change-info-city').dataset.original,
                            "state" : document.getElementById('change-info-state').dataset.original,
                            "zip5" : document.getElementById('change-info-zip5').dataset.original,
                            "zip4" : document.getElementById('change-info-zip4').dataset.original
                        };
                        address2(3, dd, ddd);
                    });

                    $('#change-info-save').click(function() {
                        var dd = {
                            "firmname" : document.getElementById('change-info-firmname').value,
                            "unit" : document.getElementById('change-info-unit').value,
                            "address" : document.getElementById('change-info-address').value,
                            "city" : document.getElementById('change-info-city').value,
                            "state" : document.getElementById('change-info-state').value,
                            "zip5" : document.getElementById('change-info-zip5').value,
                            "zip4" : document.getElementById('change-info-zip4').value
                        };
                        var ddd = {
                            "firmname" : document.getElementById('change-info-firmname').dataset.original,
                            "unit" : document.getElementById('change-info-unit').dataset.original,
                            "address" : document.getElementById('change-info-address').dataset.original,
                            "city" : document.getElementById('change-info-city').dataset.original,
                            "state" : document.getElementById('change-info-state').dataset.original,
                            "zip5" : document.getElementById('change-info-zip5').dataset.original,
                            "zip4" : document.getElementById('change-info-zip4').dataset.original
                        };
                        address2(4, dd, ddd);
                    });

                    $('#ajax-modal').animate({ scrollTop: 0 }, 'slow');
                }

                if(s === 3 || s === 4){
					document.getElementById('modal-add-card-success').style.display = 'block';
					var a = $('#modal-add-card-button').attr("data-action");
					$('#modal-add-card-button').on("click", function(){subUpdate2(a);});
					document.getElementById('fullscreenload').style.display = 'none';
                }



                document.getElementById('fullscreenload').style.display = 'none';

            }else if(data.error === '2'){
                window.location=data.redir;
            }
        },
        "json"
    )
    .fail(function() {
        $('#ajax-modal').removeClass('sub_modal');
        document.getElementById('modal_h1').innerHTML = 'Error';
        document.getElementById('modal_content').innerHTML = '<p>There was an error updating your address.</p><p>(ref. ajax fail)<p>';
        document.getElementById('fullscreenload').style.display = 'none';
    });
}
