<?php
/**
 * The header for the SolanaWP theme - UPDATED
 *
 * This is the template that displays all of the <head> section and everything up until main content.
 * CHANGES: Restructured to have smaller header, animated section, and moved title section
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="profile" href="https://gmpg.org/xfn/11" />
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content">
        <?php esc_html_e( 'Skip to content', 'solanawp' ); ?>
    </a>

    <header id="masthead" class="site-header" role="banner">
        <?php // UPDATED: Much smaller header with only logo and brand name ?>
        <div class="header">
            <?php
            // Display site branding (logo and brand name only)
            get_template_part( 'template-parts/layout/header-branding' );
            ?>
        </div>
    </header>

    <?php // NEW: Animated section between header and title ?>
    <div class="hero-text-section">
        <div class="hero-text">🚀 <?php esc_html_e( 'Advanced Blockchain Analysis Platform', 'solanawp' ); ?></div>
        <div class="hero-subtext"><?php esc_html_e( 'Real-time validation • Risk assessment • Professional insights', 'solanawp' ); ?></div>
    </div>

    <?php
    // NEW: Title section (moved from header)
    // Only display on checker pages
    $display_title_section = false;
    if ( is_front_page() ) {
        if (is_page_template('templates/template-address-checker.php') || get_page_template_slug(get_option('page_on_front')) === 'templates/template-address-checker.php' || get_post_type() === 'page') {
            $display_title_section = true;
        }
    } elseif ( is_page_template( 'templates/template-address-checker.php' ) ) {
        $display_title_section = true;
    }

    if ( $display_title_section ) :
        ?>
        <div class="title-section">
            <h1 class="main-title"><?php echo esc_html__( 'Solana Coins Address Checker', 'solanawp' ); ?></h1><?php // CHANGED: Added "Coins" ?>
            <p class="subtitle"><?php echo esc_html__( 'Comprehensive validation and analysis for Solana addresses', 'solanawp' ); ?></p>
            <p class="slogan"><?php echo esc_html__( "Hannisol's Insight, Navigating Crypto Like Hannibal Crossed the Alps.", 'solanawp' ); ?></p>
        </div>
    <?php
    endif;
    ?>

    <?php // Primary navigation menu (if needed) ?>
    <?php if ( has_nav_menu( 'primary' ) ) : ?>
        <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'solanawp' ); ?>">
            <?php
            $walker_args = array(
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'menu_class'     => 'primary-menu',
                'container'      => false,
            );
            if ( class_exists( 'SolanaWP_Nav_Walker' ) ) {
                $walker_args['walker'] = new SolanaWP_Nav_Walker();
            }
            wp_nav_menu( $walker_args );
            ?>
        </nav>
    <?php endif; ?>

    <div id="content" class="site-content">
