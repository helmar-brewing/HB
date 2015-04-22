// VERSION 1.0.4


function showModal(div, blackoutcolor) {
    "use strict";
    if(blackoutcolor !== null){
		document.getElementById('blackout').style.backgroundColor = blackoutcolor;
    }
    document.getElementById('blackout').style.display = 'block';
    document.getElementById(div).style.display = 'block';
    $('body').addClass('stop-scroll');
    document.openModal = div;
}
function hideModal(div) {
    "use strict";
    document.getElementById('blackout').style.display = 'none';
    document.getElementById('blackout').style.backgroundColor = 'black';
	document.getElementById(div).style.display = 'none';
	document.getElementById('modal_content').innerHTML = '';
    document.getElementById('modal_h1').innerHTML = '';
    $('body').removeClass('stop-scroll');
    document.openModal = '';
}
