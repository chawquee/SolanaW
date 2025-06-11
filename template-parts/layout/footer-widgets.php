<?php
/**
 * Template part for displaying footer widgets if any are active.
 * It's assumed a 'footer-widgets' sidebar is registered in inc/widget-areas.php.
 * This allows for additional content or navigation in the footer.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if the 'footer-widgets' sidebar (registered in inc/widget-areas.php) has any active widgets.
if ( ! is_active_sidebar( 'footer-widgets' ) ) {
    return; // If no widgets are assigned to this area, output nothing.
}
?>
<div class="footer-widgets-area widget-area section-padding"> <?php // General wrapper class for the footer widgets section. 'section-padding' can be a utility class for spacing. ?>
    <div class="container footer-widgets-container"> <?php // Optional inner container for layout (you would define .container style in main.css if used elsewhere). ?>
        <div class="footer-widgets-grid"> <?php // Optional grid wrapper, e.g., for creating multiple columns for footer widgets. CSS for this would be in main.css. ?>
            <?php dynamic_sidebar( 'footer-widgets' ); // This WordPress function displays the widgets assigned to the 'footer-widgets' area. ?>
        </div>
    </div>
</div>
