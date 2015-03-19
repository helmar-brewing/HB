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
                $('#auction_button').unbind();
                $('#auction_button').click(function() {
                    auctions(data.nextpage);
                });
            }else{
                alert('There was an error. ref: ebay error');
            }
            document.getElementById('fullscreenload').style.display = 'none';
        },
        "json"
    )
    .fail(function() {
        alert('There was an error. ref: ajax fail');
    });
}
