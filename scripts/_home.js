function auctions(p){
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/ajax/auctions/",
        { pagenum:p },
        function( data ) {
            if(data.error === 0){
                // add next row
                $('#auction_list').append(data.content);
                //change button
                if(data.nextpage === false){
                    $('#auction_button').unbind();
                    document.getElementById('auction_button').innerHTML = 'All Actions Loaded';
                }else{
                    $('#auction_button').unbind();
                    $('#auction_button').click(function() {
                        auctions(data.nextpage);
                    });
                }
            }else{
                alert('There was an error. ref: ebay error 2');
            }
            document.getElementById('fullscreenload').style.display = 'none';
        },
        "json"
    )
    .fail(function() {
        alert('There was an error. ref: ajax fail');
    });
}
