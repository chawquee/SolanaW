<?php
/**
 * Template part for displaying the ENHANCED "Rug Pull Risk Analysis" card - DASHBOARD LAYOUT
 * UPDATED: Enhanced with icons, better status handling, and animated loading states
 * NEW UPDATES: Rugged/Mutable color logic, Risk Score improvements, Insider Networks enhancements
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="card" id="rugPullRiskCard" style="display:none;">
    <div class="card-header">
        <span style="font-size: 2rem;">⚠️</span>
        <h1 class="card-title"><?php esc_html_e( '🔍 Rug Pull Risk Analysis', 'solanawp' ); ?></h1>
    </div>
    <div class="card-content">

        <!-- Main Risk Metrics Grid -->
        <div class="rug-metrics-grid">
            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Rug Risk Score', 'solanawp' ); ?></div>
                <div class="rug-metric-value risk-high" id="rugOverallScore">-</div>
                <div class="rug-metric-sublabel"><?php esc_html_e( '(0-100)', 'solanawp' ); ?></div>
            </div>

            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Rugged Status', 'solanawp' ); ?></div>
                <div class="rug-metric-value" id="ruggedStatus">-</div>
                <div class="rug-metric-sublabel" id="ruggedDate"></div>
            </div>

            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Mutable', 'solanawp' ); ?></div>
                <div class="rug-metric-value" id="mutableStatus">-</div>
            </div>

            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Risks', 'solanawp' ); ?></div>
                <div class="rug-metric-value" style="font-size: 1rem; text-align: left; max-height: 200px; overflow-y: auto;" id="keyRiskIndicators">
                    <div class="loading-placeholder"><?php esc_html_e( 'Loading risks...', 'solanawp' ); ?></div>
                </div>
            </div>
        </div>

        <!-- Explanations -->
        <div class="rug-explanations-grid">
            <div class="rug-explanation-box rug-explanation-blue">
                <?php esc_html_e( 'A score from 0 (low risk) to 100 (high risk) based on multiple factors.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-red">
                <strong><?php esc_html_e( 'Warning:', 'solanawp' ); ?></strong> <?php esc_html_e( 'A "Yes" indicates the project has been identified as a rug pull.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-yellow">
                <strong><?php esc_html_e( 'Warning:', 'solanawp' ); ?></strong> <?php esc_html_e( '"Yes" means metadata can be changed, potentially misleading investors.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-blue">
                <?php esc_html_e( 'Provides a quick, at-a-glance summary of the overall risk score.', 'solanawp' ); ?>
            </div>
        </div>

        <!-- TWO-COLUMN LAYOUT -->
        <div class="two-column">

            <!-- LEFT COLUMN: Token Distribution + Security & Liquidity -->
            <div class="left-column">

                <!-- Token Distribution Section -->
                <div class="distribution-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">📊</span>
                        <h3 class="section-title"><?php esc_html_e( 'Token Distribution Analysis', 'solanawp' ); ?></h3>
                    </div>

                    <!-- Distribution Metrics Grid -->
                    <div class="distribution-metrics">
                        <div class="distribution-metric">
                            <div class="distribution-label"><?php esc_html_e( 'Total Holders', 'solanawp' ); ?></div>
                            <div class="distribution-value" id="totalHoldersCount">-</div>
                        </div>
                        <div class="distribution-metric">
                            <div class="distribution-label"><?php esc_html_e( 'Top Holders', 'solanawp' ); ?></div>
                            <div class="distribution-value" id="topHoldersCount">-</div>
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
                    </div>

                    <!-- Risk Assessment -->
                    <div class="risk-assessment" id="distributionRiskContainer">
                        <div class="risk-header">
                            <span class="risk-icon" id="distributionRiskIcon">⚠️</span>
                            <span class="risk-level" id="distributionRiskLevel"><?php esc_html_e( 'Analyzing...', 'solanawp' ); ?></span>
                        </div>
                        <p class="risk-explanation" id="distributionRiskExplanation"><?php esc_html_e( 'Analyzing token distribution risks...', 'solanawp' ); ?></p>
                    </div>

                    <!-- Holder Distribution Chart -->
                    <div class="holder-distribution">
                        <h4><?php esc_html_e( 'Top Holders Distribution', 'solanawp' ); ?></h4>
                        <div class="distribution-chart" id="rugTokenDistribution">
                            <div class="loading-placeholder"><?php esc_html_e( 'Loading distribution data...', 'solanawp' ); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Security & Liquidity Section -->
                <div class="security-liquidity-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">🔒</span>
                        <h3 class="section-title"><?php esc_html_e( 'Security & Liquidity Analysis', 'solanawp' ); ?></h3>
                    </div>

                    <!-- Authority Analysis -->
                    <div class="authority-analysis">
                        <div class="security-metric" id="mintAuthorityContainer">
                            <div class="metric-header">
                                <span class="metric-icon-small" id="mintAuthorityIcon">🔑</span>
                                <div class="metric-title"><?php esc_html_e( 'Mint Authority', 'solanawp' ); ?></div>
                                <div class="metric-status" id="mintAuthorityStatus"><?php esc_html_e( 'Checking...', 'solanawp' ); ?></div>
                            </div>
                            <div class="metric-description" id="mintAuthorityExplanation"><?php esc_html_e( 'Analyzing mint authority status...', 'solanawp' ); ?></div>
                        </div>

                        <div class="security-metric" id="freezeAuthorityContainer">
                            <div class="metric-header">
                                <span class="metric-icon-small" id="freezeAuthorityIcon">❄️</span>
                                <div class="metric-title"><?php esc_html_e( 'Freeze Authority', 'solanawp' ); ?></div>
                                <div class="metric-status" id="freezeAuthorityStatus"><?php esc_html_e( 'Checking...', 'solanawp' ); ?></div>
                            </div>
                            <div class="metric-description" id="freezeAuthorityExplanation"><?php esc_html_e( 'Analyzing freeze authority status...', 'solanawp' ); ?></div>
                        </div>

                        <div class="security-metric" id="liquidityContainer">
                            <div class="metric-header">
                                <span class="metric-icon-small" id="liquidityIcon">💧</span>
                                <div class="metric-title"><?php esc_html_e( 'Liquidity Lock', 'solanawp' ); ?></div>
                                <div class="metric-status" id="liquidityStatus"><?php esc_html_e( 'Checking...', 'solanawp' ); ?></div>
                            </div>
                            <div class="metric-percentage" id="liquidityPercentage"></div>
                            <div class="metric-description" id="liquidityExplanation"><?php esc_html_e( 'Analyzing liquidity lock status...', 'solanawp' ); ?></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Risk Indicators + Creator History + Insider Networks + Lockers -->
            <div class="right-column">

                <!-- Risk Indicators Section -->
                <div class="risk-indicators-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">⚠️</span>
                        <h3 class="section-title"><?php esc_html_e( 'Risk Indicators', 'solanawp' ); ?></h3>
                    </div>
                    <div class="risk-indicators-container" id="keyRiskIndicators">
                        <div class="loading-placeholder"><?php esc_html_e( 'Analyzing risk factors...', 'solanawp' ); ?></div>
                    </div>
                </div>

                <!-- Creator History Section -->
                <div class="creator-history-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">👤</span>
                        <h3 class="section-title"><?php esc_html_e( 'Creator Token History', 'solanawp' ); ?></h3>
                    </div>
                    <div class="creator-history-container" id="creatorTokensContainer">
                        <div class="loading-placeholder"><?php esc_html_e( 'Loading creator history...', 'solanawp' ); ?></div>
                    </div>
                </div>

                <!-- Insider Networks Section -->
                <div class="insider-networks-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">🕸️</span>
                        <h3 class="section-title"><?php esc_html_e( 'Insider Networks Analysis', 'solanawp' ); ?></h3>
                    </div>
                    <div class="insider-status">
                        <strong><?php esc_html_e( 'Insiders Detected:', 'solanawp' ); ?></strong>
                        <span id="insidersDetectedStatus"><?php esc_html_e( 'Analyzing...', 'solanawp' ); ?></span>
                    </div>
                    <div class="insider-networks-container" id="insiderNetworksContainer">
                        <div class="loading-placeholder"><?php esc_html_e( 'Analyzing insider networks...', 'solanawp' ); ?></div>
                    </div>
                </div>

                <!-- Lockers & Vesting Section -->
                <div class="lockers-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">🔐</span>
                        <h3 class="section-title"><?php esc_html_e( 'Lockers & Vesting', 'solanawp' ); ?></h3>
                    </div>
                    <div class="lockers-container" id="lockersContainer">
                        <div class="loading-placeholder"><?php esc_html_e( 'Loading locker information...', 'solanawp' ); ?></div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
