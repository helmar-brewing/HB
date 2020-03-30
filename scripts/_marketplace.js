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
    document.getElementById('disclaimer').checked = false;
    document.getElementById('send').disabled = true;
    CKEDITOR.instances['message_body'].setData('');
}


function userToUserCancel(){
    hideModal('user-to-user');
    clearUserToUserForm();
}


function userToUserAcceptDisclaimer(){
    if(document.getElementById('disclaimer').checked){
        document.getElementById('send').disabled = false;
        $('#send').on('click', function(){
            var send_to_user_id;
            send_to_user_id = this.getAttribute('data-send-to-user-id');
            userToUserSend(send_to_user_id);
        });
    }else{
        document.getElementById('send').disabled = true;
        $('#send').off();
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
            'send_to_user_id' : send_to_user_id
        },
        function(data) {
            document.getElementById('send').setAttribute('data-send-to-user-id', send_to_user_id);
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

function userToUserSend(send_to_user_id){
    var subject;
    var name;
    var body;
    $('.inline_error').hide();
    showFullScreenLoad();
    subject = document.getElementById('subject').value;
    name = document.getElementById('name').value;
    body = CKEDITOR.instances['message_body'].getData();
    $.post(
        'ajax/user_to_user/',
        {
            'send_to_user_id' : send_to_user_id,
            'name' : name,
            'subject' : subject,
            'body' : body

        },
        function(data){
            hideModal('user-to-user');
            hideFullScreenLoad();
            clearUserToUserForm();
        },
        'json'
    ).fail(function(response) {
        if(response.responseJSON.error_msg === 'no message body'){
            hideFullScreenLoad();
            $('#error_no_message_body').show();
        }else{
            console.log(response);
            var h1 = 'Error';
            var content = '<p>There was an error sending the message.<br>[ref: '+response.status+' : '+response.responseJSON.error_msg+']</p>';
            userToUserError(h1, content);
        }
    });
}

function cleanString(str) {
    return str.trim().replace(/\w\S*/g, function(txt){
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

let searchType = "selling";

function getUserFunction(card) {
    let fn = "userToUser(" + card.userid;
    if (searchType === "buying") {
        fn += ", true";
    } else {
        fn += ", false";
    }

    fn += ")";

    return fn;
}

function printTableRow(card) {
    let fn = getUserFunction(card);
    let greetings = !card.firstname || card.firstname === "" ? "User" : cleanString(card.firstname);
    greetings = card.state  === "" || !card.state ? greetings : greetings + " from " + card.state.toUpperCase();

    let html = "<tr>";
    html += "<td onclick='" + fn + "' class='email_btn' style='text-align: center'><i class='fa fa-envelope-o'></i></td>";
    html += "<td>" + greetings + "</td>";
    html += "<td>" + card.series_name + "</td>";
    html += "<td>" + card.cardnum + "</td>";
    html += "<td>" + cleanString(card.properName) + "</td>";
    html += "<td>" + cleanString(card.description) + "</td>";
    html += "<td>" + cleanString(card.team) + "</td>";
    html += "<td><div style='display: inline-flex'>";

    if (card.frontPicture) {
        html += "<a href='" + card.frontPicture + "' data-lightbox='" + card.series + "_" + card.cardnum + "'><img class='thumbnail' src='" + card.frontPicture + "'/></a>";
    }

    if (card.backPicture) {
        html += "<a href='" + card.backPicture + "' data-lightbox='" + card.series + "_" + card.cardnum + "'><img class='thumbnail' src='" + card.backPicture + "'/></a>";
    }

    html += "</div></td>";
    html += "<td>" + card.card_note + "</td>";

    return html;
}

function printGridItem(card) {
    let html = "<li class='grid_item' style='width: 25%; margin: 0; padding: 1em;'>";

    let fn = getUserFunction(card);

    html += '<a style="background:url(' + "'" + card.frontPicture + "'" + '); background-size: cover; background-position: center;background-repeat: repeat;" href="' + card.frontPicture + '" data-lightbox="' + card.series + "_" + card.cardnum + '" >';
    html += "<span>";
    html += '<figure style="background:url(' + "'" + card.frontPicture + "'" + '); background-size: contain; background-position: center;background-repeat: no-repeat;"></figure>';
    html += "</span>";
    html += "</a>";

    let greetings = !card.firstname || card.firstname === "" ? "User" : cleanString(card.firstname);
    greetings = card.state  === "" || !card.state ? greetings : greetings + " from " + card.state.toUpperCase();

    html += "<p class='nameplate item-wanted' onclick='" + fn + "'>";
    html += "<i class='fa fa-envelope-o' onclick='" + fn + "'></i> " + greetings + "<br>";
    html += "</p>";

    if (card.card_note !== "") {
        html += "<p class='nameplate' style='background-color: lightblue'><i>" + card.card_note + "</i></p>";
    }

    html += '<p class="nameplate card-info" onclick="getCardInfo(' + card.cardnum + ", '" + card.series + "'" + ')">"';
    html += '<i class="fa fa-info" onclick="getCardInfo(' + card.cardnum + ', "' + card.series + '"' + ')"></i> Click for Card Info<br>';
    html += "</p>";
    html += "</li>";

    if (card.backPicture) {
        html += "<a href='" + card.backPicture + "' data-lightbox='" + card.series + "_" + card.cardnum + "'></a>";
    }

    return html;
}

let cards = [];

let pageSize = 16;
let page = 0;
let totalResults = 0;
let maxPaginationButtons = 6;

function changePageSize() {
    pageSize = $('#results-per-page').val();
    setPagination();
    refreshCards(false);
}

function gotoPage(p) {
    page = p;
    setPagination();
    refreshCards(false);
}

function paginationButton(page, active) {
    html =  "<a onclick='gotoPage(" + page + ")' ";
    if (active) {
        html += "class='active'";
    }
    html += ">" + (page + 1) + "</a>";
    return html;
}

function setPagination() {
    let pages = Math.ceil(totalResults / pageSize);

    let html = "";
    if (pages > 1 && pageSize !== 'All') {

        let lowerBound = page - (maxPaginationButtons - 1) / 2;
        let upperBound = page + (maxPaginationButtons - 1) / 2;

        console.log(lowerBound, upperBound);

        if (lowerBound < 0) {
            upperBound -= lowerBound;
            lowerBound = 0;
        }

        if (upperBound > pages - 1) {
            lowerBound -= upperBound - pages;
            upperBound = pages;
        }

        lowerBound = lowerBound < 0 ? 0 : lowerBound;
        upperBound = upperBound >= pages ? pages : upperBound;

        console.log(lowerBound, upperBound);

        if (page > 1) {
            html += "<a onclick='gotoPage(0)'>&laquo;</a>";
        }

        if (page > 0) {
            html += "<a onclick='gotoPage(" + (page - 1) + ")'>&lsaquo;</a>";
        }

        for (let i = lowerBound; i < upperBound; i++) {
            html += paginationButton(i, page === i);
        }

        if (page < pages - 1) {
            html += "<a onclick='gotoPage(" + (page + 1) + ")'>&rsaquo;</a>";
        }

        if (page < pages - 2) {
            html += "<a onclick='gotoPage(" + (pages - 1) + ")'>&raquo;</a>";
        }
        console.log(html);
    }
    $('#pagination-container').html(html);
}

// Debounce search results so server isn't spammed.
let refreshTimeout = null;
let lastRefreshRequest = null;
let refreshDelay = 500;

function setType(type) {
    searchType = type;
    let msg = $('#type_message');

    if (type === 'selling') {
        msg.html("The following users are interested in selling the cards below. Click on the items if you would like to trade or reach out to that user!");
        $('#selling_btn').removeClass("inactive");
        $('#buying_btn').addClass("inactive");
        $('#type_label').html("For Sale");
    } else {
        msg.html("The following users are interested in buying the cards below. Click on the items if you would like to trade or reach out to that user!");
        $('#selling_btn').addClass("inactive");
        $('#buying_btn').removeClass("inactive");
        $('#type_label').html("Wanted");
    }

    refreshCards();
}

let viewType = "";

function setViewType(type) {
    viewType = type;

    if (viewType === "card") {
        $('#view_type_btn').html("Table View");
    } else {
        $('#view_type_btn').html("Card View");
    }

    renderCards();
}

function toggleViewType(e) {
    e.preventDefault();
    setViewType(viewType === "card" ? "table" : "card");
}

function renderCards() {
    let html = "";
    let currentSeries = null;

    console.log(viewType);

    let auctionList = $('#auction_list');
    let auctionListTable = $('#auction_list_table');
    let tableBody = $('#auction_list_table_body');

    auctionList.hide();
    auctionListTable.hide();

    if (cards.length === 0) {
        $('#no_results').show();
    }

    if (viewType === "card") {
        for (const card of cards) {
            if (card.series_name !== currentSeries) {
                currentSeries = card.series_name;

                html += "<h1 style='width: 100%; background-color: gray; color: white;'>" + card.series_name + "</h1>";
            }
            html += printGridItem(card);
        }

        auctionList.html(html);
        auctionList.show();
    } else {

        for (const card of cards) {
            html += printTableRow(card);
        }

        tableBody.html(html);
        auctionListTable.show();
    }
}

function refreshCards(delay = true) {

    if (refreshTimeout !== null && (new Date()).getTime() - lastRefreshRequest >= refreshDelay) {
        console.log("Clearing timeout");
        clearTimeout(refreshTimeout);
    }

    refreshTimeout = setTimeout(() => {

        $('#no_results').hide();

        const auctionListLoading = $('#auction_list_loading');
        const auctionListContainer = $('#auction_list_container');
        const seriesFilter = $('#series_filter').val();

        auctionListContainer.hide();
        auctionListLoading.show();

        const search = document.getElementById("search-query").value;
        let searchParameters = {
            q: search,
            type: searchType,
            filters: {
                "cardList.series":  seriesFilter === '' ? null : seriesFilter,
            },
        };

        if (pageSize !== 'All') {
            searchParameters["limit"] = pageSize;
            searchParameters["offset"] = pageSize * page;
        }

        $.post("ajax/card_info/index.php", {
            action: "search-meta",
            ...searchParameters
        }, function(data) {
            totalResults = data.meta.card_count;
            const pages = pageSize === 'All' ? 1 : Math.ceil(totalResults / pageSize);
            $('#total_size').html(pages);

            if (page >= pages) {
                page = 0;
                if (totalResults > 0) {
                    refreshCards();
                }
            }
            setPagination();
        });

        $.post("ajax/card_info/index.php", {
            action: "search",
            ...searchParameters
        }, function(data) {
            $('#result_size').html(page + 1);

            cards = data.cards;

            renderCards();

            auctionListContainer.show();
            auctionListLoading.hide();
        })
    }, delay ? refreshDelay : 1);
}

function getSeriesNames() {
    $.get("ajax/card_info", {
        action: "series"
    }, function(data) {
        if (data.success) {
            let html = "<option value=''>All Series</option>";

            for (const series of data.series) {
                html += "<option value='" + series.series_tag + "'>" + series.series_name + "</option>";
            }

            $('#series_filter').html(html);
        }
    })
}

function formatDate(isoDate) {
    if (isoDate === "0000-00-00") {
        return "N/A";
    }
    date = new Date(isoDate);
    return (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
}

function getCardInfo(cardNum, series){
    showFullScreenLoad();
    $.get(
        "ajax/card_info/",
        {
            action: 'single',
            cardNum,
            series,
        },
        function(data) {
            console.log(data);
            if (data.success) {
                document.getElementById("series_name").value = data.cardInfo.series_name;
                document.getElementById("card_number").value = data.cardInfo.cardnum;
                document.getElementById('player_name').value = data.cardInfo.properName.trim();
                document.getElementById('player_team').value = data.cardInfo.team;
                document.getElementById('player_position').value = data.cardInfo.description;
                document.getElementById('last_sold_date').value = formatDate(data.cardInfo.lastsold);
                document.getElementById('max_ebay_price').value = "$" + data.cardInfo.maxSold;
                showModal('market-card-info');
                hideFullScreenLoad();
            } else {
                var h1 = 'Error';
                var content = '<p>Card Info Not Found</p>';
                userToUserError(h1, content);
            }
        },
        "json"
    ).fail(function(response) {
        var h1 = 'Error';
        var content = '<p>There was an error displaying the contact form.<br>[ref: '+response.status+']</p>';
        userToUserError(h1, content);
    });
}


function exitCardInfo(){
    hideModal('market-card-info');
}
