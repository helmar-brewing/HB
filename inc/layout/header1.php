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
                <li class="nav-link"><a href="'.$protocol.$site.'/artwork/">Artwork</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/about/">Helmar &amp; Charles</a></li>
                <li class="nav-link"><a href="'.$protocol.$site.'/contact/">Stay In Touch</a></li>
                <li class="nav-link"><a href="http://helmarblog.com/">Blog</a></li>
                <li class="nav-link"><a target="_blank" href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/">Store</a></li>
';
if(isset($user)){
    if( $user->login() == 1 || $user->login() == 2 ){
        print'
                <li class="nav-link"><a href="'.$protocol.$site.'/account/logout">Log out</a></li>
        ';
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
