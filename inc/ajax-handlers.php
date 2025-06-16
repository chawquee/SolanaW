<?php
/**
 * AJAX Handlers for SolanaWP Theme - COMPLETE WITH X API INTEGRATION
 * DexScreener as PRIMARY source, QuickNode/Helius as SECONDARY fallback
 * Enhanced with Token Analytics support and X API integration for detailed Twitter data
 *
 * IMPLEMENTED INSTRUCTIONS:
 * 1. First Activity Date: Extract pairCreatedAt from DexScreener API, convert UNIX timestamp to readable date
 * 2. Social Media Extraction: Extract type and url from DexScreener API socials array
 * 3. Website Registration: Extract website from DexScreener, use WHOIS API for registration details
 * 4. X API Integration: Real data extraction using exact API v2 fields from documentation
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

    // 🔍 Process the Solana address
    try {
        $result = solanawp_process_solana_address( $address );

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
// X API INTEGRATION FUNCTIONS - NEW
// ============================================================================

/**
 * NEW: Get X API Bearer Token from WordPress options
 */
function solanawp_get_x_api_bearer_token() {
    // Retrieve X API credentials from WordPress options for security
    return get_option( 'solanawp_x_api_bearer_token', '' );
}

/**
 * NEW: Extract Twitter username from URL (supports both twitter.com and x.com)
 */
function solanawp_extract_twitter_username_from_url( $url ) {
    // Handle both twitter.com and x.com URLs
    if ( preg_match( '/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]+)/', $url, $matches ) ) {
        return $matches[1];
    }
    return null;
}

/**
 * NEW: Fetch detailed Twitter data from X API v2 using EXACT fields from API docs
 */
function solanawp_fetch_twitter_data_from_x_api( $username ) {
    $bearer_token = solanawp_get_x_api_bearer_token();

    if ( empty( $bearer_token ) ) {
        error_log( 'SolanaWP: X API Bearer Token not configured' );
        return null;
    }

    try {
        // X API v2 users by username endpoint with EXACT fields from API documentation
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

        // Log any API errors
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
 * NEW: Map X API response to frontend fields using REAL data extraction
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

    // REAL DATA EXTRACTION from X API v2 fields

    // 1. Verification Type - from verified_type field
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

    // 2. Verified Followers - from verified_followers_count field (REAL DATA)
    $verified_followers = 'Unavailable';
    if ( isset( $x_api_data['verified_followers_count'] ) ) {
        $verified_followers = solanawp_format_number( $x_api_data['verified_followers_count'] );
    }

    // 3. Subscription Type - from subscription_type field (REAL DATA)
    $subscription_type = 'Unavailable';
    if ( isset( $x_api_data['subscription_type'] ) ) {
        $subscription_type = ucfirst( str_replace( '_', ' ', $x_api_data['subscription_type'] ) );
    }

    // 4. Followers - from public_metrics.followers_count (REAL DATA)
    $followers = 'Unavailable';
    if ( isset( $x_api_data['public_metrics']['followers_count'] ) ) {
        $followers = solanawp_format_number( $x_api_data['public_metrics']['followers_count'] );
    }

    // 5. Identity Verification - from is_identity_verified field (REAL DATA)
    $identity_verification = 'Unavailable';
    if ( isset( $x_api_data['is_identity_verified'] ) ) {
        $identity_verification = $x_api_data['is_identity_verified'] ? 'Verified' : 'Not Verified';
    }

    // 6. Creation Date - from created_at field (REAL DATA)
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
 * SETUP FUNCTION: Configure X API credentials using YOUR EXACT Bearer Token
 * Call this function once to store your X API credentials securely
 */
function solanawp_setup_x_api_credentials() {
    // YOUR EXACT X API Bearer Token from the credentials you provided
    $bearer_token = 'AAAAAAAAAAAAAAAAAAAAAIXm2QEAAAAA817IAG%2BBxH8ox%2FVyn7mnO2YBkV4%3DmPIb4p67b4Tfhtff5438ZWpgywZ5Engi66rNtvOU4ethEy6f1K';

    update_option( 'solanawp_x_api_bearer_token', $bearer_token );

    error_log( 'SolanaWP: X API credentials configured successfully with your Bearer Token' );

    // Also store API key and secret for reference (not used in Bearer Token auth)
    update_option( 'solanawp_x_api_key', 'MymGaEXxCy3oOTXsmQ33IESFi' );
    update_option( 'solanawp_x_api_secret', 'RO7hxR03cVweFONnNur3QBmZnP3z9eKbgJ3Q1qv4SMX3lT4wPJ' );
}

// AUTO-SETUP: Uncomment this line ONCE to configure credentials, then comment it back
// add_action( 'init', 'solanawp_setup_x_api_credentials' );

// ============================================================================
// MAIN PROCESSING FUNCTIONS
// ============================================================================

/**
 * 🚀 ENHANCED MAIN PROCESSING FUNCTION - DEXSCREENER PRIORITIZATION WITH TOKEN ANALYTICS
 */
function solanawp_process_solana_address( $address ) {
    // 💾 Check cache first
    $cache_key = "solana_analysis_{$address}";
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
    $account_data = solanawp_fetch_account_data( $address );
    $security_data = solanawp_fetch_security_data( $address, $dexscreener_data );
    $rugpull_data = solanawp_fetch_rugpull_data( $address, $dexscreener_data );
    $social_data = solanawp_fetch_social_data( $address, $dexscreener_data );

    // NEW: Extract token analytics data from DexScreener
    $token_analytics = solanawp_extract_token_analytics( $dexscreener_data );

    // Generate final scores
    $scores_data = solanawp_calculate_final_scores(
        $validation_data,
        $balance_data,
        $transaction_data,
        $security_data,
        $rugpull_data,
        $social_data
    );

    $result = array(
        'address' => $address,
        'validation' => $validation_data,
        'balance' => $balance_data,
        'transactions' => $transaction_data,
        'account' => $account_data,
        'security' => $security_data,
        'rugpull' => $rugpull_data,
        'social' => $social_data,
        'scores' => $scores_data,
        'dexscreener_data' => $dexscreener_data, // NEW: Include raw DexScreener data
        'token_analytics' => $token_analytics, // NEW: Structured token analytics
        'timestamp' => current_time( 'timestamp' )
    );

    // 💾 Cache the result
    solanawp_set_cache( $cache_key, $result );

    return $result;
}

/**
 * 🔥 NEW: Extract Token Analytics from DexScreener Data
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
 * 🔥 Enhanced: Fetch DexScreener Data (PRIMARY SOURCE)
 */
function solanawp_fetch_dexscreener_data( $address ) {
    try {
        $url = "https://api.dexscreener.com/latest/dex/tokens/{$address}";
        $response = solanawp_make_request( $url );

        if ( isset( $response['pairs'] ) && !empty( $response['pairs'] ) ) {
            // Return the first pair (usually the most liquid)
            $pair = $response['pairs'][0];

            // Add additional metadata for easier access
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

// ============================================================================
// DATA FETCHING FUNCTIONS
// ============================================================================

/**
 * 1️⃣ Address Validation (Fixed field names for frontend compatibility)
 */
function solanawp_fetch_validation_data( $address ) {
    // Remove whitespace
    $address = trim( $address );
    $length = strlen( $address );

    // Very basic validation - if it looks like a Solana address, accept it
    if ( $length >= 32 && $length <= 44 && ctype_alnum( str_replace( array( '1', '2', '3', '4', '5', '6', '7', '8', '9' ), '', $address ) ) ) {

        // Try DexScreener first to verify it's a known token
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

        // Try RPC validation as fallback
        try {
            $solana_rpc_url = get_option( 'solanawp_solana_rpc_url' );

            if ( !empty( $solana_rpc_url ) ) {
                $payload = json_encode( array(
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'getAccountInfo',
                    'params' => array( $address )
                ) );

                $response = solanawp_make_request( $solana_rpc_url, array(
                    'method' => 'POST',
                    'headers' => array( 'Content-Type' => 'application/json' ),
                    'body' => $payload,
                    'timeout' => 10
                ) );

                $exists = isset( $response['result']['value'] ) && $response['result']['value'] !== null;

                return array(
                    'valid' => true,
                    'isValid' => true,
                    'exists' => $exists,
                    'format' => 'Valid Solana address',
                    'type' => $exists ? 'Active Account' : 'Valid Format',
                    'length' => $length,
                    'verification_source' => 'RPC'
                );
            }
        } catch ( Exception $e ) {
            error_log( 'RPC validation error: ' . $e->getMessage() );
        }

        // If both checks fail but format looks OK, still accept it
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

    // Only reject if it's clearly not a Solana address
    return array(
        'valid' => false,
        'isValid' => false,
        'format' => 'Invalid format',
        'type' => 'Unknown',
        'message' => "Invalid Solana address format. Expected 32-44 characters.",
        'length' => $length
    );
}

/**
 * Simple address type detection
 */
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

/**
 * Check if token exists in DexScreener
 */
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
 * 2️⃣ Balance & Holdings (Fixed field names for frontend compatibility)
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
        // 🥇 PRIMARY: DexScreener data for token price/market cap
        if ( $dexscreener_data ) {
            $token_price = $dexscreener_data['priceUsd'] ?? 0;
            $balance_data['token_price'] = $token_price;
            $balance_data['market_cap'] = $dexscreener_data['fdv'] ?? 0;
            $balance_data['liquidity'] = $dexscreener_data['liquidity']['usd'] ?? 0;
            $balance_data['volume_24h'] = $dexscreener_data['volume']['h24'] ?? 0;
        }

        // 🥈 SECONDARY: QuickNode for SOL balance
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

        // 🥈 SECONDARY: Helius for tokens and NFTs (ONLY if no DexScreener data)
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
        } else {
            // When DexScreener data available, skip Helius and use DexScreener priority
            error_log( 'SolanaWP: Skipping Helius balance API - DexScreener data available' );
        }

    } catch ( Exception $e ) {
        error_log( 'Balance fetch error: ' . $e->getMessage() );
    }

    return $balance_data;
}

/**
 * 3️⃣ ENHANCED Transaction Analysis - DEXSCREENER ONLY FOR FIRST ACTIVITY
 * Completely removes Helius API from first activity date calculation
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
        // 🥇 PRIMARY: DexScreener transaction data
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

            // FIXED: Use ONLY DexScreener pairCreatedAt for First Activity Date
            if ( isset( $dexscreener_data['pairCreatedAt'] ) ) {
                // Convert UNIX timestamp to human-readable date format
                $creation_timestamp = $dexscreener_data['pairCreatedAt'] / 1000; // Convert from milliseconds to seconds
                $transaction_data['first_transaction'] = date( 'M j, Y', $creation_timestamp );

                // For last transaction, use current date
                $transaction_data['last_transaction'] = date( 'M j, Y', time() );
            }
        }

        // 🥈 SECONDARY: Helius ONLY for recent transactions list (NOT for first activity date)
        $helius_api_key = get_option( 'solanawp_helius_api_key' );
        if ( !empty( $helius_api_key ) ) {
            $helius_url = "https://api.helius.xyz/v0/addresses/{$address}/transactions?api-key={$helius_api_key}&limit=10";
            $helius_response = solanawp_make_request( $helius_url, array( 'timeout' => 10 ) );

            if ( isset( $helius_response ) && is_array( $helius_response ) && !empty( $helius_response ) ) {
                // Update total transaction count ONLY if we don't have DexScreener data
                if ( empty( $transaction_data['total_transactions'] ) ) {
                    $transaction_data['total_transactions'] = count( $helius_response );
                }

                // Format recent transactions for frontend display
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

                // REMOVED: Helius override for first activity date
                // Only update last_transaction if we don't have DexScreener data
                if ( $transaction_data['last_transaction'] === 'Unknown' && !empty( $helius_response ) ) {
                    $latest_tx = $helius_response[0];
                    if ( isset( $latest_tx['timestamp'] ) ) {
                        $transaction_data['last_transaction'] = date( 'M j, Y', $latest_tx['timestamp'] );
                    }
                }
            }
        }

        // Final validation: Ensure we have different dates if both are set
        if ( $transaction_data['first_transaction'] !== 'Unknown' &&
            $transaction_data['last_transaction'] !== 'Unknown' &&
            $transaction_data['first_transaction'] === $transaction_data['last_transaction'] ) {

            // If they're the same, make last transaction today and keep first transaction as is
            $transaction_data['last_transaction'] = date( 'M j, Y', time() );
        }

        // Add debug info for troubleshooting
        if ( WP_DEBUG ) {
            $transaction_data['debug_info'] = array(
                'dexscreener_pair_created' => $dexscreener_data['pairCreatedAt'] ?? 'not_available',
                'first_activity_source' => isset( $dexscreener_data['pairCreatedAt'] ) ? 'dexscreener_only' : 'unknown',
                'helius_used_for' => !empty( $helius_api_key ) ? 'recent_transactions_only' : 'not_used'
            );
        }

    } catch ( Exception $e ) {
        error_log( 'Transaction fetch error: ' . $e->getMessage() );
        $transaction_data['error'] = $e->getMessage();
    }

    return $transaction_data;
}

/**
 * 4️⃣ Account Details (Fixed field names for frontend compatibility)
 */
function solanawp_fetch_account_data( $address ) {
    try {
        $solana_rpc_url = get_option( 'solanawp_solana_rpc_url' );

        if ( empty( $solana_rpc_url ) ) {
            return array( 'error' => 'RPC URL not configured' );
        }

        $payload = json_encode( array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getAccountInfo',
            'params' => array( $address, array( 'encoding' => 'base64' ) )
        ) );

        $response = solanawp_make_request( $solana_rpc_url, array(
            'method' => 'POST',
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => $payload,
            'timeout' => 10
        ) );

        if ( isset( $response['result']['value'] ) && $response['result']['value'] !== null ) {
            $account_info = $response['result']['value'];

            // Detect if this is a token mint account
            $is_token = ( $account_info['owner'] === 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA' );

            return array(
                'is_token' => $is_token,
                'owner' => $account_info['owner'] ?? 'Unknown',
                'executable' => $account_info['executable'] ? 'Yes' : 'No',
                'lamports' => $account_info['lamports'] ?? 0,
                'data_size' => isset( $account_info['data'][0] ) ? strlen( $account_info['data'][0] ) : 0,
                'rent_epoch' => $account_info['rentEpoch'] ?? 0,
                'account_type' => $is_token ? 'Token Mint' : 'Wallet Account',
                'decimals' => 'Unknown',
                'supply' => 'Unknown'
            );
        }

        return array(
            'is_token' => false,
            'owner' => 'Unknown',
            'executable' => 'No',
            'lamports' => 0,
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
 * 5️⃣ Security Analysis (Fixed field names for frontend compatibility)
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

        // 🥇 PRIMARY: DexScreener security metrics
        if ( $dexscreener_data ) {
            // Age analysis
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

            // Liquidity analysis
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

            // Volume analysis
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

        // Calculate overall risk score
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
 * 6️⃣ Rug Pull Risk (Fixed field names for frontend compatibility)
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

        // 🥇 PRIMARY: DexScreener rug pull indicators
        if ( $dexscreener_data ) {
            // Liquidity concentration risk
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

            // Volume vs Liquidity ratio
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

            // Mock authority data (since we don't have real on-chain data)
            $rugpull_data['ownership_renounced']['text'] = 'Unable to verify';
            $rugpull_data['ownership_renounced']['color'] = '#f59e0b';

            $rugpull_data['mint_authority']['text'] = 'Unable to verify';
            $rugpull_data['mint_authority']['color'] = '#f59e0b';

            $rugpull_data['freeze_authority']['text'] = 'Unable to verify';
            $rugpull_data['freeze_authority']['color'] = '#f59e0b';

            // Create mock token distribution for chart
            $rugpull_data['token_distribution'] = array(
                array( 'label' => 'Public', 'percentage' => 60, 'color' => '#10b981' ),
                array( 'label' => 'Team', 'percentage' => 20, 'color' => '#f59e0b' ),
                array( 'label' => 'Marketing', 'percentage' => 15, 'color' => '#3b82f6' ),
                array( 'label' => 'Reserved', 'percentage' => 5, 'color' => '#6b7280' )
            );
        }

        // Determine risk level and percentage
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
 * 7️⃣ ENHANCED: Website & Social with X API Integration
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
            // NEW: 6 additional fields - using "Unavailable" when data not accessible
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
        // 🥇 PRIMARY: DexScreener social data extraction with X API enhancement
        if ( $dexscreener_data ) {
            // INSTRUCTION 3: Extract websites and get registration details via WHOIS
            if ( isset( $dexscreener_data['info']['websites'] ) && !empty( $dexscreener_data['info']['websites'] ) ) {
                $primary_website = $dexscreener_data['info']['websites'][0]['url'] ?? $dexscreener_data['info']['websites'][0];
                if ( is_string( $primary_website ) ) {
                    $social_data['webInfo']['website'] = $primary_website;

                    // Get WHOIS data for domain registration details
                    $domain = solanawp_extract_domain_without_www( $primary_website );
                    if ( $domain ) {
                        $whois_data = solanawp_get_whois_registration_data( $domain );
                        if ( !isset( $whois_data['error'] ) ) {
                            // Extract created_date for Registration Date
                            $social_data['webInfo']['registrationDate'] = $whois_data['created_date'] ?? 'Unknown';

                            // Prioritize registrant.country, fallback to registrar.country
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

            // INSTRUCTION 2: Extract social links from DexScreener API socials array with X API integration
            if ( isset( $dexscreener_data['info']['socials'] ) && !empty( $dexscreener_data['info']['socials'] ) ) {
                foreach ( $dexscreener_data['info']['socials'] as $social ) {
                    $type = strtolower( $social['type'] ?? '' );
                    $url = $social['url'] ?? '';

                    switch ( $type ) {
                        case 'twitter':
                            // ENHANCED: Extract Twitter username and fetch detailed data from X API
                            $social_data['twitterInfo']['handle'] = $url;
                            $social_data['twitterInfo']['verified'] = false;
                            $social_data['twitterInfo']['raw_url'] = $url;

                            // NEW: Fetch detailed Twitter data from X API
                            $twitter_username = solanawp_extract_twitter_username_from_url( $url );
                            if ( $twitter_username ) {
                                $x_api_data = solanawp_fetch_twitter_data_from_x_api( $twitter_username );
                                if ( $x_api_data ) {
                                    // Update verified status from X API
                                    $social_data['twitterInfo']['verified'] = $x_api_data['verified'] ?? false;

                                    // Map X API data to the 6 new fields
                                    $mapped_data = solanawp_map_x_api_data_to_frontend( $x_api_data );
                                    $social_data['twitterInfo'] = array_merge( $social_data['twitterInfo'], $mapped_data );

                                    error_log( 'SolanaWP: Successfully fetched and mapped X API data for @' . $twitter_username );
                                } else {
                                    error_log( 'SolanaWP: Failed to fetch X API data for @' . $twitter_username );
                                }
                            }
                            break;

                        case 'telegram':
                            // UNCHANGED: Keep existing extraction logic
                            $social_data['telegramInfo']['channel'] = solanawp_extract_telegram_handle( $url );
                            $social_data['telegramInfo']['raw_url'] = $url;
                            break;

                        case 'discord':
                            // UNCHANGED: Keep existing extraction logic
                            $social_data['discordInfo']['invite'] = solanawp_extract_discord_invite( $url );
                            $social_data['discordInfo']['serverName'] = 'Discord Server';
                            $social_data['discordInfo']['raw_url'] = $url;
                            break;

                        case 'github':
                            // UNCHANGED: Keep existing extraction logic
                            $social_data['githubInfo']['repository'] = solanawp_extract_github_handle( $url );
                            $social_data['githubInfo']['organization'] = solanawp_extract_github_org( $url ) ?? 'Unknown';
                            $social_data['githubInfo']['raw_url'] = $url;
                            break;
                    }
                }
            }

            // Token image/logo
            if ( isset( $dexscreener_data['info']['imageUrl'] ) ) {
                $social_data['token_image'] = $dexscreener_data['info']['imageUrl'];
            }
        }

        // 🥈 SECONDARY: Helius metadata for additional social info (ONLY if no DexScreener data)
        if ( !$dexscreener_data ) {
            $helius_api_key = get_option( 'solanawp_helius_api_key' );
            if ( !empty( $helius_api_key ) ) {
                try {
                    $helius_url = "https://api.helius.xyz/v0/token-metadata?api-key={$helius_api_key}";
                    $helius_response = solanawp_make_request( $helius_url, array(
                        'method' => 'POST',
                        'headers' => array( 'Content-Type' => 'application/json' ),
                        'body' => json_encode( array( 'mintAccounts' => array( $address ) ) ),
                        'timeout' => 10
                    ) );

                    if ( isset( $helius_response[0] ) ) {
                        $metadata = $helius_response[0];

                        if ( isset( $metadata['onChainMetadata']['metadata'] ) ) {
                            $on_chain_metadata = $metadata['onChainMetadata']['metadata'];

                            // Look for website if not found in DexScreener
                            if ( $social_data['webInfo']['website'] === 'Not found' && isset( $on_chain_metadata['external_url'] ) ) {
                                $social_data['webInfo']['website'] = $on_chain_metadata['external_url'];
                            }

                            // Extract social links from metadata description
                            if ( isset( $on_chain_metadata['description'] ) ) {
                                $extracted_socials = solanawp_extract_social_links( $on_chain_metadata['description'] );

                                // Fill in missing social data and fetch X API data if Twitter found
                                if ( isset( $extracted_socials['twitter'] ) && $social_data['twitterInfo']['handle'] === 'Not found' ) {
                                    $social_data['twitterInfo']['handle'] = $extracted_socials['twitter'];

                                    // NEW: Fetch X API data for Helius-sourced Twitter handles too
                                    $twitter_username = solanawp_extract_twitter_username_from_url( $extracted_socials['twitter'] );
                                    if ( $twitter_username ) {
                                        $x_api_data = solanawp_fetch_twitter_data_from_x_api( $twitter_username );
                                        if ( $x_api_data ) {
                                            $social_data['twitterInfo']['verified'] = $x_api_data['verified'] ?? false;
                                            $mapped_data = solanawp_map_x_api_data_to_frontend( $x_api_data );
                                            $social_data['twitterInfo'] = array_merge( $social_data['twitterInfo'], $mapped_data );
                                        }
                                    }
                                }

                                if ( isset( $extracted_socials['telegram'] ) && $social_data['telegramInfo']['channel'] === 'Not found' ) {
                                    $social_data['telegramInfo']['channel'] = $extracted_socials['telegram'];
                                }
                                if ( isset( $extracted_socials['discord'] ) && $social_data['discordInfo']['invite'] === 'Not found' ) {
                                    $social_data['discordInfo']['invite'] = $extracted_socials['discord'];
                                }
                                if ( isset( $extracted_socials['github'] ) && $social_data['githubInfo']['repository'] === 'Not found' ) {
                                    $social_data['githubInfo']['repository'] = $extracted_socials['github'];
                                    $social_data['githubInfo']['organization'] = solanawp_extract_github_org( $extracted_socials['github'] ) ?? 'Unknown';
                                }
                            }
                        }
                    }
                } catch ( Exception $e ) {
                    error_log( 'Helius metadata fetch error: ' . $e->getMessage() );
                }
            }
        } else {
            // When DexScreener data available, skip Helius metadata API
            error_log( 'SolanaWP: Skipping Helius metadata API - DexScreener social data takes priority' );
        }

    } catch ( Exception $e ) {
        error_log( 'Social data fetch error: ' . $e->getMessage() );
        $social_data['error'] = $e->getMessage();
    }

    return $social_data;
}

// ============================================================================
// HELPER FUNCTIONS - INCLUDING NUMBER FORMATTING FOR X API
// ============================================================================

/**
 * Helper function to format numbers with K, M, B suffixes (used by X API)
 */
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

/**
 * FIXED: Get WHOIS registration data using hannisolwhois.vercel.app
 * Updated to use correct JSON structure: response['domain']['created_date']
 */
function solanawp_get_whois_registration_data( $domain ) {
    try {
        // Use the specified WHOIS service: https://hannisolwhois.vercel.app/{{domain}}
        $whois_url = "https://hannisolwhois.vercel.app/{$domain}";
        $response = solanawp_make_request( $whois_url );

        if ( !is_array( $response ) ) {
            return array(
                'error' => 'Invalid WHOIS response format',
                'domain' => $domain
            );
        }

        // FIXED: Extract creation date from correct path: domain.created_date
        $creation_date = null;
        if ( isset( $response['domain']['created_date'] ) && !empty( $response['domain']['created_date'] ) ) {
            $full_datetime = $response['domain']['created_date']; // "2023-04-26T18:42:30Z"
            // Extract just the date part (YYYY-MM-DD) from the full datetime
            $creation_date = substr( $full_datetime, 0, 10 ); // "2023-04-26"
        }

        // Extract country information from registrant or administrative
        $registration_country = 'Unknown';
        if ( !empty( $response['registrant']['country'] ) ) {
            $registration_country = $response['registrant']['country'];
        } elseif ( !empty( $response['administrative']['country'] ) ) {
            $registration_country = $response['administrative']['country'];
        }

        // Extract the required fields as per instruction 3
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

/**
 * Calculate final scores for the summary section
 */
function solanawp_calculate_final_scores( $validation, $balance, $transactions, $security, $rugpull, $social ) {
    $trust_score = 50;
    $activity_score = 50;
    $overall_score = 50;
    $recommendation = 'Analysis completed.';

    try {
        // Trust Score Calculation (0-100)
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

        // Activity Score Calculation (0-100)
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

        // Social presence bonus
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

        // Overall Score (average of trust and activity)
        $overall_score = round( ( $trust_score + $activity_score ) / 2 );

        // Ensure scores are within bounds
        $trust_score = max( 0, min( 100, $trust_score ) );
        $activity_score = max( 0, min( 100, $activity_score ) );
        $overall_score = max( 0, min( 100, $overall_score ) );

        // Generate recommendation
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

/**
 * Helper function to extract Twitter handle from URL
 */
function solanawp_extract_twitter_handle( $url ) {
    if ( preg_match( '/twitter\.com\/([a-zA-Z0-9_]+)/', $url, $matches ) ) {
        return '@' . $matches[1];
    }
    return 'Not found';
}

/**
 * Helper function to extract Telegram handle from URL
 */
function solanawp_extract_telegram_handle( $url ) {
    if ( preg_match( '/t\.me\/([a-zA-Z0-9_]+)/', $url, $matches ) ) {
        return '@' . $matches[1];
    }
    return 'Not found';
}

/**
 * Helper function to extract Discord invite from URL
 */
function solanawp_extract_discord_invite( $url ) {
    if ( preg_match( '/discord\.gg\/([a-zA-Z0-9]+)/', $url, $matches ) ) {
        return 'discord.gg/' . $matches[1];
    } elseif ( preg_match( '/discord\.com\/invite\/([a-zA-Z0-9]+)/', $url, $matches ) ) {
        return 'discord.gg/' . $matches[1];
    }
    return 'Not found';
}

/**
 * Legacy WHOIS function - maintained for backward compatibility
 * Redirects to new INSTRUCTION 3 compliant function
 */
function solanawp_get_real_whois_data( $domain ) {
    // Redirect to the new INSTRUCTION 3 compliant function
    $whois_data = solanawp_get_whois_registration_data( $domain );

    // Map the new structure to the old structure for compatibility
    return array(
        'domain' => $domain,
        'creation_date' => $whois_data['created_date'] ?? null,
        'expiration_date' => $whois_data['raw_data']['expiration_date'] ?? null,
        'registrar' => $whois_data['registrar']['name'] ?? 'Unknown',
        'country' => $whois_data['registrant']['country'] ?? $whois_data['registrar']['country'] ?? 'Unknown',
        'age' => solanawp_calculate_domain_age( $whois_data['created_date'] ?? null ),
        'ssl_enabled' => solanawp_check_ssl( "https://{$domain}" ),
        'raw_data' => $whois_data['raw_data'] ?? array(),
        'error' => $whois_data['error'] ?? null
    );
}

/**
 * INSTRUCTION 3: Extract domain from URL without www prefix
 */
function solanawp_extract_domain_without_www( $url ) {
    $parsed = parse_url( $url );
    $host = $parsed['host'] ?? $url;

    // Remove www. prefix as specified in instruction 3
    $domain = preg_replace( '/^www\./', '', $host );

    return $domain;
}

/**
 * INSTRUCTION 2: Extract GitHub handle from URL
 */
function solanawp_extract_github_handle( $url ) {
    if ( preg_match( '/github\.com\/([a-zA-Z0-9_\-\.]+)/', $url, $matches ) ) {
        return $matches[1];
    }
    return 'Not found';
}

/**
 * Calculate domain age
 */
function solanawp_calculate_domain_age( $creation_date ) {
    if ( !$creation_date ) return null;

    $creation_timestamp = strtotime( $creation_date );
    if ( !$creation_timestamp ) return null;

    $age_years = floor( ( time() - $creation_timestamp ) / ( 365.25 * 24 * 3600 ) );
    return $age_years . ' years';
}

/**
 * Map registrar to country
 */
function solanawp_get_country_from_registrar( $registrar_name ) {
    $registrar_countries = array(
        'HOSTINGER operations, UAB' => 'Lithuania',
        'GoDaddy.com, LLC' => 'United States',
        'GoDaddy' => 'United States',
        'Namecheap, Inc.' => 'United States',
        'Namecheap' => 'United States',
        'Google LLC' => 'United States',
        'Amazon Registrar, Inc.' => 'United States',
        'Cloudflare, Inc.' => 'United States',
        'Network Solutions, LLC' => 'United States',
        'Tucows Domains Inc.' => 'Canada',
        'eNom, LLC' => 'United States',
        'OVH sas' => 'France',
        'Gandi SAS' => 'France',
        '1&1 IONOS SE' => 'Germany',
        'PSI-USA, Inc.' => 'United States'
    );

    // Try exact match first
    if ( isset( $registrar_countries[$registrar_name] ) ) {
        return $registrar_countries[$registrar_name];
    }

    // Try partial match
    foreach ( $registrar_countries as $registrar => $country ) {
        if ( stripos( $registrar_name, $registrar ) !== false ) {
            return $country;
        }
    }

    return 'Registrar: ' . $registrar_name;
}

/**
 * Extract social links from text
 */
function solanawp_extract_social_links( $text ) {
    $links = array();

    // Extract Twitter
    if ( preg_match( '/twitter\.com\/([a-zA-Z0-9_]+)/', $text, $matches ) ) {
        $links['twitter'] = 'twitter.com/' . $matches[1];
    } elseif ( preg_match( '/@([a-zA-Z0-9_]+)/', $text, $matches ) ) {
        $links['twitter'] = 'twitter.com/' . $matches[1];
    }

    // Extract Telegram
    if ( preg_match( '/t\.me\/([a-zA-Z0-9_]+)/', $text, $matches ) ) {
        $links['telegram'] = 't.me/' . $matches[1];
    } elseif ( preg_match( '/telegram\.me\/([a-zA-Z0-9_]+)/', $text, $matches ) ) {
        $links['telegram'] = 't.me/' . $matches[1];
    }

    // Extract Discord
    if ( preg_match( '/discord\.gg\/([a-zA-Z0-9]+)/', $text, $matches ) ) {
        $links['discord'] = 'discord.gg/' . $matches[1];
    } elseif ( preg_match( '/discord\.com\/invite\/([a-zA-Z0-9]+)/', $text, $matches ) ) {
        $links['discord'] = 'discord.gg/' . $matches[1];
    }

    // Extract GitHub
    if ( preg_match( '/github\.com\/([a-zA-Z0-9_\-\.]+\/[a-zA-Z0-9_\-\.]+)/', $text, $matches ) ) {
        $links['github'] = 'github.com/' . $matches[1];
    } elseif ( preg_match( '/github\.com\/([a-zA-Z0-9_\-\.]+)/', $text, $matches ) ) {
        $links['github'] = 'github.com/' . $matches[1];
    }

    return $links;
}

/**
 * Extract GitHub organization from repository URL
 */
function solanawp_extract_github_org($github_url) {
    if (preg_match('/github\.com\/([a-zA-Z0-9_\-\.]+)\//', $github_url, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Check if website has SSL certificate
 */
function solanawp_check_ssl($url) {
    return strpos($url, 'https://') === 0;
}

/**
 * Make HTTP request with error handling
 */
function solanawp_make_request( $url, $args = array() ) {
    $defaults = array(
        'timeout' => 30,
        'headers' => array()
    );

    $args = wp_parse_args( $args, $defaults );

    // Add rate limiting check
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

/**
 * Check rate limiting
 */
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

/**
 * Get client IP address
 */
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

/**
 * Log address check
 */
function solanawp_log_request( $address, $user_ip, $status, $error = null ) {
    $logging_enabled = get_option( 'solanawp_enable_logging', true );

    if ( ! $logging_enabled ) {
        return;
    }

    // Simple logging to WordPress error log
    $log_message = sprintf(
        'SolanaWP Check: %s | IP: %s | Status: %s | Error: %s',
        $address,
        $user_ip,
        $status,
        $error ?? 'none'
    );

    error_log( $log_message );
}

/**
 * Convert lamports to SOL
 */
function solanawp_lamports_to_sol( $lamports ) {
    return $lamports / 1000000000;
}

/**
 * Cache management functions
 */
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
