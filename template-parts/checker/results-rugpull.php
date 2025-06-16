<?php
/**
 * Template part for displaying the ENHANCED "Rug Pull Risk Analysis" card.
 * PHASES 2 & 3 IMPLEMENTATION - Authority Check and Token Distribution
 * Enhanced with colored risk warnings and explanations per roadmap.
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
        <svg class="icon text-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Rug Pull Risk Analysis', 'solanawp' ); ?></h2>
    </div>
    <div class="card-content">
        <!-- Overall Risk Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value text-yellow" id="rugOverallScore">-</div>
                <div class="metric-label"><?php esc_html_e( 'Overall Score', 'solanawp' ); ?></div>
            </div>
            <div class="metric-card">
                <div style="background: #fef3c7; color: #92400e; padding: 8px 16px; border-radius: 9999px; font-weight: bold; font-size: 18px; display:inline-block;" id="rugRiskLevel">-</div>
                <div class="metric-label"><?php esc_html_e( 'Risk Level', 'solanawp' ); ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-value text-green" id="rugVolume24h">-</div>
                <div class="metric-label"><?php esc_html_e( '24h Volume', 'solanawp' ); ?></div>
            </div>
        </div>

        <!-- PHASE 2: Authority Check Section -->
        <div style="margin-top: 24px;">
            <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #374151;"><?php esc_html_e( 'Authority Analysis', 'solanawp' ); ?></h4>

            <!-- Mint Authority -->
            <div class="authority-section" style="margin-bottom: 16px;">
                <div id="mintAuthorityStatus" class="risk-indicator" style="padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span id="mintAuthorityIcon">🔒</span>
                        <strong><?php esc_html_e( 'Mint Authority:', 'solanawp' ); ?></strong>
                        <span id="mintAuthorityText" style="font-weight: bold;">-</span>
                    </div>
                    <span id="mintAuthorityExplanation" class="explanation" style="font-style: italic; font-size: 0.9em; color: #6b7280; display: block;">Loading...</span>
                </div>
            </div>

            <!-- Freeze Authority -->
            <div class="authority-section">
                <div id="freezeAuthorityStatus" class="risk-indicator" style="padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span id="freezeAuthorityIcon">❄️</span>
                        <strong><?php esc_html_e( 'Freeze Authority:', 'solanawp' ); ?></strong>
                        <span id="freezeAuthorityText" style="font-weight: bold;">-</span>
                    </div>
                    <span id="freezeAuthorityExplanation" class="explanation" style="font-style: italic; font-size: 0.9em; color: #6b7280; display: block;">Loading...</span>
                </div>
            </div>
        </div>

        <!-- PHASE 3: Token Distribution Analysis -->
        <div style="margin-top: 24px;">
            <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #374151;"><?php esc_html_e( 'Token Distribution Analysis', 'solanawp' ); ?></h4>

            <!-- Concentration Metrics -->
            <div class="concentration-metrics" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px;">
                <div style="background: #f9fafb; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #374151;" id="top1HolderPercentage">-</div>
                    <div style="font-size: 0.8em; color: #6b7280;"><?php esc_html_e( 'Top 1 Holder', 'solanawp' ); ?></div>
                </div>
                <div style="background: #f9fafb; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #374151;" id="top5HoldersPercentage">-</div>
                    <div style="font-size: 0.8em; color: #6b7280;"><?php esc_html_e( 'Top 5 Holders', 'solanawp' ); ?></div>
                </div>
                <div style="background: #f9fafb; padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #374151;" id="top20HoldersPercentage">-</div>
                    <div style="font-size: 0.8em; color: #6b7280;"><?php esc_html_e( 'Top 20 Holders', 'solanawp' ); ?></div>
                </div>
            </div>

            <!-- Risk Assessment -->
            <div id="distributionRiskAssessment" class="risk-assessment" style="padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span id="distributionRiskIcon">📊</span>
                    <strong id="distributionRiskLevel"><?php esc_html_e( 'Distribution Risk:', 'solanawp' ); ?></strong>
                    <span id="distributionRiskText" style="font-weight: bold;">-</span>
                </div>
                <span id="distributionRiskExplanation" class="explanation" style="font-style: italic; font-size: 0.9em; color: #6b7280; display: block;">Loading distribution analysis...</span>
            </div>
        </div>

        <!-- Existing Risk Factors and Token Distribution Chart -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px;">
            <!-- Risk Factors (Left Column) -->
            <div>
                <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #374151;"><?php esc_html_e( 'Risk Factors', 'solanawp' ); ?></h4>
                <div class="risk-factors-list">
                    <div class="factor-item"><span><?php esc_html_e( 'Liquidity Locked:', 'solanawp' ); ?></span><span id="rugLiquidityLocked" style="font-weight: 600;">-</span></div>
                    <div class="factor-item"><span><?php esc_html_e( 'Ownership Renounced:', 'solanawp' ); ?></span><span id="rugOwnershipRenounced" style="font-weight: 600;">-</span></div>
                    <div class="factor-item"><span><?php esc_html_e( 'Mint Authority:', 'solanawp' ); ?></span><span id="rugMintAuthority" style="font-weight: 600;">-</span></div>
                    <div class="factor-item"><span><?php esc_html_e( 'Freeze Authority:', 'solanawp' ); ?></span><span id="rugFreezeAuthority" style="font-weight: 600;">-</span></div>
                </div>

                <!-- Warning Signs -->
                <div style="margin-top: 16px;">
                    <h5 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #ef4444;"><?php esc_html_e( 'Warning Signs', 'solanawp' ); ?></h5>
                    <ul id="rugPullWarningsList" style="list-style: none; padding: 0; margin: 0;">
                        <li class="loading-indicator" style="color: #6b7280; font-style: italic;"><?php esc_html_e( 'Loading warning signs...', 'solanawp' ); ?></li>
                    </ul>
                </div>

                <!-- Safe Indicators -->
                <div style="margin-top: 16px;">
                    <h5 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #10b981;"><?php esc_html_e( 'Safe Indicators', 'solanawp' ); ?></h5>
                    <ul id="rugPullSafeIndicatorsList" style="list-style: none; padding: 0; margin: 0;">
                        <li class="loading-indicator" style="color: #6b7280; font-style: italic;"><?php esc_html_e( 'Loading safe indicators...', 'solanawp' ); ?></li>
                    </ul>
                </div>
            </div>

            <!-- Token Distribution Chart (Right Column) -->
            <div>
                <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #374151;"><?php esc_html_e( 'Holder Distribution', 'solanawp' ); ?></h4>
                <div style="position: relative; height: 250px;">
                    <canvas id="tokenDistributionChart"></canvas>
                </div>

                <!-- Distribution List -->
                <div id="rugTokenDistribution" style="margin-top: 16px;">
                    <p class="loading-indicator" style="color: #6b7280; font-style: italic;"><?php esc_html_e( 'Loading token distribution...', 'solanawp' ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
