<?php
/**
 * Admin Settings Page for SolanaWP
 * Enhanced interface with pre-configured API keys and status monitoring
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

        <!-- Status Dashboard -->
        <div class="solanawp-dashboard" style="margin: 20px 0;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">

                <!-- API Status Card -->
                <div class="postbox" style="padding: 20px;">
                    <h3 style="margin: 0 0 15px 0; color: #1e40af;">🔗 API Status</h3>
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
                    <div>
                        <strong>Overall Status:</strong>
                        <span style="color: <?php echo (!empty($solana_rpc_url) && !empty($helius_api_key)) ? '#10b981' : '#f59e0b'; ?>;">
                            <?php
                            if (!empty($solana_rpc_url) && !empty($helius_api_key)) {
                                echo '🟢 Fully Operational';
                            } elseif (!empty($solana_rpc_url) || !empty($helius_api_key)) {
                                echo '🟡 Partially Configured';
                            } else {
                                echo '🔴 Needs Configuration';
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Usage Statistics Card -->
                <div class="postbox" style="padding: 20px;">
                    <h3 style="margin: 0 0 15px 0; color: #1e40af;">📊 Usage Statistics</h3>
                    <div style="margin-bottom: 10px;">
                        <strong>Total Requests:</strong> <?php echo number_format($total_requests); ?>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Successful:</strong> <span style="color: #10b981;"><?php echo number_format($successful_requests); ?></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Errors:</strong> <span style="color: #ef4444;"><?php echo number_format($error_requests); ?></span>
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
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-left: 4px solid #3b82f6; border-radius: 4px;">
            <h3 style="margin: 0 0 10px 0;">🚀 Quick Actions</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="<?php echo home_url(); ?>" class="button button-primary" target="_blank">
                    📋 Test Address Checker
                </a>
                <button type="button" class="button" onclick="solanawpTestConnection()">
                    🔍 Test API Connection
                </button>
                <button type="button" class="button" onclick="solanawpClearCache()">
                    🗑️ Clear Cache
                </button>
                <button type="button" class="button" onclick="solanawpClearLogs()">
                    📝 Clear Logs
                </button>
            </div>
        </div>

        <!-- Settings Form -->
        <form method="post" action="options.php" id="solanawp-settings-form">
            <?php settings_fields( 'solanawp_api_settings' ); ?>

            <table class="form-table" role="presentation">
                <tbody>
                <!-- QuickNode RPC Settings -->
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
                            class="regular-text code"
                            placeholder="https://your-quicknode-endpoint.quiknode.pro/..."
                        />
                        <p class="description">
                            <?php _e( 'Your QuickNode RPC endpoint URL for high-performance Solana data access.', 'solanawp' ); ?>
                            <?php if ( !empty($solana_rpc_url) ): ?>
                                <span style="color: #10b981;">✅ <?php _e( 'Currently configured with your provided QuickNode endpoint', 'solanawp' ); ?></span>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>

                <!-- Helius API Settings -->
                <tr>
                    <th scope="row">
                        <label for="helius_api_key"><?php _e( 'Helius API Key', 'solanawp' ); ?></label>
                    </th>
                    <td>
                        <div style="position: relative;">
                            <input
                                name="helius_api_key"
                                type="password"
                                id="helius_api_key"
                                value="<?php echo esc_attr( $helius_api_key ); ?>"
                                class="regular-text code"
                                placeholder="Enter your Helius API key"
                            />
                            <button type="button" onclick="solanawpTogglePassword('helius_api_key')" style="position: absolute; right: 5px; top: 2px; background: none; border: none; cursor: pointer;">👁️</button>
                        </div>
                        <p class="description">
                            <?php _e( 'Helius API key for enhanced Solana analytics, security analysis, and transaction parsing.', 'solanawp' ); ?>
                            <?php if ( !empty($helius_api_key) ): ?>
                                <span style="color: #10b981;">✅ <?php _e( 'Currently configured with your provided Helius API key', 'solanawp' ); ?></span>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>

                <!-- Performance Settings -->
                <tr>
                    <th scope="row"><?php _e( 'Performance Settings', 'solanawp' ); ?></th>
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
                    <th scope="row"><?php _e( 'Logging & Monitoring', 'solanawp' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input
                                    name="enable_logging"
                                    type="checkbox"
                                    value="1"
                                    <?php checked( $enable_logging ); ?>
                                />
                                <?php _e( 'Enable request logging', 'solanawp' ); ?>
                            </label>
                            <p class="description"><?php _e( 'Log API requests for monitoring and debugging. Recommended for production sites.', 'solanawp' ); ?></p>
                        </fieldset>
                    </td>
                </tr>
                </tbody>
            </table>

            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'solanawp_admin_nonce' ); ?>" />

            <?php submit_button( __( 'Save Settings', 'solanawp' ), 'primary', 'submit', true, array( 'style' => 'font-size: 14px; padding: 8px 24px;' ) ); ?>
        </form>

        <!-- Help Section -->
        <div style="margin: 30px 0; padding: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
            <h3 style="margin: 0 0 15px 0; color: #1e40af;">❓ Help & Information</h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div>
                    <h4 style="color: #374151; margin: 0 0 10px 0;">🔧 Getting Started</h4>
                    <ul style="margin: 0; color: #6b7280;">
                        <li>Your QuickNode and Helius API keys are already configured</li>
                        <li>Test the address checker on your homepage</li>
                        <li>Monitor usage statistics in the dashboard above</li>
                        <li>Enable caching for better performance</li>
                    </ul>
                </div>

                <div>
                    <h4 style="color: #374151; margin: 0 0 10px 0;">🚀 API Benefits</h4>
                    <ul style="margin: 0; color: #6b7280;">
                        <li><strong>QuickNode:</strong> High-performance RPC with 99.9% uptime</li>
                        <li><strong>Helius:</strong> Enhanced analytics and security analysis</li>
                        <li><strong>Caching:</strong> Reduces API calls and improves speed</li>
                        <li><strong>Rate Limiting:</strong> Protects against API abuse</li>
                    </ul>
                </div>

                <div>
                    <h4 style="color: #374151; margin: 0 0 10px 0;">📊 Monitoring</h4>
                    <ul style="margin: 0; color: #6b7280;">
                        <li>View real-time API connection status</li>
                        <li>Track success rates and error patterns</li>
                        <li>Monitor performance metrics</li>
                        <li>Clear cache and logs when needed</li>
                    </ul>
                </div>
            </div>

            <div style="margin: 20px 0 0 0; padding: 15px; background: #dbeafe; border-radius: 6px; border-left: 4px solid #3b82f6;">
                <strong style="color: #1e40af;">💡 Optimization Tips:</strong>
                <span style="color: #1e40af;">Enable caching to reduce API calls • Set appropriate rate limits • Monitor logs for issues • Test regularly</span>
            </div>
        </div>

        <!-- Recent Activity Log -->
        <?php if ( $enable_logging && !empty($logs) ): ?>
            <div style="margin: 30px 0;">
                <h3><?php _e( 'Recent Activity', 'solanawp' ); ?> <small style="color: #6b7280;">(Last 10 requests)</small></h3>
                <table class="widefat fixed striped" style="margin-top: 10px;">
                    <thead>
                    <tr>
                        <th style="width: 15%;"><?php _e( 'Time', 'solanawp' ); ?></th>
                        <th style="width: 35%;"><?php _e( 'Address', 'solanawp' ); ?></th>
                        <th style="width: 12%;"><?php _e( 'Status', 'solanawp' ); ?></th>
                        <th style="width: 15%;"><?php _e( 'IP', 'solanawp' ); ?></th>
                        <th style="width: 23%;"><?php _e( 'Error', 'solanawp' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $recent_logs = array_slice( array_reverse($logs), 0, 10 );
                    foreach ( $recent_logs as $log ):
                        $status_color = $log['status'] === 'success' ? '#10b981' : '#ef4444';
                        $status_icon = $log['status'] === 'success' ? '✅' : '❌';
                        ?>
                        <tr>
                            <td><?php echo esc_html( date( 'M j, H:i', strtotime($log['timestamp']) ) ); ?></td>
                            <td style="font-family: monospace; font-size: 11px;"><?php echo esc_html( substr($log['address'], 0, 20) . '...' ); ?></td>
                            <td style="color: <?php echo $status_color; ?>;"><?php echo $status_icon . ' ' . ucfirst($log['status']); ?></td>
                            <td style="font-family: monospace; font-size: 11px;"><?php echo esc_html( $log['ip'] ); ?></td>
                            <td style="color: #ef4444; font-size: 11px;"><?php echo $log['error'] ? esc_html( substr($log['error'], 0, 50) . '...' ) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .solanawp-dashboard .postbox {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .solanawp-dashboard h3 {
            font-size: 16px;
            font-weight: 600;
        }

        #solanawp-settings-form input[type="url"],
        #solanawp-settings-form input[type="password"],
        #solanawp-settings-form input[type="number"] {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        }

        .form-table th {
            font-weight: 600;
            color: #374151;
        }
    </style>

    <script>
        // Toggle password visibility
        function solanawpTogglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        // Save settings via AJAX
        document.getElementById('solanawp-settings-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'solanawp_save_api_settings');

            const submitBtn = this.querySelector('[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = '<?php _e( 'Saving...', 'solanawp' ); ?>';
            submitBtn.disabled = true;

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php _e( 'Settings saved successfully!', 'solanawp' ); ?>');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        alert('<?php _e( 'Error: ', 'solanawp' ); ?>' + (data.data ? data.data.message : 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Settings save error:', error);
                    alert('<?php _e( 'Network error. Please try again.', 'solanawp' ); ?>');
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Test API connection
        function solanawpTestConnection() {
            const rpcUrl = document.getElementById('solana_rpc_url').value;
            const heliusKey = document.getElementById('helius_api_key').value;

            if (!rpcUrl && !heliusKey) {
                alert('<?php _e( 'Please configure at least one API endpoint first.', 'solanawp' ); ?>');
                return;
            }

            // Test with a known Solana address
            const testAddress = '11111111111111111111111111111111';
            const testData = new FormData();
            testData.append('action', 'solanawp_check_address');
            testData.append('address', testAddress);
            testData.append('nonce', '<?php echo wp_create_nonce( 'solanawp_solana_checker_nonce' ); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: testData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ API connection test successful!\n\nResponse received for test address.');
                    } else {
                        alert('❌ API connection test failed:\n\n' + (data.data ? data.data.message : 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Connection test error:', error);
                    alert('❌ Network error during connection test.');
                });
        }

        // Clear cache
        function solanawpClearCache() {
            if (confirm('<?php _e( 'Are you sure you want to clear all cached data?', 'solanawp' ); ?>')) {
                const formData = new FormData();
                formData.append('action', 'solanawp_clear_cache');
                formData.append('nonce', '<?php echo wp_create_nonce( 'solanawp_admin_nonce' ); ?>');

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Cache cleared successfully!');
                        } else {
                            alert('❌ Error clearing cache: ' + (data.data ? data.data.message : 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Clear cache error:', error);
                        alert('❌ Network error while clearing cache.');
                    });
            }
        }

        // Clear logs
        function solanawpClearLogs() {
            if (confirm('<?php _e( 'Are you sure you want to clear all request logs?', 'solanawp' ); ?>')) {
                const formData = new FormData();
                formData.append('action', 'solanawp_clear_logs');
                formData.append('nonce', '<?php echo wp_create_nonce( 'solanawp_admin_nonce' ); ?>');

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Logs cleared successfully!');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            alert('❌ Error clearing logs: ' + (data.data ? data.data.message : 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Clear logs error:', error);
                        alert('❌ Network error while clearing logs.');
                    });
            }
        }
    </script>
    <?php
}

/**
 * AJAX handler to clear logs
 */
add_action( 'wp_ajax_solanawp_clear_logs', 'solanawp_handle_clear_logs' );

function solanawp_handle_clear_logs() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'solanawp' ) ) );
    }

    if ( ! wp_verify_nonce( $_POST['nonce'], 'solanawp_admin_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'solanawp' ) ) );
    }

    delete_option( 'solanawp_request_logs' );
    wp_send_json_success( array( 'message' => __( 'Logs cleared successfully.', 'solanawp' ) ) );
}
