<?php
/**
 * Template part for displaying the "Token Analytics" card for the Solana Checker.
 * Called by template-address-checker.php or front-page.php.
 *
 * POSITIONING: This section appears AFTER Address Validation and BEFORE Balance & Holdings
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="card" id="tokenAnalyticsCard" style="display:none;">
    <div class="card-header">
        <svg class="icon text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Token Analytics', 'solanawp' ); ?></h2>
    </div>
    <div class="card-content">
        <div class="token-analytics-section">
            <h4><?php esc_html_e( 'Price Information', 'solanawp' ); ?></h4>
            <div class="token-analytics-grid">
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( 'Price (USD)', 'solanawp' ); ?></div>
                    <div class="analytics-value" id="tokenPriceUsd">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( 'Price (SOL)', 'solanawp' ); ?></div>
                    <div class="analytics-value" id="tokenPriceNative">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( 'Liquidity', 'solanawp' ); ?></div>
                    <div class="analytics-value" id="tokenLiquidity">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( 'Market Cap', 'solanawp' ); ?></div>
                    <div class="analytics-value" id="tokenMarketCap">-</div>
                </div>
            </div>
        </div>

        <div class="token-analytics-section">
            <h4><?php esc_html_e( 'Volume Information', 'solanawp' ); ?></h4>
            <div class="token-analytics-grid">
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '24h Volume', 'solanawp' ); ?></div>
                    <div class="analytics-value" id="tokenVolume24h">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '6h Volume', 'solanawp' ); ?></div>
                    <div class="analytics-value" id="tokenVolume6h">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '1h Volume', 'solanawp' ); ?></div>
                    <div class="analytics-value" id="tokenVolume1h">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '24h Transactions', 'solanawp' ); ?></div>
                    <div class="analytics-value" id="tokenTransactions24h">-</div>
                </div>
            </div>
        </div>

        <div class="token-analytics-section">
            <h4><?php esc_html_e( 'Price Changes', 'solanawp' ); ?></h4>
            <div class="token-analytics-grid">
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '5m Change', 'solanawp' ); ?></div>
                    <div class="analytics-value price-change" id="tokenPriceChange5m">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '1h Change', 'solanawp' ); ?></div>
                    <div class="analytics-value price-change" id="tokenPriceChange1h">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '6h Change', 'solanawp' ); ?></div>
                    <div class="analytics-value price-change" id="tokenPriceChange6h">-</div>
                </div>
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '24h Change', 'solanawp' ); ?></div>
                    <div class="analytics-value price-change" id="tokenPriceChange24h">-</div>
                </div>
            </div>
        </div>

        <div class="token-analytics-section">
            <h4><?php esc_html_e( 'Trading Activity', 'solanawp' ); ?></h4>
            <div class="trading-activity-grid">
                <div class="trading-period">
                    <div class="period-label"><?php esc_html_e( '24h Activity', 'solanawp' ); ?></div>
                    <div class="trading-stats">
                        <span class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Buys:', 'solanawp' ); ?></span>
                            <span class="stat-value buy-value" id="tokenBuys24h">-</span>
                        </span>
                        <span class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Sells:', 'solanawp' ); ?></span>
                            <span class="stat-value sell-value" id="tokenSells24h">-</span>
                        </span>
                    </div>
                </div>
                <div class="trading-period">
                    <div class="period-label"><?php esc_html_e( '6h Activity', 'solanawp' ); ?></div>
                    <div class="trading-stats">
                        <span class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Buys:', 'solanawp' ); ?></span>
                            <span class="stat-value buy-value" id="tokenBuys6h">-</span>
                        </span>
                        <span class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Sells:', 'solanawp' ); ?></span>
                            <span class="stat-value sell-value" id="tokenSells6h">-</span>
                        </span>
                    </div>
                </div>
                <div class="trading-period">
                    <div class="period-label"><?php esc_html_e( '1h Activity', 'solanawp' ); ?></div>
                    <div class="trading-stats">
                        <span class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Buys:', 'solanawp' ); ?></span>
                            <span class="stat-value buy-value" id="tokenBuys1h">-</span>
                        </span>
                        <span class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Sells:', 'solanawp' ); ?></span>
                            <span class="stat-value sell-value" id="tokenSells1h">-</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
