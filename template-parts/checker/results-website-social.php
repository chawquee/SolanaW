<?php
/**
 * Template part for displaying the "Website & Social Accounts" card for the Solana Checker.
 * Called by template-address-checker.php or front-page.php.
 * Structure and classes from hannisolsvelte.html.
 * File location: template-parts/checker/results-website-social.php
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="card" id="websiteSocialCard" style="display:none;"> <?php // Structure & ID for JavaScript targeting, initially hidden ?>
    <div class="card-header"> <?php // Class from hannisolsvelte.html ?>
        <svg class="icon text-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // Class and SVG ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Website & Social Accounts', 'solanawp' ); ?></h2>
    </div>
    <div class="card-content"> <?php // Class from hannisolsvelte.html ?>

        <?php // Web Information Section ?>
        <div class="website-social-section">
            <h4><?php esc_html_e( 'Web Information', 'solanawp' ); ?></h4>
            <div class="web-info-grid"> <?php // Grid for web information items ?>
                <div class="web-info-item">
                    <div class="web-info-label"><?php esc_html_e( 'Website Address', 'solanawp' ); ?></div>
                    <div class="web-info-value" id="webInfoAddress">-</div> <?php // Placeholder for JS ?>
                </div>
                <div class="web-info-item">
                    <div class="web-info-label"><?php esc_html_e( 'Registration Date', 'solanawp' ); ?></div>
                    <div class="web-info-value text-blue" id="webInfoRegDate">-</div> <?php // Placeholder for JS ?>
                </div>
                <div class="web-info-item">
                    <div class="web-info-label"><?php esc_html_e( 'Registration Country', 'solanawp' ); ?></div>
                    <div class="web-info-value text-blue" id="webInfoRegCountry">-</div> <?php // Placeholder for JS ?>
                </div>
            </div>
        </div>

        <?php // Telegram Information Section ?>
        <div class="website-social-section">
            <h4><?php esc_html_e( 'Telegram Information', 'solanawp' ); ?></h4>
            <div class="telegram-info-grid"> <?php // Grid for telegram information ?>
                <div class="telegram-info-item">
                    <div class="telegram-info-label"><?php esc_html_e( 'Channel/Group', 'solanawp' ); ?></div>
                    <div class="telegram-info-value" id="telegramChannel">-</div> <?php // Placeholder for JS ?>
                </div>
                <div class="telegram-info-item">
                    <div class="telegram-info-label"><?php esc_html_e( 'Members', 'solanawp' ); ?></div>
                    <div class="telegram-info-value text-blue" id="telegramMembers">-</div> <?php // Placeholder for JS ?>
                </div>
            </div>
        </div>

        <?php // X (Twitter) Information Section ?>
        <div class="website-social-section">
            <h4><?php esc_html_e( 'X (Twitter) Information', 'solanawp' ); ?></h4>
            <div class="twitter-info-grid"> <?php // Grid for twitter information ?>
                <div class="twitter-info-item">
                    <div class="twitter-info-label"><?php esc_html_e( 'Account Handle', 'solanawp' ); ?></div>
                    <div class="twitter-info-value" id="twitterHandle">-</div> <?php // Placeholder for JS ?>
                </div>
                <div class="twitter-info-item">
                    <div class="twitter-info-label"><?php esc_html_e( 'Followers', 'solanawp' ); ?></div>
                    <div class="twitter-info-value" id="twitterFollowers">-</div> <?php // Placeholder for JS ?>
                </div>
                <div class="twitter-info-item">
                    <div class="twitter-info-label"><?php esc_html_e( 'Verified', 'solanawp' ); ?></div>
                    <div class="twitter-info-value" id="twitterVerified">-</div> <?php // Placeholder for JS, color will be set by JS ?>
                </div>
            </div>
        </div>
    </div>
</div>
