<?php
/**
 * Template Name: Address Checker
 *
 * This is the template for the Solana Address Checker.
 * It can be used on any page.
 *
 * @link https://developer.wordpress.org/themes/template-files-section/page-template-files/
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); // Includes header.php
?>
    <div class="main-container">
        <?php get_sidebar(); // Includes sidebar.php ?>

        <div id="primary" class="content-area address-checker-content">
            <main id="main" class="site-main solanawp-checker-main" role="main">
                <?php
                // Loop through WordPress content if needed
                while ( have_posts() ) :
                    the_post();
                    // You can include content-page.php here if you want to show page content
                    // get_template_part( 'template-parts/content', 'page' );
                endwhile;
                ?>

                <?php // --- Input Section for the address checker --- ?>
                <?php get_template_part( 'template-parts/checker/input-section' ); ?>

                <?php // --- Results Section container --- ?>
                <div class="results-section" id="resultsSection">
                    <?php get_template_part( 'template-parts/checker/results-validation' ); ?>
                    <?php get_template_part( 'template-parts/checker/results-balance' ); ?>
                    <?php get_template_part( 'template-parts/checker/results-transactions' ); ?>

                    <div id="accountAndSecurityOuterGrid" class="account-security-grid-wrapper" style="display:none;">
                        <?php get_template_part( 'template-parts/checker/results-account-details' ); ?>
                        <?php get_template_part( 'template-parts/checker/results-security' ); ?>
                    </div>

                    <?php get_template_part( 'template-parts/checker/results-rugpull' ); ?>
                    <?php get_template_part( 'template-parts/checker/results-website-social' ); ?>
                    <?php get_template_part( 'template-parts/checker/results-affiliate' ); ?>

                    <?php
                    // --- New Custom Content Banner ---
                    $content_banner_html = get_theme_mod('solanawp_content_banner_html', '');
                    if (!empty($content_banner_html)) :
                        ?>
                        <div class="card content-area-banner" style="margin-bottom: 24px;">
                            <div class="card-content">
                                <?php echo wp_kses_post($content_banner_html); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php get_template_part( 'template-parts/checker/results-final' ); ?>
                </div>
            </main>
        </div>

        <?php
        // Ensure the function exists before calling it.
        // This function is defined in functions.php and renders ads from the Customizer
        // for the right sidebar, mirroring the left sidebar's functionality.
        if ( function_exists( 'solanawp_get_right_sidebar' ) ) {
            echo solanawp_get_right_sidebar();
        } else {
            // Fallback if the function is somehow not available
            // You could output a placeholder or an error message here.
            // For example, a simple placeholder:
            echo '<aside id="secondary-right" class="widget-area sidebar-right" role="complementary">';
            echo '<p>Right sidebar content is unavailable.</p>';
            echo '</aside>';
        }
        ?>
    </div>
<?php
get_footer(); // Includes footer.php
