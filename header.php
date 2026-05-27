<?php
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="profile" href="https://gmpg.org/xfn/11">


    <?php
        $favicon = get_field('option_logo_favicon', 'option');
        $faviconArray = get_custom_thumb($favicon, 'full');
    ?>
    <!-- Copy & Paste Real Favicon Geenerator code : http://realfavicongenerator.net -->
    <?php if($faviconArray && $faviconArray['url']): ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $faviconArray['url']; ?>">
    <link rel="icon" type="image/png" href="<?php echo $faviconArray['url']; ?>">
    <link rel="icon" type="image/png" href="<?php echo $faviconArray['url']; ?>">
        <link rel="apple-touch-startup-image" href="<?php echo $faviconArray['url']; ?>">
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $faviconArray['url']; ?>">
    <?php endif; ?>
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/assets/favicon/site.webmanifest" crossorigin="use-credentials">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <!-- Apple Mobile -->
    <link rel="apple-touch-icon-precomposed" href="">

    <meta name='HandheldFriendly' content='true' />
    <meta name='format-detection' content='telephone=no' />
    <meta name="msapplication-tap-highlight" content="no">
    <!-- Add to Home Screen -->
    <meta name="apple-mobile-web-app-title" content="" />
    <meta name="apple-mobile-web-app-capable" content="no" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <!-- Smart App Banner - https://developer.apple.com/library/safari/documentation/AppleApplications/Reference/SafariHTMLRef/Articles/MetaTags.html -->
    <meta name="apple-itunes-app" content="app-id=APP_ID,affiliate-data=AFFILIATE_ID,app-argument=SOME_TEXT">
    <!-- Meta Tags Generated via http://heymeta.com -->
    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="<?php wp_title(''); ?>">
    <meta itemprop="description" content="<?php bloginfo('description'); ?>">
    <meta itemprop="image" content="<?php echo get_template_directory_uri(); ?>/assets/img/google.jpg">
    <!-- Facebook Open Graph
    https://developers.facebook.com/tools/debug/sharing/
    https://developers.facebook.com/docs/sharing/webmasters#markup -->
    <meta property="fb:app_id" content="123456789">
    <meta property="og:url" content="<?php bloginfo('wpurl'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php wp_title(''); ?>">
    <meta property="og:description" content="<?php bloginfo('description'); ?>">
    <meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/assets/img/facebook.jpg">
    <!-- Twitter Card
    https://cards-dev.twitter.com/validator
    https://dev.twitter.com/cards/getting-started -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php wp_title(''); ?>">
    <meta name="twitter:description" content="<?php bloginfo('description'); ?>">
    <meta name="twitter:image" content="<?php echo get_template_directory_uri(); ?>/assets/img/twitter.jpg">

    <?= get_template_part('template-parts/general/fonts'); ?>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?> data-module="bugReport">

<?php
wp_body_open();
bagxpro_render_page_loader();
bagxpro_render_configurator_sidebar();

$optionMenu = get_field('option_menu_style', 'option');

get_template_part('template-parts/header/' . $optionMenu);
?>
