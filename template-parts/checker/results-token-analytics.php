<?php
/**
 * Template part for displaying the "Token Analytics" card for the Solana Checker.
 * Called by front-page.php after validation and before balance sections.
 *
 * POSITIONING: This section appears AFTER Address Validation and BEFORE Balance & Holdings
 * FILE LOCATION: template-parts/checker/results-token-analytics.php
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
        <div class="card-subtitle">
            <?php esc_html_e( 'Real-time price and trading data from DexScreener', 'solanawp' ); ?>
        </div>
    </div>
    <div class="card-content">
        <!-- Price Information Section -->
        <div class="token-analytics-section">
            <h4 class="analytics-section-title">
                <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                <?php esc_html_e( 'Price Information', 'solanawp' ); ?>
            </h4>
            <div class="token-analytics-grid">
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( 'Price (USD)', 'solanawp' ); ?></div>
                    <div class="analytics-value primary-price" id="tokenPriceUsd">-</div>
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

        <!-- Volume Information Section -->
        <div class="token-analytics-section">
            <h4 class="analytics-section-title">
                <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <?php esc_html_e( 'Volume Information', 'solanawp' ); ?>
            </h4>
            <div class="token-analytics-grid">
                <div class="analytics-item">
                    <div class="analytics-label"><?php esc_html_e( '24h Volume', 'solanawp' ); ?></div>
                    <div class="analytics-value volume-primary" id="tokenVolume24h">-</div>
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

        <!-- Price Changes Section -->
        <div class="token-analytics-section">
            <h4 class="analytics-section-title">
                <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                </svg>
                <?php esc_html_e( 'Price Changes', 'solanawp' ); ?>
            </h4>
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

        <!-- Trading Activity Section -->
        <div class="token-analytics-section">
            <h4 class="analytics-section-title">
                <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <?php esc_html_e( 'Trading Activity', 'solanawp' ); ?>
            </h4>
            <div class="trading-activity-grid">
                <div class="trading-period">
                    <div class="period-label"><?php esc_html_e( '24h Activity', 'solanawp' ); ?></div>
                    <div class="trading-stats">
                        <div class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Buys:', 'solanawp' ); ?></span>
                            <span class="stat-value buy-value" id="tokenBuys24h">-</span>
                        </div>
                        <div class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Sells:', 'solanawp' ); ?></span>
                            <span class="stat-value sell-value" id="tokenSells24h">-</span>
                        </div>
                    </div>
                </div>
                <div class="trading-period">
                    <div class="period-label"><?php esc_html_e( '6h Activity', 'solanawp' ); ?></div>
                    <div class="trading-stats">
                        <div class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Buys:', 'solanawp' ); ?></span>
                            <span class="stat-value buy-value" id="tokenBuys6h">-</span>
                        </div>
                        <div class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Sells:', 'solanawp' ); ?></span>
                            <span class="stat-value sell-value" id="tokenSells6h">-</span>
                        </div>
                    </div>
                </div>
                <div class="trading-period">
                    <div class="period-label"><?php esc_html_e( '1h Activity', 'solanawp' ); ?></div>
                    <div class="trading-stats">
                        <div class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Buys:', 'solanawp' ); ?></span>
                            <span class="stat-value buy-value" id="tokenBuys1h">-</span>
                        </div>
                        <div class="trade-stat">
                            <span class="stat-label"><?php esc_html_e( 'Sells:', 'solanawp' ); ?></span>
                            <span class="stat-value sell-value" id="tokenSells1h">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Source Attribution -->
        <div class="data-source-attribution">
            <div class="attribution-content">
                <svg class="attribution-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                <span><?php esc_html_e( 'Data powered by', 'solanawp' ); ?> <strong>DexScreener API</strong></span>
                <span class="live-indicator">
                    <span class="live-dot"></span>
                    <?php esc_html_e( 'Live', 'solanawp' ); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
    /* Token Analytics Specific Styles */
    .token-analytics-section {
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .token-analytics-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
    }

    .analytics-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 16px;
    }

    .section-icon {
        width: 20px;
        height: 20px;
        color: #6366f1;
    }

    .token-analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .analytics-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        transition: all 0.2s ease;
    }

    .analytics-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .analytics-label {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        margin-bottom: 8px;
    }

    .analytics-value {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        min-height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .analytics-value.primary-price {
        font-size: 20px;
        color: #6366f1;
    }

    .analytics-value.volume-primary {
        color: #059669;
    }

    .price-change {
        font-weight: 600;
    }

    .trading-activity-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .trading-period {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
    }

    .period-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 12px;
        text-align: center;
    }

    .trading-stats {
        display: flex;
        justify-content: space-between;
        gap: 12px;
    }

    .trade-stat {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }

    .stat-label {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 16px;
        font-weight: 600;
    }

    .buy-value {
        color: #059669;
    }

    .sell-value {
        color: #dc2626;
    }

    .data-source-attribution {
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .attribution-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 12px;
        color: #6b7280;
    }

    .attribution-icon {
        width: 16px;
        height: 16px;
    }

    .live-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #059669;
        font-weight: 500;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: #059669;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(5, 150, 105, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(5, 150, 105, 0);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .token-analytics-grid {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .trading-activity-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .analytics-item {
            padding: 12px;
        }

        .analytics-value {
            font-size: 16px;
        }

        .analytics-value.primary-price {
            font-size: 18px;
        }
    }

    @media (max-width: 480px) {
        .token-analytics-grid {
            grid-template-columns: 1fr;
        }

        .trading-stats {
            flex-direction: column;
            gap: 8px;
        }

        .attribution-content {
            flex-direction: column;
            gap: 4px;
        }
    }
</style>
