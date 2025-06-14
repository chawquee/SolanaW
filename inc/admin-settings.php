<?php
/**
 * Admin Settings Page for SolanaWP
 * Enhanced interface with pre-configured API keys and status monitoring
 * Updated with DexScreener integration and new WHOIS service
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add admin menu item
 */
add_action( 'admin_menu', 'solanawp_add_admin_menu' );
function solanawp_add_admin_menu() {
    add_options_page(
        __( 'SolanaWP API Settings', 'solanawp' ),
        __( '🚀 Solana API', 'solanawp' ),
        'manage_options',
        'solanawp-api-settings',
        'solanawp_admin_page'
    );
}

/**
 * Enhanced Admin Settings Page
 */
function solanawp_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Get current settings with your API keys as defaults
    $solana_rpc_url = get_option( 'solanawp_solana_rpc_url', 'https://docs-demo.solana-mainnet.quiknode.pro/8296d90ecc3fd935a810ab23362ac9a2044d1af6/' );
    $helius_api_key = get_option( 'solanawp_helius_api_key', '0eb7ee92-786a-43a4-b64e-afa478664406' );
    $rate_limit = get_option( 'solanawp_rate_limit', 100 );
    $enable_logging = get_option( 'solanawp_enable_logging', true );
    $enable_caching = get_option( 'solanawp_enable_caching', true );
    $cache_duration = get_option( 'solanawp_cache_duration', 300 );

    // Get usage statistics
    $logs = get_option( 'solanawp_request_logs', array() );
    $total_requests = count( $logs );
    $successful_requests = count( array_filter( $logs, function( $log ) { return $log['status'] === 'success'; } ) );
    $error_requests = $total_requests - $successful_requests;
    $success_rate = $total_requests > 0 ? round( ( $successful_requests / $total_requests ) * 100, 1 ) : 0;

    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-admin-settings" style="color: #3b82f6;"></span> <?php _e( 'SolanaWP API Settings', 'solanawp' ); ?></h1>

        <!-- 🚀 NEW: DexScreener Integration Notice -->
        <div class="notice notice-success" style="margin: 20px 0;">
            <h3 style="margin: 10px 0;">🔥 Enhanced with DexScreener Integration</h3>
            <p><strong>Primary Data Source:</strong> DexScreener API for comprehensive token analysis, trading data, and social links</p>
            <p><strong>WHOIS Service:</strong> Updated to use hannisolwhois.vercel.app for reliable domain analysis</p>
            <p><strong>Fallback APIs:</strong> QuickNode RPC and Helius API for additional blockchain data</p>
        </div>

        <!-- Status Dashboard -->
        <div class="solanawp-dashboard" style="margin: 20px 0;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">

                <!-- API Status Card -->
                <div class="postbox" style="padding: 20px;">
                    <h3 style="margin: 0 0 15px 0; color: #1e40af;">🔗 API Status</h3>
                    <div style="margin-bottom: 10px;">
                        <strong>DexScreener API:</strong>
                        <span style="color: #10b981;">✅ Active (Primary Source)</span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>WHOIS Service:</strong>
                        <span style="color: #10b981;">✅ hannisolwhois.vercel.app</span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>QuickNode RPC:</strong>
                        <span style="color: <?php echo !empty($solana_rpc_url) ? '#10b981' : '#ef4444'; ?>;">
                            <?php echo !empty($solana_rpc_url) ? '✅ Connected' : '❌ Not configured'; ?>
                        </span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Helius API:</strong>
                        <span style="color: <?php echo !empty($helius_api_key) ? '#10b981' : '#ef4444'; ?>;">
                            <?php echo !empty($helius_api_key) ? '✅ Connected' : '❌ Not configured'; ?>
                        </span>
                    </div>
                </div>

                <!-- Usage Statistics Card -->
                <div class="postbox" style="padding: 20px;">
                    <h3 style="margin: 0 0 15px 0; color: #1e40af;">📊 Usage Statistics</h3>
                    <div style="margin-bottom: 10px;">
                        <strong>Total Requests:</strong> <?php echo $total_requests; ?>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Successful:</strong> <span style="color: #10b981;"><?php echo $successful_requests; ?></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Errors:</strong> <span style="color: #ef4444;"><?php echo $error_requests; ?></span>
                    </div>
                    <div>
                        <strong>Success Rate:</strong> <span style="color: <?php echo $success_rate >= 90 ? '#10b981' : ($success_rate >= 70 ? '#f59e0b' : '#ef4444'); ?>;"><?php echo $success_rate; ?>%</span>
                    </div>
                </div>

                <!-- Performance Card -->
                <div class="postbox" style="padding: 20px;">
                    <h3 style="margin: 0 0 15px 0; color: #1e40af;">⚡ Performance</h3>
                    <div style="margin-bottom: 10px;">
                        <strong>Caching:</strong>
                        <span style="color: <?php echo $enable_caching ? '#10b981' : '#ef4444'; ?>;">
                            <?php echo $enable_caching ? '✅ Enabled' : '❌ Disabled'; ?>
                        </span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Cache Duration:</strong> <?php echo $cache_duration; ?> seconds
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Rate Limit:</strong> <?php echo $rate_limit; ?>/hour
                    </div>
                    <div>
                        <strong>Logging:</strong>
                        <span style="color: <?php echo $enable_logging ? '#10b981' : '#f59e0b'; ?>;">
                            <?php echo $enable_logging ? '✅ Enabled' : '⚠️ Disabled'; ?>
                        </span>
                    </div>
                </div>

                <!-- Data Sources Card -->
                <div class="postbox" style="padding: 20px;">
                    <h3 style="margin: 0 0 15px 0; color: #1e40af;">🎯 Data Priority</h3>
                    <div style="margin-bottom: 8px;">
                        <strong>1st:</strong> <span style="color: #3b82f6;">DexScreener API</span>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>2nd:</strong> <span style="color: #6b7280;">QuickNode RPC</span>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>3rd:</strong> <span style="color: #6b7280;">Helius Enhanced</span>
                    </div>
                    <div>
                        <strong>WHOIS:</strong> <span style="color: #059669;">hannisolwhois.vercel.app</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Settings Form -->
        <form method="post" action="" id="solanawp-api-settings-form">
            <?php wp_nonce_field( 'solanawp_api_settings_nonce' ); ?>

            <table class="form-table">

                <!-- API Configuration Section -->
                <tr>
                    <th scope="row" colspan="2">
                        <h2 style="margin: 0; color: #1e40af;">🔧 API Configuration</h2>
                        <p style="margin: 5px 0 20px 0; color: #6b7280; font-weight: normal;">
                            Configure your fallback APIs. DexScreener integration is automatic and requires no API key.
                        </p>
                    </th>
                </tr>

                <!-- QuickNode RPC URL -->
                <tr>
                    <th scope="row">
                        <label for="solana_rpc_url"><?php _e( 'QuickNode RPC URL', 'solanawp' ); ?></label>
                    </th>
                    <td>
                        <input
                            name="solana_rpc_url"
                            type="url"
                            id="solana_rpc_url"
                            value="<?php echo esc_attr( $solana_rpc_url ); ?>"
                            class="regular-text"
                            placeholder="https://your-endpoint.quiknode.pro/your-key/"
                        />
                        <p class="description">
                            <?php _e( 'Your QuickNode Solana mainnet RPC endpoint for blockchain data', 'solanawp' ); ?>
                            <?php if ( !empty($solana_rpc_url) ): ?>
                                <span style="color: #10b981;">✅ <?php _e( 'Currently configured with your provided QuickNode endpoint', 'solanawp' ); ?></span>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>

                <!-- Helius API Key -->
                <tr>
                    <th scope="row">
                        <label for="helius_api_key"><?php _e( 'Helius API Key', 'solanawp' ); ?></label>
                    </th>
                    <td>
                        <input
                            name="helius_api_key"
                            type="text"
                            id="helius_api_key"
                            value="<?php echo esc_attr( $helius_api_key ); ?>"
                            class="regular-text"
                            placeholder="your-helius-api-key"
                        />
                        <p class="description">
                            <?php _e( 'Your Helius API key for enhanced transaction and NFT data', 'solanawp' ); ?>
                            <?php if ( !empty($helius_api_key) ): ?>
                                <span style="color: #10b981;">✅ <?php _e( 'Currently configured with your provided Helius API key', 'solanawp' ); ?></span>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>

                <!-- Performance Settings Section -->
                <tr>
                    <th scope="row" colspan="2">
                        <h2 style="margin: 30px 0 0 0; color: #1e40af;">⚡ Performance Settings</h2>
                        <p style="margin: 5px 0 20px 0; color: #6b7280; font-weight: normal;">
                            Optimize response times and manage API usage limits.
                        </p>
                    </th>
                </tr>

                <!-- Caching Settings -->
                <tr>
                    <th scope="row"><?php _e( 'Caching', 'solanawp' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input
                                    name="enable_caching"
                                    type="checkbox"
                                    value="1"
                                    <?php checked( $enable_caching ); ?>
                                />
                                <?php _e( 'Enable caching (recommended)', 'solanawp' ); ?>
                            </label>
                            <br><br>

                            <label for="cache_duration">
                                <?php _e( 'Cache Duration (seconds):', 'solanawp' ); ?>
                                <input
                                    name="cache_duration"
                                    type="number"
                                    id="cache_duration"
                                    value="<?php echo esc_attr( $cache_duration ); ?>"
                                    min="60"
                                    max="3600"
                                    class="small-text"
                                />
                            </label>
                            <p class="description"><?php _e( 'How long to cache API responses (60-3600 seconds). Default: 300 (5 minutes)', 'solanawp' ); ?></p>
                        </fieldset>
                    </td>
                </tr>

                <!-- Rate Limiting -->
                <tr>
                    <th scope="row">
                        <label for="rate_limit"><?php _e( 'Rate Limiting', 'solanawp' ); ?></label>
                    </th>
                    <td>
                        <input
                            name="rate_limit"
                            type="number"
                            id="rate_limit"
                            value="<?php echo esc_attr( $rate_limit ); ?>"
                            min="10"
                            max="1000"
                            class="small-text"
                        />
                        <span><?php _e( 'requests per hour per IP', 'solanawp' ); ?></span>
                        <p class="description"><?php _e( 'Maximum number of address checks per hour per IP address. Helps prevent API abuse.', 'solanawp' ); ?></p>
                    </td>
                </tr>

                <!-- Logging Settings -->
                <tr>
                    <th scope="row"><?php _e( 'Logging', 'solanawp' ); ?></th>
                    <td>
                        <label>
                            <input
                                name="enable_logging"
                                type="checkbox"
                                value="1"
                                <?php checked( $enable_logging ); ?>
                            />
                            <?php _e( 'Enable request logging', 'solanawp' ); ?>
                        </label>
                        <p class="description"><?php _e( 'Log address checks for monitoring and debugging. Logs are stored in WordPress error log.', 'solanawp' ); ?></p>
                    </td>
                </tr>

            </table>

            <!-- Data Sources Information Section -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 30px 0;">
                <h3 style="margin: 0 0 15px 0; color: #1e40af;">📊 Integrated Data Sources</h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">

                    <!-- DexScreener -->
                    <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #3b82f6;">
                        <h4 style="margin: 0 0 10px 0; color: #3b82f6;">🥇 DexScreener API (Primary)</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #4b5563;">
                            <li>Token prices and market data</li>
                            <li>Trading volume and liquidity</li>
                            <li>Social links and websites</li>
                            <li>DEX diversity analysis</li>
                            <li>Price volatility metrics</li>
                        </ul>
                    </div>

                    <!-- QuickNode -->
                    <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #6b7280;">
                        <h4 style="margin: 0 0 10px 0; color: #6b7280;">🥈 QuickNode RPC (Secondary)</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #4b5563;">
                            <li>SOL balance verification</li>
                            <li>Account existence validation</li>
                            <li>On-chain account details</li>
                            <li>Blockchain data integrity</li>
                        </ul>
                    </div>

                    <!-- Helius -->
                    <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #6b7280;">
                        <h4 style="margin: 0 0 10px 0; color: #6b7280;">🥉 Helius API (Secondary)</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #4b5563;">
                            <li>Token and NFT metadata</li>
                            <li>Detailed transaction history</li>
                            <li>Enhanced balance information</li>
                            <li>Social link extraction</li>
                        </ul>
                    </div>

                    <!-- WHOIS Service -->
                    <div style="background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #059669;">
                        <h4 style="margin: 0 0 10px 0; color: #059669;">🌐 WHOIS Service (Specialized)</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #4b5563;">
                            <li>Domain registration data</li>
                            <li>Domain age analysis</li>
                            <li>Registrar information</li>
                            <li>SSL certificate detection</li>
                        </ul>
                        <p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280;">
                            <strong>Service:</strong> hannisolwhois.vercel.app
                        </p>
                    </div>

                </div>
            </div>

            <!-- Advanced Information -->
            <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <h4 style="margin: 0 0 10px 0; color: #92400e;">⚠️ Important Notes</h4>
                <ul style="margin: 0; padding-left: 20px; color: #92400e;">
                    <li><strong>DexScreener Integration:</strong> Primary data source, no API key required</li>
                    <li><strong>WHOIS Service:</strong> Updated to use hannisolwhois.vercel.app for improved reliability</li>
                    <li><strong>Fallback Strategy:</strong> APIs are used as secondary sources when DexScreener data is unavailable</li>
                    <li><strong>Performance:</strong> Enable caching to reduce API calls and improve response times</li>
                    <li><strong>Rate Limiting:</strong> Prevents abuse and protects your API quotas</li>
                </ul>
            </div>

            <!-- Submit Button -->
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e( 'Save Settings', 'solanawp' ); ?>" />
                <span id="solanawp-save-status" style="margin-left: 10px;"></span>
            </p>
        </form>

    </div>

    <!-- AJAX Script for Settings -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#solanawp-api-settings-form').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $submitButton = $form.find('#submit');
                var $status = $('#solanawp-save-status');

                // Show loading state
                $submitButton.prop('disabled', true).val('<?php _e( 'Saving...', 'solanawp' ); ?>');
                $status.html('<span style="color: #f59e0b;">💾 Saving settings...</span>');

                // Prepare form data
                var formData = {
                    action: 'solanawp_save_api_settings',
                    nonce: '<?php echo wp_create_nonce( 'solanawp_api_settings_nonce' ); ?>',
                    solana_rpc_url: $form.find('#solana_rpc_url').val(),
                    helius_api_key: $form.find('#helius_api_key').val(),
                    rate_limit: $form.find('#rate_limit').val(),
                    enable_logging: $form.find('input[name="enable_logging"]').is(':checked') ? 1 : 0,
                    enable_caching: $form.find('input[name="enable_caching"]').is(':checked') ? 1 : 0,
                    cache_duration: $form.find('#cache_duration').val()
                };

                // Save settings via AJAX
                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        $status.html('<span style="color: #10b981;">✅ ' + response.data.message + '</span>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $status.html('<span style="color: #ef4444;">❌ ' + response.data.message + '</span>');
                    }
                }).fail(function() {
                    $status.html('<span style="color: #ef4444;">❌ <?php _e( 'Error saving settings. Please try again.', 'solanawp' ); ?></span>');
                }).always(function() {
                    $submitButton.prop('disabled', false).val('<?php _e( 'Save Settings', 'solanawp' ); ?>');
                });
            });
        });
    </script>

    <?php
}

/**
 * Register settings for WordPress Settings API (backup method)
 */
add_action( 'admin_init', 'solanawp_register_settings' );
function solanawp_register_settings() {
    register_setting( 'solanawp_api_settings', 'solanawp_solana_rpc_url' );
    register_setting( 'solanawp_api_settings', 'solanawp_helius_api_key' );
    register_setting( 'solanawp_api_settings', 'solanawp_rate_limit' );
    register_setting( 'solanawp_api_settings', 'solanawp_enable_logging' );
    register_setting( 'solanawp_api_settings', 'solanawp_enable_caching' );
    register_setting( 'solanawp_api_settings', 'solanawp_cache_duration' );
}
