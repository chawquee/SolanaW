<?php
/**
 * Template part for displaying the "Token Distribution Analysis" card for the Solana Checker.
 * Called by template-address-checker.php or front-page.php.
 * NEW: Standalone section extracted from Rug Pull Risk Analysis with enhanced features.
 * UPDATED: Following new update requirements with icons, banners, and improved formatting
 * UPDATED: Dynamic Time Period Sub-sections Based on Activity Duration
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="card" id="tokenDistributionCard" style="display:none;">
    <div class="card-header">
        <span style="font-size: 1.5rem;">📊</span>
        <h2 class="card-title"><?php esc_html_e( 'Token Distribution Analysis', 'solanawp' ); ?></h2>
    </div>
    <div class="card-content">

        <!-- Holders Distribution Section -->
        <div class="token-distribution-section">
            <h3 class="section-title">
                <span class="section-icon">📊</span>
                <?php esc_html_e( 'Holders Distribution', 'solanawp' ); ?>
            </h3>

            <!-- Distribution Metrics Grid -->
            <div class="distribution-metrics">
                <div class="distribution-metric">
                    <div class="distribution-label"><?php esc_html_e( 'Total Holders', 'solanawp' ); ?></div>
                    <div class="distribution-value" id="totalHoldersCount">-</div>
                </div>
                <div class="distribution-metric">
                    <div class="distribution-label"><?php esc_html_e( 'Top 1 Holder', 'solanawp' ); ?></div>
                    <div class="distribution-value" id="concentrationTop1">-</div>
                </div>
                <div class="distribution-metric">
                    <div class="distribution-label"><?php esc_html_e( 'Top 5 Holders', 'solanawp' ); ?></div>
                    <div class="distribution-value" id="concentrationTop5">-</div>
                </div>
                <div class="distribution-metric">
                    <div class="distribution-label"><?php esc_html_e( 'Top 20 Holders', 'solanawp' ); ?></div>
                    <div class="distribution-value" id="concentrationTop20">-</div>
                </div>
                <div class="distribution-metric">
                    <div class="distribution-label"><?php esc_html_e( 'Top 50 Holders', 'solanawp' ); ?></div>
                    <div class="distribution-value" id="concentrationTop50">-</div>
                </div>
                <div class="distribution-metric">
                    <div class="distribution-label"><?php esc_html_e( 'Top 100 Holders', 'solanawp' ); ?></div>
                    <div class="distribution-value" id="concentrationTop100">-</div>
                </div>
                <div class="distribution-metric">
                    <div class="distribution-label"><?php esc_html_e( 'Top 250 Holders', 'solanawp' ); ?></div>
                    <div class="distribution-value" id="concentrationTop250">-</div>
                </div>
                <div class="distribution-metric">
                    <div class="distribution-label"><?php esc_html_e( 'Top 500 Holders', 'solanawp' ); ?></div>
                    <div class="distribution-value" id="concentrationTop500">-</div>
                </div>
            </div>

            <!-- Risk Assessment -->
            <div class="risk-assessment" id="distributionRiskContainer">
                <div class="risk-header">
                    <span class="risk-icon" id="distributionRiskIcon">⚠️</span>
                    <span class="risk-level" id="distributionRiskLevel"><?php esc_html_e( 'Analyzing...', 'solanawp' ); ?></span>
                </div>
                <p class="risk-explanation" id="distributionRiskExplanation"><?php esc_html_e( 'Analyzing token distribution risks...', 'solanawp' ); ?></p>
            </div>
        </div>

        <!-- Top Holders Distribution Section -->
        <div class="token-distribution-section">
            <h3 class="section-title">
                <span class="section-icon">📈</span>
                <?php esc_html_e( 'Top Holders Distribution', 'solanawp' ); ?>
            </h3>

            <!-- Top Holders Info Banner -->
            <div class="info-banner top-holders-info-banner">
                <div class="banner-icon">📊</div>
                <div class="banner-content">
                    <div class="banner-title"><?php esc_html_e( 'What does token concentration mean?', 'solanawp' ); ?></div>
                    <div class="banner-text">
                        <?php esc_html_e( 'Token concentration shows how many tokens the largest holders own. High concentration (like 80%+ held by top 20 holders) can be risky because few people control most tokens. Lower concentration is generally healthier for long-term investment as it reduces manipulation risk.', 'solanawp' ); ?>
                    </div>
                </div>
            </div>

            <div class="distribution-chart" id="tokenDistributionChart">
                <div class="loading-placeholder"><?php esc_html_e( 'Loading distribution data...', 'solanawp' ); ?></div>
            </div>
        </div>

        <!-- Holders Growth Analysis Section - UPDATED: Dynamic Time Periods -->
        <div class="token-distribution-section">
            <h3 class="section-title">
                <span class="section-icon">📊</span>
                <?php esc_html_e( 'Holders Growth Analysis', 'solanawp' ); ?>
            </h3>

            <!-- UPDATED: Dynamic holders growth grid - will be populated by JavaScript -->
            <div class="holders-growth-grid dynamic-periods-container" id="dynamicHoldersGrowthGrid">
                <!-- Dynamic time period cards will be inserted here by JavaScript based on activity duration -->
                <div class="loading-placeholder">
                    <?php esc_html_e( 'Calculating dynamic time periods...', 'solanawp' ); ?>
                </div>
            </div>

            <!-- Debug info container (can be hidden in production) -->
            <div class="dynamic-periods-debug" id="dynamicPeriodsDebug" style="display: none;">
                <small>
                    <strong>Debug Info:</strong>
                    <span id="debugActivityDuration">-</span> |
                    <span id="debugPeriodsCount">-</span> periods |
                    <span id="debugCalculationMethod">-</span>
                </small>
            </div>

            <!-- Growth Analysis Info Banner -->
            <div class="info-banner growth-analysis-info-banner">
                <div class="banner-icon">📈</div>
                <div class="banner-content">
                    <div class="banner-title"><?php esc_html_e( 'Understanding Holder Changes Over Time', 'solanawp' ); ?></div>
                    <div class="banner-text">
                        <?php esc_html_e( 'Tracking how the number of token holders changes over different time periods helps you understand investor sentiment. Growing holders (green) often indicates increasing confidence and adoption. Declining holders (red) might suggest selling pressure or loss of interest. Short-term changes (5m-6h) show immediate market reactions, while longer periods (7-30 days) reveal investment trends. Time periods shown are dynamically adjusted based on token activity duration.', 'solanawp' ); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Holders Categories Section -->
        <div class="token-distribution-section">
            <h3 class="section-title">
                <span class="section-icon">🐋</span>
                <?php esc_html_e( 'Holders Categories', 'solanawp' ); ?>
            </h3>
            <div class="holders-categories-grid">
                <div class="category-metric-card">
                    <div class="category-icon">🐋</div>
                    <div class="category-name"><?php esc_html_e( 'Whales', 'solanawp' ); ?></div>
                    <div class="category-count" id="holdersWhales">-</div>
                </div>
                <div class="category-metric-card">
                    <div class="category-icon">🦈</div>
                    <div class="category-name"><?php esc_html_e( 'Sharks', 'solanawp' ); ?></div>
                    <div class="category-count" id="holdersSharks">-</div>
                </div>
                <div class="category-metric-card">
                    <div class="category-icon">🐬</div>
                    <div class="category-name"><?php esc_html_e( 'Dolphins', 'solanawp' ); ?></div>
                    <div class="category-count" id="holdersDolphins">-</div>
                </div>
                <div class="category-metric-card">
                    <div class="category-icon">🐟</div>
                    <div class="category-name"><?php esc_html_e( 'Fish', 'solanawp' ); ?></div>
                    <div class="category-count" id="holdersFish">-</div>
                </div>
                <div class="category-metric-card">
                    <div class="category-icon">🐙</div>
                    <div class="category-name"><?php esc_html_e( 'Octopus', 'solanawp' ); ?></div>
                    <div class="category-count" id="holdersOctopus">-</div>
                </div>
                <div class="category-metric-card">
                    <div class="category-icon">🦀</div>
                    <div class="category-name"><?php esc_html_e( 'Crabs', 'solanawp' ); ?></div>
                    <div class="category-count" id="holdersCrabs">-</div>
                </div>
                <div class="category-metric-card">
                    <div class="category-icon">🦐</div>
                    <div class="category-name"><?php esc_html_e( 'Shrimps', 'solanawp' ); ?></div>
                    <div class="category-count" id="holdersShrimps">-</div>
                </div>
            </div>

            <!-- Holders Categories Info Banner -->
            <div class="info-banner holders-categories-info-banner">
                <div class="banner-icon">🐋</div>
                <div class="banner-content">
                    <div class="banner-title"><?php esc_html_e( 'What do these holder categories mean?', 'solanawp' ); ?></div>
                    <div class="banner-text">
                        <?php esc_html_e( 'Whales 🐋 are massive holders who can move markets. Sharks 🦈 are large but more active traders. Dolphins 🐬 represent serious investors with substantial holdings. Fish 🐟 are medium-sized holders. Octopus 🐙, Crabs 🦀, and Shrimps 🦐 are smaller holders. A healthy token has many small-to-medium holders (fish, crabs, shrimps) and fewer whales. Too many whales can create price manipulation risk, while many small holders indicate broad community adoption.', 'solanawp' ); ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
