<?php
/**
 * AJAX Handlers for SolanaWP Theme - ENHANCED WITH ALL 3 PHASES
 * PHASE 1: Enhanced Account Details with Alchemy API
 * PHASE 2: Authority Risk Analysis
 * PHASE 3: Token Distribution Analysis
 * DexScreener as PRIMARY source, Alchemy/Helius as SECONDARY fallback
 * Enhanced with Token Analytics support and X API integration for detailed Twitter data
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PUBLIC ENDPOINT: Check Solana Address (No Login Required)
 * Handles both logged-in and non-logged-in users
 */
add_action( 'wp_ajax_solanawp_check_address', 'solanawp_handle_address_check' );
add_action( 'wp_ajax_nopriv_solanawp_check_address', 'solanawp_handle_address_check' );

function solanawp_handle_address_check() {
    // 🔐 Security: Verify nonce
    if ( ! wp_verify_nonce( $_POST['nonce'], 'solanawp_solana_checker_nonce' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Security check failed. Please refresh the page.', 'solanawp' ),
            'code' => 'invalid_nonce'
        ) );
    }

    // 🔐 Rate Limiting: Check if user is making too many requests
    $user_ip = solanawp_get_client_ip();
    $rate_limit_key = 'solanawp_rate_limit_' . md5( $user_ip );
    $requests = get_transient( $rate_limit_key );
    $max_requests = get_option( 'solanawp_rate_limit', 100 ); // Default: 100 requests per hour

    if ( $requests >= $max_requests ) {
        wp_send_json_error( array(
            'message' => __( 'Too many requests. Please wait a moment before trying again.', 'solanawp' ),
            'code' => 'rate_limit_exceeded'
        ) );
    }

    // 🧹 Input Sanitization
    $address = sanitize_text_field( $_POST['address'] ?? '' );

    if ( empty( $address ) ) {
        wp_send_json_error( array(
            'message' => __( 'Please provide a Solana address.', 'solanawp' ),
            'code' => 'missing_address'
        ) );
    }

    // 📊 Update rate limiting counter
    set_transient( $rate_limit_key, ($requests + 1), 3600 ); // 1 hour window

    // 🔍 Process the Solana address with ENHANCED functionality
    try {
        $result = solanawp_process_solana_address_enhanced( $address );

        // 📝 Log successful request (optional)
        solanawp_log_request( $address, $user_ip, 'success' );

        wp_send_json_success( $result );

    } catch ( Exception $e ) {
        // 📝 Log error
        solanawp_log_request( $address, $user_ip, 'error', $e->getMessage() );

        wp_send_json_error( array(
            'message' => __( 'Unable to process address. Please try again.', 'solanawp' ),
            'code' => 'processing_error',
            'debug' => WP_DEBUG ? $e->getMessage() : null
        ) );
    }
}

/**
 * ADMIN ENDPOINT: Save API Settings (Login Required)
 */
add_action( 'wp_ajax_solanawp_save_api_settings', 'solanawp_handle_save_api_settings' );

function solanawp_handle_save_api_settings() {
    // 🔐 Admin Security Check
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Unauthorized access.', 'solanawp' ),
            'code' => 'unauthorized'
        ) );
    }

    // 🔐 Verify nonce
    if ( ! wp_verify_nonce( $_POST['nonce'], 'solanawp_api_settings_nonce' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Security check failed.', 'solanawp' ),
            'code' => 'invalid_nonce'
        ) );
    }

    // 🧹 Sanitize and save settings
    $settings = array(
        'solanawp_solana_rpc_url' => esc_url_raw( $_POST['solana_rpc_url'] ?? '' ),
        'solanawp_helius_api_key' => sanitize_text_field( $_POST['helius_api_key'] ?? '' ),
        'solanawp_rate_limit' => intval( $_POST['rate_limit'] ?? 100 ),
        'solanawp_enable_logging' => isset( $_POST['enable_logging'] ),
        'solanawp_enable_caching' => isset( $_POST['enable_caching'] ),
        'solanawp_cache_duration' => intval( $_POST['cache_duration'] ?? 300 )
    );

    foreach ( $settings as $key => $value ) {
        update_option( $key, $value );
    }

    wp_send_json_success( array(
        'message' => __( 'Settings saved successfully!', 'solanawp' )
    ) );
}

// ============================================================================
// X API INTEGRATION FUNCTIONS
// ============================================================================

/**
 * Get X API Bearer Token from WordPress options
 */
function solanawp_get_x_api_bearer_token() {
    return get_option( 'solanawp_x_api_bearer_token', '' );
}

/**
 * Extract Twitter username from URL (supports both twitter.com and x.com)
 */
function solanawp_extract_twitter_username_from_url( $url ) {
    if ( preg_match( '/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]+)/', $url, $matches ) ) {
        return $matches[1];
    }
    return null;
}

/**
 * Fetch detailed Twitter data from X API v2
 */
function solanawp_fetch_twitter_data_from_x_api( $username ) {
    $bearer_token = solanawp_get_x_api_bearer_token();

    if ( empty( $bearer_token ) ) {
        error_log( 'SolanaWP: X API Bearer Token not configured' );
        return null;
    }

    try {
        $api_url = 'https://api.twitter.com/2/users/by/username/' . urlencode( $username );
        $api_url .= '?user.fields=created_at,is_identity_verified,public_metrics,subscription_type,verified,verified_followers_count,verified_type';

        $response = wp_remote_get( $api_url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $bearer_token,
                'Content-Type' => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'SolanaWP: X API request failed: ' . $response->get_error_message() );
            return null;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            error_log( 'SolanaWP: X API returned HTTP ' . $response_code );
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['data'] ) ) {
            error_log( 'SolanaWP: Successfully fetched X API data for @' . $username );
            return $data['data'];
        }

        if ( isset( $data['errors'] ) ) {
            error_log( 'SolanaWP: X API error: ' . json_encode( $data['errors'] ) );
        }

        return null;

    } catch ( Exception $e ) {
        error_log( 'SolanaWP: X API exception: ' . $e->getMessage() );
        return null;
    }
}

/**
 * Map X API response to frontend fields
 */
function solanawp_map_x_api_data_to_frontend( $x_api_data ) {
    if ( !$x_api_data ) {
        return array(
            'verificationType' => 'Unavailable',
            'verifiedFollowers' => 'Unavailable',
            'subscriptionType' => 'Unavailable',
            'followers' => 'Unavailable',
            'identityVerification' => 'Unavailable',
            'creationDate' => 'Unavailable'
        );
    }

    // Verification Type
    $verification_type = 'Unavailable';
    if ( isset( $x_api_data['verified_type'] ) ) {
        switch ( $x_api_data['verified_type'] ) {
            case 'blue':
                $verification_type = 'Blue Checkmark';
                break;
            case 'government':
                $verification_type = 'Government';
                break;
            case 'business':
                $verification_type = 'Business';
                break;
            default:
                $verification_type = ucfirst( $x_api_data['verified_type'] );
                break;
        }
    } elseif ( isset( $x_api_data['verified'] ) && $x_api_data['verified'] ) {
        $verification_type = 'Legacy Verified';
    } else {
        $verification_type = 'Not Verified';
    }

    // Verified Followers
    $verified_followers = 'Unavailable';
    if ( isset( $x_api_data['verified_followers_count'] ) ) {
        $verified_followers = solanawp_format_number( $x_api_data['verified_followers_count'] );
    }

    // Subscription Type
    $subscription_type = 'Unavailable';
    if ( isset( $x_api_data['subscription_type'] ) ) {
        $subscription_type = ucfirst( str_replace( '_', ' ', $x_api_data['subscription_type'] ) );
    }

    // Followers
    $followers = 'Unavailable';
    if ( isset( $x_api_data['public_metrics']['followers_count'] ) ) {
        $followers = solanawp_format_number( $x_api_data['public_metrics']['followers_count'] );
    }

    // Identity Verification
    $identity_verification = 'Unavailable';
    if ( isset( $x_api_data['is_identity_verified'] ) ) {
        $identity_verification = $x_api_data['is_identity_verified'] ? 'Verified' : 'Not Verified';
    }

    // Creation Date
    $creation_date = 'Unavailable';
    if ( isset( $x_api_data['created_at'] ) ) {
        $creation_date = date( 'F j, Y', strtotime( $x_api_data['created_at'] ) );
    }

    return array(
        'verificationType' => $verification_type,
        'verifiedFollowers' => $verified_followers,
        'subscriptionType' => $subscription_type,
        'followers' => $followers,
        'identityVerification' => $identity_verification,
        'creationDate' => $creation_date
    );
}

/**
 * SETUP FUNCTION: Configure X API credentials
 */
function solanawp_setup_x_api_credentials() {
    $bearer_token = 'AAAAAAAAAAAAAAAAAAAAAIXm2QEAAAAA817IAG%2BBxH8ox%2FVyn7mnO2YBkV4%3DmPIb4p67b4Tfhtff5438ZWpgywZ5Engi66rNtvOU4ethEy6f1K';

    update_option( 'solanawp_x_api_bearer_token', $bearer_token );
    error_log( 'SolanaWP: X API credentials configured successfully' );

    update_option( 'solanawp_x_api_key', 'MymGaEXxCy3oOTXsmQ33IESFi' );
    update_option( 'solanawp_x_api_secret', 'RO7hxR03cVweFONnNur3QBmZnP3z9eKbgJ3Q1qv4SMX3lT4wPJ' );
}

// ============================================================================
// ENHANCED MAIN PROCESSING FUNCTIONS - ALL 3 PHASES
// ============================================================================

/**
 * ENHANCED MAIN PROCESSING FUNCTION - ALL 3 PHASES IMPLEMENTATION
 */
function solanawp_process_solana_address_enhanced( $address ) {
    // 💾 Check cache first
    $cache_key = "solana_analysis_enhanced_{$address}";
    $cached_result = solanawp_get_cache( $cache_key );

    if ( $cached_result !== false ) {
        return $cached_result;
    }

    // 🎯 Try DexScreener first for token data
    $dexscreener_data = solanawp_fetch_dexscreener_data( $address );

    // 📊 Aggregate all data with DexScreener prioritization
    $validation_data = solanawp_fetch_validation_data( $address );
    $balance_data = solanawp_fetch_balance_data( $address, $dexscreener_data );
    $transaction_data = solanawp_fetch_transaction_data( $address, $dexscreener_data );
    $security_data = solanawp_fetch_security_data( $address, $dexscreener_data );
    $social_data = solanawp_fetch_social_data( $address, $dexscreener_data );

    // NEW: Enhanced data with all 3 phases
    $enhanced_account_data = solanawp_fetch_enhanced_account_data( $address );
    $authority_risk_data = solanawp_fetch_authority_risk_data( $address );
    $distribution_data = solanawp_fetch_token_distribution_data( $address );

    // Enhanced rug pull analysis incorporating all phases
    $enhanced_rugpull_data = solanawp_fetch_enhanced_rugpull_data(
        $address,
        $dexscreener_data,
        $authority_risk_data,
        $distribution_data
    );

    $token_analytics = solanawp_extract_token_analytics( $dexscreener_data );
    $scores_data = solanawp_calculate_final_scores(
        $validation_data,
        $balance_data,
        $transaction_data,
        $security_data,
        $enhanced_rugpull_data,
        $social_data
    );

    $result = array(
        'address' => $address,
        'validation' => $validation_data,
        'balance' => $balance_data,
        'transactions' => $transaction_data,
        'account' => $enhanced_account_data, // PHASE 1: Enhanced account details
        'security' => $security_data,
        'rugpull' => $enhanced_rugpull_data, // PHASE 2 & 3: Enhanced rug pull analysis
        'social' => $social_data,
        'scores' => $scores_data,
        'dexscreener_data' => $dexscreener_data,
        'token_analytics' => $token_analytics,
        // NEW: Additional data from roadmap phases
        'authority_analysis' => $authority_risk_data, // PHASE 2: Authority risk data
        'distribution_analysis' => $distribution_data, // PHASE 3: Token distribution data
        'timestamp' => current_time( 'timestamp' )
    );

    // 💾 Cache the result
    solanawp_set_cache( $cache_key, $result );

    return $result;
}

/**
 * PHASE 1: Enhanced Account Details with Alchemy API
 */
function solanawp_fetch_enhanced_account_data( $address ) {
    try {
        // Use Alchemy endpoint from roadmap
        $alchemy_url = 'https://solana-mainnet.g.alchemy.com/v2/7b0nzOAaomkkueSW09CGrMnl1VPMy6vY';

        // API Call #1: getAccountInfo (Basic)
        $payload = json_encode( array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getAccountInfo',
            'params' => array( $address, array( 'encoding' => 'base64' ) )
        ) );

        $response = solanawp_make_request( $alchemy_url, array(
            'method' => 'POST',
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => $payload,
            'timeout' => 10
        ) );

        if ( isset( $response['result']['value'] ) && $response['result']['value'] !== null ) {
            $account_info = $response['result']['value'];

            $is_token = ( $account_info['owner'] === 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA' );

            return array(
                'is_token' => $is_token,
                // PHASE 1 REQUIREMENTS:
                'owner' => $account_info['owner'] ?? 'Unknown',                    // OWNER
                'executable' => $account_info['executable'] ? 'Yes' : 'No',       // EXECUTABLES
                'data_size' => isset( $account_info['data'][0] ) ? strlen( $account_info['data'][0] ) : 0, // DATA SIZE
                'rent_epoch' => $account_info['rentEpoch'] ?? 0,                  // RENT EPOCH
                'lamports' => $account_info['lamports'] ?? 0,
                'account_type' => $is_token ? 'Token Mint' : 'Wallet Account'
            );
        }

        return array(
            'is_token' => false,
            'owner' => 'Unknown',
            'executable' => 'No',
            'data_size' => 0,
            'rent_epoch' => 0,
            'account_type' => 'Unknown',
            'error' => 'Account not found'
        );

    } catch ( Exception $e ) {
        return array(
            'error' => $e->getMessage(),
            'is_token' => false,
            'owner' => 'Error',
            'executable' => 'Unknown',
            'data_size' => 0,
            'rent_epoch' => 0
        );
    }
}

/**
 * PHASE 2: Authority Check with Risk Analysis
 */
function solanawp_fetch_authority_risk_data( $address ) {
    try {
        $alchemy_url = 'https://solana-mainnet.g.alchemy.com/v2/7b0nzOAaomkkueSW09CGrMnl1VPMy6vY';

        // API Call #2: getAccountInfo (Parsed)
        $payload = json_encode( array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getAccountInfo',
            'params' => array( $address, array( 'encoding' => 'jsonParsed' ) )
        ) );

        $response = solanawp_make_request( $alchemy_url, array(
            'method' => 'POST',
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => $payload,
            'timeout' => 10
        ) );

        $authority_data = array(
            'mint_authority' => array(
                'value' => null,
                'status' => 'Unknown',
                'risk_level' => 'Unknown',
                'color' => '#6b7280',
                'explanation' => 'Unable to determine mint authority status'
            ),
            'freeze_authority' => array(
                'value' => null,
                'status' => 'Unknown',
                'risk_level' => 'Unknown',
                'color' => '#6b7280',
                'explanation' => 'Unable to determine freeze authority status'
            )
        );

        if ( isset( $response['result']['value']['data']['parsed']['info'] ) ) {
            $parsed_info = $response['result']['value']['data']['parsed']['info'];

            // PHASE 2: Mint Authority Analysis
            if ( isset( $parsed_info['mintAuthority'] ) ) {
                $mint_auth = $parsed_info['mintAuthority'];

                if ( $mint_auth === null ) {
                    // ✅ Safe (null) - SAFE
                    $authority_data['mint_authority'] = array(
                        'value' => 'null',
                        'status' => 'SAFE',
                        'risk_level' => 'Low',
                        'color' => '#10B981',
                        'explanation' => 'Token supply is fixed, no new tokens can be created to devalue existing holders'
                    );
                } else {
                    // 🚨 Risk (address) - DANGER
                    $authority_data['mint_authority'] = array(
                        'value' => $mint_auth,
                        'status' => 'DANGER',
                        'risk_level' => 'High',
                        'color' => '#EF4444',
                        'explanation' => 'Creator can mint unlimited new tokens and crash the price instantly'
                    );
                }
            }

            // PHASE 2: Freeze Authority Analysis
            if ( isset( $parsed_info['freezeAuthority'] ) ) {
                $freeze_auth = $parsed_info['freezeAuthority'];

                if ( $freeze_auth === null ) {
                    // ✅ Safe (null) - SAFE
                    $authority_data['freeze_authority'] = array(
                        'value' => 'null',
                        'status' => 'SAFE',
                        'risk_level' => 'Low',
                        'color' => '#10B981',
                        'explanation' => 'Your tokens cannot be frozen or locked by anyone'
                    );
                } else {
                    // 🚨 Risk (address) - DANGER
                    $authority_data['freeze_authority'] = array(
                        'value' => $freeze_auth,
                        'status' => 'DANGER',
                        'risk_level' => 'High',
                        'color' => '#EF4444',
                        'explanation' => 'Creator can freeze your tokens, preventing you from selling or transferring'
                    );
                }
            }
        }

        return $authority_data;

    } catch ( Exception $e ) {
        error_log( 'Authority risk analysis error: ' . $e->getMessage() );
        return array(
            'mint_authority' => array(
                'value' => 'Error',
                'status' => 'Error',
                'risk_level' => 'Unknown',
                'color' => '#EF4444',
                'explanation' => 'Error retrieving mint authority data'
            ),
            'freeze_authority' => array(
                'value' => 'Error',
                'status' => 'Error',
                'risk_level' => 'Unknown',
                'color' => '#EF4444',
                'explanation' => 'Error retrieving freeze authority data'
            ),
            'error' => $e->getMessage()
        );
    }
}

/**
 * PHASE 3: Token Distribution Analysis
 */
function solanawp_fetch_token_distribution_data( $address ) {
    try {
        $alchemy_url = 'https://solana-mainnet.g.alchemy.com/v2/7b0nzOAaomkkueSW09CGrMnl1VPMy6vY';

        // API Call #3: getTokenSupply
        $supply_payload = json_encode( array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getTokenSupply',
            'params' => array( $address )
        ) );

        $supply_response = solanawp_make_request( $alchemy_url, array(
            'method' => 'POST',
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => $supply_payload,
            'timeout' => 10
        ) );

        // API Call #4: getTokenLargestAccounts
        $holders_payload = json_encode( array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getTokenLargestAccounts',
            'params' => array( $address )
        ) );

        $holders_response = solanawp_make_request( $alchemy_url, array(
            'method' => 'POST',
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => $holders_payload,
            'timeout' => 10
        ) );

        $distribution_data = array(
            'total_supply' => 0,
            'top_1_percentage' => 0,
            'top_5_percentage' => 0,
            'top_20_percentage' => 0,
            'risk_level' => 'Unknown',
            'risk_assessment' => array(
                'level' => 'Unknown',
                'color' => '#6b7280',
                'explanation' => 'Unable to analyze token distribution'
            ),
            'largest_holders' => array()
        );

        // Process token supply
        if ( isset( $supply_response['result']['value']['amount'] ) ) {
            $distribution_data['total_supply'] = intval( $supply_response['result']['value']['amount'] );
        }

        // Process largest holders
        if ( isset( $holders_response['result']['value'] ) && is_array( $holders_response['result']['value'] ) ) {
            $holders = $holders_response['result']['value'];
            $total_supply = $distribution_data['total_supply'];

            if ( $total_supply > 0 && !empty( $holders ) ) {
                // Calculate concentration percentages
                $top_1_amount = 0;
                $top_5_amount = 0;
                $top_20_amount = 0;

                foreach ( $holders as $index => $holder ) {
                    $amount = intval( $holder['amount'] ?? 0 );

                    if ( $index === 0 ) {
                        $top_1_amount = $amount;
                    }
                    if ( $index < 5 ) {
                        $top_5_amount += $amount;
                    }
                    if ( $index < 20 ) {
                        $top_20_amount += $amount;
                    }

                    // Store holder data
                    if ( $index < 10 ) { // Top 10 for display
                        $distribution_data['largest_holders'][] = array(
                            'rank' => $index + 1,
                            'address' => $holder['address'] ?? 'Unknown',
                            'amount' => $amount,
                            'percentage' => round( ($amount / $total_supply) * 100, 2 )
                        );
                    }
                }

                // Calculate percentages as specified in roadmap
                $distribution_data['top_1_percentage'] = round( ($top_1_amount / $total_supply) * 100, 2 );
                $distribution_data['top_5_percentage'] = round( ($top_5_amount / $total_supply) * 100, 2 );
                $distribution_data['top_20_percentage'] = round( ($top_20_amount / $total_supply) * 100, 2 );

                // Risk assessment based on roadmap criteria
                $top_1 = $distribution_data['top_1_percentage'];
                $top_5 = $distribution_data['top_5_percentage'];
                $top_20 = $distribution_data['top_20_percentage'];

                if ( $top_1 > 40 || $top_5 > 80 || $top_20 > 95 ) {
                    // 🚨 HIGH RISK - DANGER ZONE
                    $distribution_data['risk_assessment'] = array(
                        'level' => 'HIGH RISK - DANGER ZONE',
                        'color' => '#EF4444',
                        'explanation' => 'Extreme concentration - few wallets control most tokens. Very high rug pull risk!'
                    );
                } elseif ( ($top_1 >= 20 && $top_1 <= 40) || ($top_5 >= 60 && $top_5 <= 80) || ($top_20 >= 80 && $top_20 <= 95) ) {
                    // ⚠️ MEDIUM RISK - CAUTION
                    $distribution_data['risk_assessment'] = array(
                        'level' => 'MEDIUM RISK - CAUTION',
                        'color' => '#F59E0B',
                        'explanation' => 'Moderate concentration - some wallets have significant control. Monitor closely before investing.'
                    );
                } else {
                    // ✅ LOW RISK - HEALTHY
                    $distribution_data['risk_assessment'] = array(
                        'level' => 'LOW RISK - HEALTHY',
                        'color' => '#10B981',
                        'explanation' => 'Well-distributed tokens reduce single-party manipulation and rug pull risk.'
                    );
                }
            }
        }

        return $distribution_data;

    } catch ( Exception $e ) {
        error_log( 'Token distribution analysis error: ' . $e->getMessage() );
        return array(
            'total_supply' => 0,
            'top_1_percentage' => 0,
            'top_5_percentage' => 0,
            'top_20_percentage' => 0,
            'risk_assessment' => array(
                'level' => 'Error',
                'color' => '#EF4444',
                'explanation' => 'Error analyzing token distribution: ' . $e->getMessage()
            ),
            'largest_holders' => array(),
            'error' => $e->getMessage()
        );
    }
}

/**
 * Enhanced Rug Pull Analysis incorporating Authority and Distribution data
 */
function solanawp_fetch_enhanced_rugpull_data( $address, $dexscreener_data = null, $authority_data = null, $distribution_data = null ) {
    // Start with existing rug pull data structure
    $rugpull_data = solanawp_fetch_rugpull_data( $address, $dexscreener_data );

    // PHASE 2: Integrate Authority Analysis
    if ( $authority_data ) {
        $rugpull_data['mint_authority'] = array(
            'text' => $authority_data['mint_authority']['status'] ?? 'Unknown',
            'color' => $authority_data['mint_authority']['color'] ?? '#6b7280',
            'explanation' => $authority_data['mint_authority']['explanation'] ?? 'Unknown'
        );

        $rugpull_data['freeze_authority'] = array(
            'text' => $authority_data['freeze_authority']['status'] ?? 'Unknown',
            'color' => $authority_data['freeze_authority']['color'] ?? '#6b7280',
            'explanation' => $authority_data['freeze_authority']['explanation'] ?? 'Unknown'
        );

        // Adjust risk score based on authorities
        if ( isset( $authority_data['mint_authority']['risk_level'] ) && $authority_data['mint_authority']['risk_level'] === 'High' ) {
            $rugpull_data['overall_score'] = min( 100, $rugpull_data['overall_score'] + 25 );
            $rugpull_data['warning_signs'][] = 'Mint authority not renounced - creator can mint new tokens';
        }

        if ( isset( $authority_data['freeze_authority']['risk_level'] ) && $authority_data['freeze_authority']['risk_level'] === 'High' ) {
            $rugpull_data['overall_score'] = min( 100, $rugpull_data['overall_score'] + 20 );
            $rugpull_data['warning_signs'][] = 'Freeze authority active - creator can freeze your tokens';
        }
    }

    // PHASE 3: Integrate Distribution Analysis
    if ( $distribution_data ) {
        // Update token distribution with real data
        $rugpull_data['token_distribution'] = array();

        if ( !empty( $distribution_data['largest_holders'] ) ) {
            foreach ( $distribution_data['largest_holders'] as $index => $holder ) {
                $rugpull_data['token_distribution'][] = array(
                    'label' => 'Top ' . ($index + 1) . ' Holder',
                    'percentage' => $holder['percentage'],
                    'color' => solanawp_get_distribution_color( $holder['percentage'] )
                );

                if ( $index >= 4 ) break; // Only show top 5 in chart
            }
        }

        // Add concentration risk warnings
        $top_1 = $distribution_data['top_1_percentage'] ?? 0;
        $top_5 = $distribution_data['top_5_percentage'] ?? 0;
        $top_20 = $distribution_data['top_20_percentage'] ?? 0;

        if ( $top_1 > 40 ) {
            $rugpull_data['overall_score'] = min( 100, $rugpull_data['overall_score'] + 30 );
            $rugpull_data['warning_signs'][] = "Top holder owns {$top_1}% of total supply - extreme concentration risk";
        }

        if ( $top_5 > 80 ) {
            $rugpull_data['overall_score'] = min( 100, $rugpull_data['overall_score'] + 20 );
            $rugpull_data['warning_signs'][] = "Top 5 holders own {$top_5}% of total supply - high manipulation risk";
        }

        // Add distribution metrics to rugpull data for frontend display
        $rugpull_data['concentration_metrics'] = array(
            'top_1_percentage' => $top_1,
            'top_5_percentage' => $top_5,
            'top_20_percentage' => $top_20,
            'risk_assessment' => $distribution_data['risk_assessment'] ?? array()
        );
    }

    // Recalculate risk level based on enhanced score
    $score = $rugpull_data['overall_score'];
    if ( $score >= 70 ) {
        $rugpull_data['risk_level'] = 'High';
        $rugpull_data['risk_percentage'] = min( $score, 100 );
    } elseif ( $score >= 40 ) {
        $rugpull_data['risk_level'] = 'Medium';
        $rugpull_data['risk_percentage'] = $score;
    } else {
        $rugpull_data['risk_level'] = 'Low';
        $rugpull_data['risk_percentage'] = max( $score, 10 );
    }

    return $rugpull_data;
}

/**
 * Helper function to assign colors based on holder percentage
 */
function solanawp_get_distribution_color( $percentage ) {
    if ( $percentage > 30 ) return '#ef4444'; // Red for high concentration
    if ( $percentage > 15 ) return '#f59e0b'; // Orange for medium
    if ( $percentage > 5 ) return '#3b82f6';  // Blue for moderate
    return '#10b981'; // Green for low/healthy
}

// ============================================================================
// EXISTING FUNCTIONS (UNCHANGED - KEEPING ORIGINAL STRUCTURE)
// ============================================================================

/**
 * Extract Token Analytics from DexScreener Data
 */
function solanawp_extract_token_analytics( $dexscreener_data ) {
    if ( !$dexscreener_data ) {
        return array(
            'available' => false,
            'message' => 'No token data available'
        );
    }

    return array(
        'available' => true,
        'price_info' => array(
            'price_usd' => $dexscreener_data['priceUsd'] ?? null,
            'price_native' => $dexscreener_data['priceNative'] ?? null,
            'liquidity_usd' => $dexscreener_data['liquidity']['usd'] ?? null,
            'market_cap' => $dexscreener_data['fdv'] ?? $dexscreener_data['marketCap'] ?? null
        ),
        'volume_info' => array(
            'volume_24h' => $dexscreener_data['volume']['h24'] ?? null,
            'volume_6h' => $dexscreener_data['volume']['h6'] ?? null,
            'volume_1h' => $dexscreener_data['volume']['h1'] ?? null,
            'transactions_24h' => ($dexscreener_data['txns']['h24']['buys'] ?? 0) + ($dexscreener_data['txns']['h24']['sells'] ?? 0)
        ),
        'price_changes' => array(
            'change_5m' => $dexscreener_data['priceChange']['m5'] ?? null,
            'change_1h' => $dexscreener_data['priceChange']['h1'] ?? null,
            'change_6h' => $dexscreener_data['priceChange']['h6'] ?? null,
            'change_24h' => $dexscreener_data['priceChange']['h24'] ?? null
        ),
        'trading_activity' => array(
            'buys_24h' => $dexscreener_data['txns']['h24']['buys'] ?? null,
            'sells_24h' => $dexscreener_data['txns']['h24']['sells'] ?? null,
            'buys_6h' => $dexscreener_data['txns']['h6']['buys'] ?? null,
            'sells_6h' => $dexscreener_data['txns']['h6']['sells'] ?? null,
            'buys_1h' => $dexscreener_data['txns']['h1']['buys'] ?? null,
            'sells_1h' => $dexscreener_data['txns']['h1']['sells'] ?? null
        ),
        'pair_info' => array(
            'pair_address' => $dexscreener_data['pairAddress'] ?? null,
            'dex_id' => $dexscreener_data['dexId'] ?? null,
            'pair_created_at' => $dexscreener_data['pairCreatedAt'] ?? null
        )
    );
}

/**
 * Fetch DexScreener Data (PRIMARY SOURCE)
 */
function solanawp_fetch_dexscreener_data( $address ) {
    try {
        $url = "https://api.dexscreener.com/latest/dex/tokens/{$address}";
        $response = solanawp_make_request( $url );

        if ( isset( $response['pairs'] ) && !empty( $response['pairs'] ) ) {
            $pair = $response['pairs'][0];
            $pair['token_address'] = $address;
            $pair['pair_count'] = count( $response['pairs'] );
            $pair['data_source'] = 'dexscreener';
            $pair['fetched_at'] = time();
            return $pair;
        }

        return null;
    } catch ( Exception $e ) {
        error_log( 'DexScreener API Error: ' . $e->getMessage() );
        return null;
    }
}

/**
 * Address Validation
 */
function solanawp_fetch_validation_data( $address ) {
    $address = trim( $address );
    $length = strlen( $address );

    if ( $length >= 32 && $length <= 44 && ctype_alnum( str_replace( array( '1', '2', '3', '4', '5', '6', '7', '8', '9' ), '', $address ) ) ) {
        try {
            $dexscreener_check = solanawp_check_dexscreener_token( $address );

            if ( $dexscreener_check['found'] ) {
                return array(
                    'valid' => true,
                    'isValid' => true,
                    'exists' => true,
                    'format' => 'Valid Solana token address',
                    'type' => 'Token (' . ( $dexscreener_check['symbol'] ?? 'Unknown' ) . ')',
                    'length' => $length,
                    'token_name' => $dexscreener_check['name'] ?? 'Unknown Token',
                    'verification_source' => 'DexScreener'
                );
            }
        } catch ( Exception $e ) {
            error_log( 'DexScreener validation error: ' . $e->getMessage() );
        }

        return array(
            'valid' => true,
            'isValid' => true,
            'exists' => null,
            'format' => 'Valid Solana address format',
            'type' => solanawp_get_address_type_simple( $address ),
            'length' => $length,
            'verification_source' => 'Format validation'
        );
    }

    return array(
        'valid' => false,
        'isValid' => false,
        'format' => 'Invalid format',
        'type' => 'Unknown',
        'message' => "Invalid Solana address format. Expected 32-44 characters.",
        'length' => $length
    );
}

function solanawp_get_address_type_simple( $address ) {
    $length = strlen( $address );

    if ( strpos( $address, 'pump' ) !== false ) {
        return 'Pump.fun Token';
    } elseif ( $length === 44 ) {
        return 'Token/Account Address';
    } elseif ( $length === 43 ) {
        return 'Account Address';
    } elseif ( $length <= 35 ) {
        return 'Program/System Account';
    }

    return 'Solana Address';
}

function solanawp_check_dexscreener_token( $address ) {
    $url = "https://api.dexscreener.com/latest/dex/tokens/{$address}";

    $args = array(
        'timeout' => 8,
        'headers' => array(
            'User-Agent' => 'SolanaWP/1.0'
        )
    );

    $response = wp_remote_get( $url, $args );

    if ( is_wp_error( $response ) ) {
        return array( 'found' => false, 'error' => $response->get_error_message() );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( isset( $data['pairs'] ) && !empty( $data['pairs'] ) ) {
        $first_pair = $data['pairs'][0];
        return array(
            'found' => true,
            'name' => $first_pair['baseToken']['name'] ?? 'Unknown Token',
            'symbol' => $first_pair['baseToken']['symbol'] ?? 'Unknown',
            'pair_count' => count( $data['pairs'] )
        );
    }

    return array( 'found' => false );
}

/**
 * Balance & Holdings
 */
function solanawp_fetch_balance_data( $address, $dexscreener_data = null ) {
    $balance_data = array(
        'sol_balance' => 0,
        'sol_balance_formatted' => '0 SOL',
        'sol_balance_usd' => '0.00',
        'token_count' => 0,
        'nft_count' => 0,
        'total_value_usd' => 0
    );

    try {
        if ( $dexscreener_data ) {
            $token_price = $dexscreener_data['priceUsd'] ?? 0;
            $balance_data['token_price'] = $token_price;
            $balance_data['market_cap'] = $dexscreener_data['fdv'] ?? 0;
            $balance_data['liquidity'] = $dexscreener_data['liquidity']['usd'] ?? 0;
            $balance_data['volume_24h'] = $dexscreener_data['volume']['h24'] ?? 0;
        }

        $solana_rpc_url = get_option( 'solanawp_solana_rpc_url' );
        if ( !empty( $solana_rpc_url ) ) {
            $payload = json_encode( array(
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getBalance',
                'params' => array( $address )
            ) );

            $response = solanawp_make_request( $solana_rpc_url, array(
                'method' => 'POST',
                'headers' => array( 'Content-Type' => 'application/json' ),
                'body' => $payload,
                'timeout' => 10
            ) );

            if ( isset( $response['result']['value'] ) ) {
                $sol_balance = solanawp_lamports_to_sol( $response['result']['value'] );
                $balance_data['sol_balance'] = $sol_balance;
                $balance_data['sol_balance_formatted'] = number_format( $sol_balance, 4 ) . ' SOL';
                $balance_data['sol_balance_usd'] = number_format( $sol_balance * 150, 2 );
            }
        }

        if ( !$dexscreener_data ) {
            $helius_api_key = get_option( 'solanawp_helius_api_key' );
            if ( !empty( $helius_api_key ) ) {
                $helius_url = "https://api.helius.xyz/v0/addresses/{$address}/balances?api-key={$helius_api_key}";
                $helius_response = solanawp_make_request( $helius_url, array( 'timeout' => 10 ) );

                if ( isset( $helius_response['tokens'] ) ) {
                    $balance_data['token_count'] = count( $helius_response['tokens'] );
                }

                if ( isset( $helius_response['nfts'] ) ) {
                    $balance_data['nft_count'] = count( $helius_response['nfts'] );
                }
            }
        }

    } catch ( Exception $e ) {
        error_log( 'Balance fetch error: ' . $e->getMessage() );
    }

    return $balance_data;
}

/**
 * Transaction Analysis
 */
function solanawp_fetch_transaction_data( $address, $dexscreener_data = null ) {
    $transaction_data = array(
        'total_transactions' => 0,
        'first_transaction' => 'Unknown',
        'last_transaction' => 'Unknown',
        'recent_transactions' => array(),
        'transaction_volume' => 0
    );

    try {
        if ( $dexscreener_data ) {
            $buys_24h = $dexscreener_data['txns']['h24']['buys'] ?? 0;
            $sells_24h = $dexscreener_data['txns']['h24']['sells'] ?? 0;
            $volume_24h = $dexscreener_data['volume']['h24'] ?? 0;

            $transaction_data['total_transactions'] = $buys_24h + $sells_24h;
            $transaction_data['buys_24h'] = $buys_24h;
            $transaction_data['sells_24h'] = $sells_24h;
            $transaction_data['volume_24h'] = number_format( $volume_24h, 2 );
            $transaction_data['buys_5m'] = $dexscreener_data['txns']['m5']['buys'] ?? 0;
            $transaction_data['sells_5m'] = $dexscreener_data['txns']['m5']['sells'] ?? 0;

            if ( isset( $dexscreener_data['pairCreatedAt'] ) ) {
                $creation_timestamp = $dexscreener_data['pairCreatedAt'] / 1000;
                $transaction_data['first_transaction'] = date( 'M j, Y', $creation_timestamp );
                $transaction_data['last_transaction'] = date( 'M j, Y', time() );
            }
        }

        $helius_api_key = get_option( 'solanawp_helius_api_key' );
        if ( !empty( $helius_api_key ) ) {
            $helius_url = "https://api.helius.xyz/v0/addresses/{$address}/transactions?api-key={$helius_api_key}&limit=10";
            $helius_response = solanawp_make_request( $helius_url, array( 'timeout' => 10 ) );

            if ( isset( $helius_response ) && is_array( $helius_response ) && !empty( $helius_response ) ) {
                if ( empty( $transaction_data['total_transactions'] ) ) {
                    $transaction_data['total_transactions'] = count( $helius_response );
                }

                $recent_txs = array();
                foreach ( array_slice( $helius_response, 0, 5 ) as $tx ) {
                    $recent_txs[] = array(
                        'type' => $tx['type'] ?? 'Transfer',
                        'signature' => isset( $tx['signature'] ) ? substr( $tx['signature'], 0, 8 ) . '...' : 'N/A',
                        'description' => $tx['description'] ?? 'Transaction',
                        'date' => isset( $tx['timestamp'] ) ? date( 'M j, Y H:i', $tx['timestamp'] ) : 'Unknown'
                    );
                }
                $transaction_data['recent_transactions'] = $recent_txs;

                if ( $transaction_data['last_transaction'] === 'Unknown' && !empty( $helius_response ) ) {
                    $latest_tx = $helius_response[0];
                    if ( isset( $latest_tx['timestamp'] ) ) {
                        $transaction_data['last_transaction'] = date( 'M j, Y', $latest_tx['timestamp'] );
                    }
                }
            }
        }

        if ( $transaction_data['first_transaction'] !== 'Unknown' &&
            $transaction_data['last_transaction'] !== 'Unknown' &&
            $transaction_data['first_transaction'] === $transaction_data['last_transaction'] ) {

            $transaction_data['last_transaction'] = date( 'M j, Y', time() );
        }

    } catch ( Exception $e ) {
        error_log( 'Transaction fetch error: ' . $e->getMessage() );
        $transaction_data['error'] = $e->getMessage();
    }

    return $transaction_data;
}

/**
 * Security Analysis
 */
function solanawp_fetch_security_data( $address, $dexscreener_data = null ) {
    $security_data = array(
        'risk_level' => 'Unknown',
        'risk_score' => 0,
        'known_scam' => array( 'text' => 'Unknown', 'isScam' => false ),
        'suspicious_activity' => array( 'text' => 'Unknown', 'found' => false ),
        'checks' => array()
    );

    try {
        $risk_factors = array();

        if ( $dexscreener_data ) {
            if ( isset( $dexscreener_data['pairCreatedAt'] ) ) {
                $creation_timestamp = $dexscreener_data['pairCreatedAt'] / 1000;
                $age_days = ( time() - $creation_timestamp ) / 86400;

                if ( $age_days < 1 ) {
                    $risk_factors[] = 'Very new token (< 1 day old)';
                    $security_data['age_risk'] = 'High';
                    $security_data['known_scam']['text'] = 'Very New Token';
                    $security_data['known_scam']['isScam'] = true;
                } elseif ( $age_days < 7 ) {
                    $risk_factors[] = 'New token (< 1 week old)';
                    $security_data['age_risk'] = 'Medium';
                    $security_data['known_scam']['text'] = 'New Token';
                } else {
                    $security_data['age_risk'] = 'Low';
                    $security_data['known_scam']['text'] = 'Established Token';
                }

                $security_data['token_age_days'] = round( $age_days, 1 );
            }

            $liquidity = $dexscreener_data['liquidity']['usd'] ?? 0;
            if ( $liquidity < 1000 ) {
                $risk_factors[] = 'Very low liquidity (< $1,000)';
                $security_data['liquidity_risk'] = 'High';
                $security_data['suspicious_activity']['text'] = 'Low Liquidity Detected';
                $security_data['suspicious_activity']['found'] = true;
            } elseif ( $liquidity < 10000 ) {
                $risk_factors[] = 'Low liquidity (< $10,000)';
                $security_data['liquidity_risk'] = 'Medium';
                $security_data['suspicious_activity']['text'] = 'Moderate Liquidity';
            } else {
                $security_data['liquidity_risk'] = 'Low';
                $security_data['suspicious_activity']['text'] = 'Good Liquidity';
            }

            $volume_24h = $dexscreener_data['volume']['h24'] ?? 0;
            if ( $volume_24h < 100 ) {
                $risk_factors[] = 'Very low trading volume';
                $security_data['volume_risk'] = 'High';
            } elseif ( $volume_24h < 1000 ) {
                $security_data['volume_risk'] = 'Medium';
            } else {
                $security_data['volume_risk'] = 'Low';
            }
        } else {
            $security_data['known_scam']['text'] = 'No Token Data';
            $security_data['suspicious_activity']['text'] = 'No Activity Data';
        }

        $high_risk_count = 0;
        $medium_risk_count = 0;

        foreach ( array( 'age_risk', 'liquidity_risk', 'volume_risk' ) as $risk_type ) {
            if ( isset( $security_data[$risk_type] ) ) {
                if ( $security_data[$risk_type] === 'High' ) $high_risk_count++;
                elseif ( $security_data[$risk_type] === 'Medium' ) $medium_risk_count++;
            }
        }

        if ( $high_risk_count >= 2 ) {
            $security_data['risk_level'] = 'High';
            $security_data['risk_score'] = 80 + ( $high_risk_count * 5 );
        } elseif ( $high_risk_count >= 1 || $medium_risk_count >= 2 ) {
            $security_data['risk_level'] = 'Medium';
            $security_data['risk_score'] = 40 + ( $high_risk_count * 15 ) + ( $medium_risk_count * 10 );
        } else {
            $security_data['risk_level'] = 'Low';
            $security_data['risk_score'] = $medium_risk_count * 15;
        }

        $security_data['risk_factors'] = $risk_factors;
        $security_data['checks'] = array(
            'Age Check' => isset( $security_data['token_age_days'] ) ? 'Completed' : 'No data',
            'Liquidity Check' => isset( $security_data['liquidity_risk'] ) ? 'Completed' : 'No data',
            'Volume Check' => isset( $security_data['volume_risk'] ) ? 'Completed' : 'No data'
        );

    } catch ( Exception $e ) {
        error_log( 'Security analysis error: ' . $e->getMessage() );
        $security_data['error'] = $e->getMessage();
    }

    return $security_data;
}

/**
 * Rug Pull Risk
 */
function solanawp_fetch_rugpull_data( $address, $dexscreener_data = null ) {
    $rugpull_data = array(
        'risk_level' => 'Unknown',
        'risk_percentage' => 0,
        'overall_score' => 0,
        'warning_signs' => array(),
        'safe_indicators' => array(),
        'volume_24h' => 'Unknown',
        'liquidity_locked' => array( 'text' => 'Unknown', 'color' => '#6b7280' ),
        'ownership_renounced' => array( 'text' => 'Unknown', 'color' => '#6b7280' ),
        'mint_authority' => array( 'text' => 'Unknown', 'color' => '#6b7280' ),
        'freeze_authority' => array( 'text' => 'Unknown', 'color' => '#6b7280' ),
        'token_distribution' => array()
    );

    try {
        $risk_score = 0;
        $warning_signs = array();
        $safe_indicators = array();

        if ( $dexscreener_data ) {
            $liquidity = $dexscreener_data['liquidity']['usd'] ?? 0;
            $volume_24h = $dexscreener_data['volume']['h24'] ?? 0;

            $rugpull_data['volume_24h'] = '$' . number_format( $volume_24h, 2 );

            if ( $liquidity < 5000 ) {
                $risk_score += 25;
                $warning_signs[] = 'Very low liquidity makes exit difficult';
                $rugpull_data['liquidity_locked']['text'] = 'Low Liquidity';
                $rugpull_data['liquidity_locked']['color'] = '#ef4444';
            } elseif ( $liquidity < 25000 ) {
                $risk_score += 15;
                $warning_signs[] = 'Moderate liquidity risk';
                $rugpull_data['liquidity_locked']['text'] = 'Moderate Liquidity';
                $rugpull_data['liquidity_locked']['color'] = '#f59e0b';
            } else {
                $safe_indicators[] = 'Good liquidity levels';
                $rugpull_data['liquidity_locked']['text'] = 'Good Liquidity';
                $rugpull_data['liquidity_locked']['color'] = '#10b981';
            }

            if ( $liquidity > 0 ) {
                $volume_liquidity_ratio = $volume_24h / $liquidity;
                if ( $volume_liquidity_ratio > 5 ) {
                    $risk_score += 20;
                    $warning_signs[] = 'Unusually high volume relative to liquidity';
                } elseif ( $volume_liquidity_ratio < 0.1 && $volume_24h > 0 ) {
                    $risk_score += 10;
                    $warning_signs[] = 'Very low trading activity';
                } else {
                    $safe_indicators[] = 'Reasonable trading volume';
                }
            }

            $rugpull_data['ownership_renounced']['text'] = 'Unable to verify';
            $rugpull_data['ownership_renounced']['color'] = '#f59e0b';

            $rugpull_data['mint_authority']['text'] = 'Unable to verify';
            $rugpull_data['mint_authority']['color'] = '#f59e0b';

            $rugpull_data['freeze_authority']['text'] = 'Unable to verify';
            $rugpull_data['freeze_authority']['color'] = '#f59e0b';

            $rugpull_data['token_distribution'] = array(
                array( 'label' => 'Public', 'percentage' => 60, 'color' => '#10b981' ),
                array( 'label' => 'Team', 'percentage' => 20, 'color' => '#f59e0b' ),
                array( 'label' => 'Marketing', 'percentage' => 15, 'color' => '#3b82f6' ),
                array( 'label' => 'Reserved', 'percentage' => 5, 'color' => '#6b7280' )
            );
        }

        if ( $risk_score >= 60 ) {
            $rugpull_data['risk_level'] = 'High';
            $rugpull_data['risk_percentage'] = min( $risk_score, 100 );
        } elseif ( $risk_score >= 30 ) {
            $rugpull_data['risk_level'] = 'Medium';
            $rugpull_data['risk_percentage'] = $risk_score;
        } else {
            $rugpull_data['risk_level'] = 'Low';
            $rugpull_data['risk_percentage'] = max( $risk_score, 10 );
        }

        $rugpull_data['overall_score'] = min( $risk_score, 100 );
        $rugpull_data['warning_signs'] = !empty( $warning_signs ) ? $warning_signs : array( 'No major warning signs detected' );
        $rugpull_data['safe_indicators'] = !empty( $safe_indicators ) ? $safe_indicators : array( 'Limited data available for safety assessment' );

    } catch ( Exception $e ) {
        error_log( 'Rug pull analysis error: ' . $e->getMessage() );
        $rugpull_data['error'] = $e->getMessage();
    }

    return $rugpull_data;
}

/**
 * Website & Social with X API Integration
 */
function solanawp_fetch_social_data( $address, $dexscreener_data = null ) {
    $social_data = array(
        'webInfo' => array(
            'website' => 'Not found',
            'registrationDate' => 'Unknown',
            'registrationCountry' => 'Unknown'
        ),
        'twitterInfo' => array(
            'handle' => 'Not found',
            'verified' => false,
            'raw_url' => '',
            'verificationType' => 'Unavailable',
            'verifiedFollowers' => 'Unavailable',
            'subscriptionType' => 'Unavailable',
            'followers' => 'Unavailable',
            'identityVerification' => 'Unavailable',
            'creationDate' => 'Unavailable'
        ),
        'telegramInfo' => array(
            'channel' => 'Not found',
            'raw_url' => ''
        ),
        'discordInfo' => array(
            'invite' => 'Not found',
            'serverName' => 'Unknown',
            'raw_url' => ''
        ),
        'githubInfo' => array(
            'repository' => 'Not found',
            'organization' => 'Unknown',
            'raw_url' => ''
        )
    );

    try {
        if ( $dexscreener_data ) {
            if ( isset( $dexscreener_data['info']['websites'] ) && !empty( $dexscreener_data['info']['websites'] ) ) {
                $primary_website = $dexscreener_data['info']['websites'][0]['url'] ?? $dexscreener_data['info']['websites'][0];
                if ( is_string( $primary_website ) ) {
                    $social_data['webInfo']['website'] = $primary_website;

                    $domain = solanawp_extract_domain_without_www( $primary_website );
                    if ( $domain ) {
                        $whois_data = solanawp_get_whois_registration_data( $domain );
                        if ( !isset( $whois_data['error'] ) ) {
                            $social_data['webInfo']['registrationDate'] = $whois_data['created_date'] ?? 'Unknown';

                            $registration_country = 'Unknown';
                            if ( !empty( $whois_data['registrant']['country'] ) ) {
                                $registration_country = $whois_data['registrant']['country'];
                            } elseif ( !empty( $whois_data['registrar']['country'] ) ) {
                                $registration_country = $whois_data['registrar']['country'];
                            }
                            $social_data['webInfo']['registrationCountry'] = $registration_country;
                        }
                    }
                }
            }

            if ( isset( $dexscreener_data['info']['socials'] ) && !empty( $dexscreener_data['info']['socials'] ) ) {
                foreach ( $dexscreener_data['info']['socials'] as $social ) {
                    $type = strtolower( $social['type'] ?? '' );
                    $url = $social['url'] ?? '';

                    switch ( $type ) {
                        case 'twitter':
                            $social_data['twitterInfo']['handle'] = $url;
                            $social_data['twitterInfo']['verified'] = false;
                            $social_data['twitterInfo']['raw_url'] = $url;

                            $twitter_username = solanawp_extract_twitter_username_from_url( $url );
                            if ( $twitter_username ) {
                                $x_api_data = solanawp_fetch_twitter_data_from_x_api( $twitter_username );
                                if ( $x_api_data ) {
                                    $social_data['twitterInfo']['verified'] = $x_api_data['verified'] ?? false;
                                    $mapped_data = solanawp_map_x_api_data_to_frontend( $x_api_data );
                                    $social_data['twitterInfo'] = array_merge( $social_data['twitterInfo'], $mapped_data );
                                }
                            }
                            break;

                        case 'telegram':
                            $social_data['telegramInfo']['channel'] = solanawp_extract_telegram_handle( $url );
                            $social_data['telegramInfo']['raw_url'] = $url;
                            break;

                        case 'discord':
                            $social_data['discordInfo']['invite'] = solanawp_extract_discord_invite( $url );
                            $social_data['discordInfo']['serverName'] = 'Discord Server';
                            $social_data['discordInfo']['raw_url'] = $url;
                            break;

                        case 'github':
                            $social_data['githubInfo']['repository'] = solanawp_extract_github_handle( $url );
                            $social_data['githubInfo']['organization'] = solanawp_extract_github_org( $url ) ?? 'Unknown';
                            $social_data['githubInfo']['raw_url'] = $url;
                            break;
                    }
                }
            }

            if ( isset( $dexscreener_data['info']['imageUrl'] ) ) {
                $social_data['token_image'] = $dexscreener_data['info']['imageUrl'];
            }
        }

    } catch ( Exception $e ) {
        error_log( 'Social data fetch error: ' . $e->getMessage() );
        $social_data['error'] = $e->getMessage();
    }

    return $social_data;
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function solanawp_format_number( $number ) {
    if ( $number >= 1000000000 ) {
        return round( $number / 1000000000, 1 ) . 'B';
    } elseif ( $number >= 1000000 ) {
        return round( $number / 1000000, 1 ) . 'M';
    } elseif ( $number >= 1000 ) {
        return round( $number / 1000, 1 ) . 'K';
    }
    return number_format( $number );
}

function solanawp_get_whois_registration_data( $domain ) {
    try {
        $whois_url = "https://hannisolwhois.vercel.app/{$domain}";
        $response = solanawp_make_request( $whois_url );

        if ( !is_array( $response ) ) {
            return array(
                'error' => 'Invalid WHOIS response format',
                'domain' => $domain
            );
        }

        $creation_date = null;
        if ( isset( $response['domain']['created_date'] ) && !empty( $response['domain']['created_date'] ) ) {
            $full_datetime = $response['domain']['created_date'];
            $creation_date = substr( $full_datetime, 0, 10 );
        }

        $registration_country = 'Unknown';
        if ( !empty( $response['registrant']['country'] ) ) {
            $registration_country = $response['registrant']['country'];
        } elseif ( !empty( $response['administrative']['country'] ) ) {
            $registration_country = $response['administrative']['country'];
        }

        $result = array(
            'domain' => $domain,
            'created_date' => $creation_date,
            'registrant' => array(
                'country' => $registration_country
            ),
            'registrar' => array(
                'name' => $response['registrar']['name'] ?? 'Unknown',
                'country' => solanawp_get_country_from_registrar( $response['registrar']['name'] ?? '' )
            ),
            'raw_data' => $response
        );

        return $result;

    } catch ( Exception $e ) {
        return array(
            'domain' => $domain,
            'error' => $e->getMessage(),
            'created_date' => null,
            'registrant' => array( 'country' => null ),
            'registrar' => array( 'name' => 'Unknown', 'country' => 'Unknown' )
        );
    }
}

function solanawp_calculate_final_scores( $validation, $balance, $transactions, $security, $rugpull, $social ) {
    $trust_score = 50;
    $activity_score = 50;
    $overall_score = 50;
    $recommendation = 'Analysis completed.';

    try {
        if ( isset( $security['risk_level'] ) ) {
            switch ( $security['risk_level'] ) {
                case 'Low':
                    $trust_score += 30;
                    break;
                case 'Medium':
                    $trust_score += 10;
                    break;
                case 'High':
                    $trust_score -= 20;
                    break;
            }
        }

        if ( isset( $rugpull['risk_level'] ) ) {
            switch ( $rugpull['risk_level'] ) {
                case 'Low':
                    $trust_score += 20;
                    break;
                case 'Medium':
                    $trust_score -= 10;
                    break;
                case 'High':
                    $trust_score -= 30;
                    break;
            }
        }

        if ( isset( $transactions['total_transactions'] ) ) {
            $tx_count = $transactions['total_transactions'];
            if ( $tx_count > 1000 ) {
                $activity_score += 40;
            } elseif ( $tx_count > 100 ) {
                $activity_score += 20;
            } elseif ( $tx_count > 10 ) {
                $activity_score += 10;
            } else {
                $activity_score -= 20;
            }
        }

        if ( isset( $balance['token_count'] ) ) {
            $token_count = $balance['token_count'];
            if ( $token_count > 5 ) {
                $activity_score += 10;
            } elseif ( $token_count > 0 ) {
                $activity_score += 5;
            }
        }

        $social_count = 0;
        if ( isset( $social['twitterInfo']['handle'] ) && $social['twitterInfo']['handle'] !== 'Not found' ) {
            $social_count++;
        }
        if ( isset( $social['telegramInfo']['channel'] ) && $social['telegramInfo']['channel'] !== 'Not found' ) {
            $social_count++;
        }
        if ( isset( $social['webInfo']['website'] ) && $social['webInfo']['website'] !== 'Not found' ) {
            $social_count++;
        }

        $trust_score += ( $social_count * 5 );
        $overall_score = round( ( $trust_score + $activity_score ) / 2 );

        $trust_score = max( 0, min( 100, $trust_score ) );
        $activity_score = max( 0, min( 100, $activity_score ) );
        $overall_score = max( 0, min( 100, $overall_score ) );

        if ( $overall_score >= 80 ) {
            $recommendation = 'This address shows strong indicators of legitimacy and activity. Generally considered safe for interaction.';
        } elseif ( $overall_score >= 60 ) {
            $recommendation = 'This address shows moderate signs of legitimacy. Exercise standard caution when interacting.';
        } elseif ( $overall_score >= 40 ) {
            $recommendation = 'This address has mixed indicators. Proceed with caution and do additional research.';
        } else {
            $recommendation = 'This address shows concerning indicators. Exercise extreme caution or avoid interaction.';
        }

    } catch ( Exception $e ) {
        error_log( 'Score calculation error: ' . $e->getMessage() );
    }

    return array(
        'trust_score' => $trust_score,
        'activity_score' => $activity_score,
        'overall_score' => $overall_score,
        'recommendation' => $recommendation
    );
}

function solanawp_extract_twitter_handle( $url ) {
    if ( preg_match( '/twitter\.com\/([a-zA-Z0-9_]+)/', $url, $matches ) ) {
        return '@' . $matches[1];
    }
    return 'Not found';
}

function solanawp_extract_telegram_handle( $url ) {
    if ( preg_match( '/t\.me\/([a-zA-Z0-9_]+)/', $url, $matches ) ) {
        return '@' . $matches[1];
    }
    return 'Not found';
}

function solanawp_extract_discord_invite( $url ) {
    if ( preg_match( '/discord\.gg\/([a-zA-Z0-9]+)/', $url, $matches ) ) {
        return 'discord.gg/' . $matches[1];
    } elseif ( preg_match( '/discord\.com\/invite\/([a-zA-Z0-9]+)/', $url, $matches ) ) {
        return 'discord.gg/' . $matches[1];
    }
    return 'Not found';
}

function solanawp_extract_domain_without_www( $url ) {
    $parsed = parse_url( $url );
    $host = $parsed['host'] ?? $url;
    $domain = preg_replace( '/^www\./', '', $host );
    return $domain;
}

function solanawp_extract_github_handle( $url ) {
    if ( preg_match( '/github\.com\/([a-zA-Z0-9_\-\.]+)/', $url, $matches ) ) {
        return $matches[1];
    }
    return 'Not found';
}

function solanawp_extract_github_org($github_url) {
    if (preg_match('/github\.com\/([a-zA-Z0-9_\-\.]+)\//', $github_url, $matches)) {
        return $matches[1];
    }
    return null;
}

function solanawp_get_country_from_registrar( $registrar_name ) {
    $registrar_countries = array(
        'HOSTINGER operations, UAB' => 'Lithuania',
        'GoDaddy.com, LLC' => 'United States',
        'GoDaddy' => 'United States',
        'Namecheap, Inc.' => 'United States',
        'Namecheap' => 'United States',
        'Google LLC' => 'United States',
        'Amazon Registrar, Inc.' => 'United States',
        'Cloudflare, Inc.' => 'United States'
    );

    if ( isset( $registrar_countries[$registrar_name] ) ) {
        return $registrar_countries[$registrar_name];
    }

    foreach ( $registrar_countries as $registrar => $country ) {
        if ( stripos( $registrar_name, $registrar ) !== false ) {
            return $country;
        }
    }

    return 'Registrar: ' . $registrar_name;
}

function solanawp_make_request( $url, $args = array() ) {
    $defaults = array(
        'timeout' => 30,
        'headers' => array()
    );

    $args = wp_parse_args( $args, $defaults );

    if ( ! solanawp_check_rate_limit( $url ) ) {
        throw new Exception( 'Rate limit exceeded. Please try again later.' );
    }

    $response = wp_remote_request( $url, $args );

    if ( is_wp_error( $response ) ) {
        throw new Exception( 'Request failed: ' . $response->get_error_message() );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        throw new Exception( 'Invalid JSON response: ' . json_last_error_msg() );
    }

    return $data;
}

function solanawp_check_rate_limit( $url ) {
    $rate_limit_enabled = get_option( 'solanawp_enable_rate_limiting', true );

    if ( ! $rate_limit_enabled ) {
        return true;
    }

    $rate_limit = get_option( 'solanawp_rate_limit', 100 );
    $rate_window = 60; // 1 minute window

    $transient_key = 'solanawp_rate_' . md5( parse_url( $url, PHP_URL_HOST ) );
    $current_count = get_transient( $transient_key );

    if ( $current_count === false ) {
        set_transient( $transient_key, 1, $rate_window );
        return true;
    }

    if ( $current_count >= $rate_limit ) {
        return false;
    }

    set_transient( $transient_key, $current_count + 1, $rate_window );
    return true;
}

function solanawp_get_client_ip() {
    $ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );

    foreach ( $ip_keys as $key ) {
        if ( array_key_exists( $key, $_SERVER ) === true ) {
            foreach ( explode( ',', $_SERVER[$key] ) as $ip ) {
                $ip = trim( $ip );
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                    return $ip;
                }
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function solanawp_log_request( $address, $user_ip, $status, $error = null ) {
    $logging_enabled = get_option( 'solanawp_enable_logging', true );

    if ( ! $logging_enabled ) {
        return;
    }

    $log_message = sprintf(
        'SolanaWP Check: %s | IP: %s | Status: %s | Error: %s',
        $address,
        $user_ip,
        $status,
        $error ?? 'none'
    );

    error_log( $log_message );
}

function solanawp_lamports_to_sol( $lamports ) {
    return $lamports / 1000000000;
}

function solanawp_get_cache( $key ) {
    if ( ! get_option( 'solanawp_enable_caching', true ) ) {
        return false;
    }

    return get_transient( 'solanawp_' . md5( $key ) );
}

function solanawp_set_cache( $key, $data, $expiration = 300 ) {
    if ( ! get_option( 'solanawp_enable_caching', true ) ) {
        return false;
    }

    return set_transient( 'solanawp_' . md5( $key ), $data, $expiration );
}

?>
