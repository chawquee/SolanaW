<?php
/**
 * Template part for displaying the "Rug Pull Risk Analysis" card for the Solana Checker.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */
?>
<div class="card" id="rugPullRiskCard" style="display:none;"> <?php // Structure & ID from hannisolsvelte.html, initially hidden ?>
    <div class="card-header"> <?php // Class from hannisolsvelte.html ?>
        <svg class="icon text-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <?php // Class and SVG from hannisolsvelte.html ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path> <?php // ?>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Rug Pull Risk Analysis', 'solanawp' ); ?></h2> <?php // Text from hannisolsvelte.html ?>
    </div>
    <div class="card-content"> <?php // Class from hannisolsvelte.html ?>
        <div class="metrics-grid"> <?php // Class from hannisolsvelte.html ?>
            <div class="metric-card"> <?php // ?>
                <div class="metric-value text-yellow" id="rugOverallScore">-</div> <?php // Placeholder for JS ?>
                <div class="metric-label"><?php esc_html_e( 'Overall Score', 'solanawp' ); ?></div> <?php // Text from hannisolsvelte.html ?>
            </div>
            <div class="metric-card"> <?php // ?>
                <div style="background: #fef3c7; color: #92400e; padding: 8px 16px; border-radius: 9999px; font-weight: bold; font-size: 18px;" id="rugRiskLevel">-</div> <?php // Placeholder for JS, style from hannisolsvelte.html ?>
                <div class="metric-label"><?php esc_html_e( 'Risk Level', 'solanawp' ); ?></div> <?php // Text from hannisolsvelte.html ?>
            </div>
            <div class="metric-card"> <?php // ?>
                <div class="metric-value text-green" id="rugVolume24h">-</div> <?php // Placeholder for JS ?>
                <div class="metric-label"><?php esc_html_e( '24h Volume', 'solanawp' ); ?></div> <?php // Text from hannisolsvelte.html ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px;"> <?php // Grid structure from hannisolsvelte.html ?>
            <div>
                <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #374151;"><?php esc_html_e( 'Risk Factors', 'solanawp' ); ?></h4> <?php // Style and text from hannisolsvelte.html ?>
                <div style="/* space-y: 8px; */" class="risk-factors-list"> <?php // Children will have padding ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;"><span><?php esc_html_e( 'Liquidity Locked:', 'solanawp' ); ?></span><span id="rugLiquidityLocked" style="font-weight: 600;">-</span></div> <?php // Placeholder for JS, color will be set by JS ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;"><span><?php esc_html_e( 'Ownership Renounced:', 'solanawp' ); ?></span><span id="rugOwnershipRenounced" style="font-weight: 600;">-</span></div> <?php // Placeholder for JS ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;"><span><?php esc_html_e( 'Mint Authority:', 'solanawp' ); ?></span><span id="rugMintAuthority" style="font-weight: 600;">-</span></div> <?php // Placeholder for JS ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;"><span><?php esc_html_e( 'Freeze Authority:', 'solanawp' ); ?></span><span id="rugFreezeAuthority" style="font-weight: 600;">-</span></div> <?php // Placeholder for JS ?>
                </div>
            </div>
            <div>
                <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #374151;"><?php esc_html_e( 'Token Distribution', 'solanawp' ); ?></h4> <?php // Style and text from hannisolsvelte.html ?>
                <div style="/* space-y: 12px; */" id="rugTokenDistribution"> <?php // JS Target, children will have margin ?>
                    <?php // Token distribution bars will be populated by JS. Template for one item: ?>
                    <div class="token-distribution-item-template" style="margin-bottom: 12px; display:none;"> <?php // Initially hidden template ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span class="dist-label"></span> <?php // JS Target ?>
                            <span class="dist-percentage"></span> <?php // JS Target ?>
                        </div>
                        <div style="width: 100%; background-color: #e5e7eb; border-radius: 9999px; height: 8px;"> <?php // Style from hannisolsvelte.html ?>
                            <div class="dist-bar" style="height: 8px; border-radius: 9999px; width: 0%;"></div> <?php // JS Target for background-color and width ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
