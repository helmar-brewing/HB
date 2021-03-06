<?php

/* THIS HEADER GOES AFTER THE HERO UNTI ON PAGES THAT HAVE ONE */

print'
    <header class="centered-navigation">
        <div class="centered-navigation-wrapper">
            <a href="'.$protocol.$site.'/" class="mobile-logo">
                <img src="/img/h.png" alt="H logo">
            </a>
            <a href="" class="centered-navigation-menu-button">MENU</a>
            <ul class="centered-navigation-menu">
                <li class="nav-link logo">
                  <a href="'.$protocol.$site.'/" class="logo">
                    <img src="/img/h.png" alt="H logo">
                  </a>
                </li>
                <li class="nav-link"><a href="'.$protocol.$site.'/artwork/">Helmar Card Art</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/marketplace/">Marketplace</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/about/">Helmar &amp; Charles</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/contact/">Stay In Touch</a></li>
                <li class="nav-link"><a href="http://helmarblog.com/" target="_blank">Blog</a></li>
                <li class="nav-link"><a href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/" target="_blank">eBay Store</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/magazine/">Magazine</a></li>
';

		// grab userType
        $R_cards2 = $db_main->query("
        SELECT userType
        FROM users
        WHERE userid ='".$user->id."'
            "
        );
        $R_cards2->data_seek(0);
        while($card = $R_cards2->fetch_object()){
            $userType = $card->userType;
        }
        $R_cards2->free();

if(isset($user)){
    if( $user->login() == 1 || $user->login() == 2 ){
        print'
                <li class="nav-link"><a href="'.$protocol.$site.'/checklist/">My Checklist</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/account/logout">Log out</a></li>
        ';
            if ($userType === 'admin') {
                print'
                <li class="nav-link"><a href="'.$protocol.$site.'/reports/">Reports</a></li>
                ';
            }
    }else{
        print'
                <li class="nav-link"><a href="'.$protocol.$site.'/account/login?redir='.$currentpage.'">Log in</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/account/register">Register</a></li>
        ';
    }
}else{
    print'
                <li class="nav-link"><a href="'.$protocol.$site.'/account/login?redir='.$currentpage.'">Log in</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/account/register">Register</a></li>
    ';
}
print'
            </ul>
        </div>
    </header>
';

?>
