<?php
/**
 * AJAX Handlers for SolanaWP Theme - ENHANCED WITH STANDALONE TOKEN DISTRIBUTION + MORALIS API
 * PHASE 1: Enhanced Account Details with Free Solana RPC API
 * PHASE 2: Authority Risk Analysis with RugCheck API
 * PHASE 3: Token Distribution Analysis with Alchemy + Moralis API (enhanced)
 * NEW: Moralis API integration for enhanced token distribution data
 * NEW: Standalone Token Distribution section with 4 subsections
 * REMOVED: Security Analysis section (deleted)
 * DexScreener as PRIMARY source, Alchemy/Helius as SECONDARY fallback
 * Enhanced with Token Analytics support and X API integration for detailed Twitter data
 * UPDATED: Added Moralis API for Top 50/100/250/500 holders, Growth Analysis, Categories
 * UPDATED: Free Solana RPC API endpoint for Account Details
 * UPDATED: Enhanced Total Holders extraction from Moralis API for new frontend requirements
 * UPDATED: Improved data structure for informational banners and proper formatting
 * UPDATED: Dynamic Time Period Sub-sections Based on Activity Duration
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

    // 🔍 Process the Solana address with ENHANCED functionality + RugCheck + Moralis
    try {
        $result = solanawp_process_solana_address_enhanced_with_moralis( $address );

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
        'solanawp_moralis_api_key' => sanitize_text_field( $_POST['moralis_api_key'] ?? '' ),
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
// X API INTEGRATION FUNCTIONS (Keep existing)
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
// ENHANCED MAIN PROCESSING FUNCTIONS - WITH RUGCHECK + MORALIS API INTEGRATION
// ============================================================================

/**
 * ENHANCED MAIN PROCESSING FUNCTION - WITH RUGCHECK + MORALIS API INTEGRATION
 * REMOVED: Security Analysis (deleted section)
 * UPDATED: Enhanced Total Holders extraction and data structure optimization
 * UPDATED: Dynamic Time Period Sub-sections Based on Activity Duration
 */
/**
 * ENHANCED MAIN PROCESSING FUNCTION - WITH RUGCHECK + MORALIS API INTEGRATION
 * REMOVED: Security Analysis (deleted section)
 * UPDATED: Enhanced Total Holders extraction and data structure optimization
 * UPDATED: Dynamic Time Period Sub-sections Based on Activity Duration
 * UPDATED: Integration of dynamic time periods with Moralis holders growth filtering
 */
function solanawp_process_solana_address_enhanced_with_moralis( $address ) {
    // 💾 Check cache first
    $cache_key = "solana_analysis_rugcheck_moralis_enhanced_{$address}";
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
    $social_data = solanawp_fetch_social_data( $address, $dexscreener_data );

    // PHASE 1: Enhanced Account Data (Free Solana RPC)
    $enhanced_account_data = solanawp_fetch_enhanced_account_data( $address );

    // PHASE 3: Token Distribution Data (Alchemy API - keep existing)
    $distribution_data = solanawp_fetch_token_distribution_data( $address );

    // NEW: Enhanced RugCheck API Integration
    $rugcheck_data = solanawp_fetch_enhanced_rugcheck_data( $address );

    // NEW: Dynamic Time Periods calculation based on activity duration
    $dynamic_time_periods = solanawp_calculate_dynamic_time_periods( $transaction_data );

    // NEW: Enhanced Moralis API Integration with dynamic period filtering
    $moralis_data = solanawp_fetch_moralis_token_data_with_dynamic_periods( $address, $dynamic_time_periods );

    // Token Analytics
    $token_analytics = solanawp_extract_token_analytics( $dexscreener_data );

    // Calculate scores (REMOVED security_data parameter)
    $scores_data = solanawp_calculate_final_scores_without_security_enhanced(
        $validation_data,
        $balance_data,
        $transaction_data,
        $rugcheck_data,
        $social_data
    );

    $result = array(
        'address' => $address,
        'validation' => $validation_data,
        'balance' => $balance_data,
        'transactions' => $transaction_data,
        'account' => $enhanced_account_data, // PHASE 1: Enhanced account details
        // REMOVED: 'security' => $security_data, (Security Analysis deleted)
        'rugpull' => $rugcheck_data, // Legacy field for compatibility
        'rugcheck_data' => $rugcheck_data, // NEW: Enhanced RugCheck API data
        'moralis_data' => $moralis_data, // NEW: Enhanced Moralis API data for standalone token distribution with dynamic filtering
        'social' => $social_data,
        'scores' => $scores_data,
        'dexscreener_data' => $dexscreener_data,
        'token_analytics' => $token_analytics,
        'distribution_analysis' => $distribution_data, // PHASE 3: Token distribution data
        'dynamic_time_periods' => $dynamic_time_periods, // NEW: Dynamic time periods for holders growth
        'timestamp' => current_time( 'timestamp' )
    );

    // 💾 Cache the result
    solanawp_set_cache( $cache_key, $result );

    return $result;
}
// ============================================================================
// NEW: DYNAMIC TIME PERIODS CALCULATION FUNCTION
// ============================================================================

/**
 * Calculate Dynamic Time Periods Based on Activity Duration
 *
 * @param array $transaction_data Transaction data containing first and last activity
 * @return array Dynamic time periods configuration
 */
/**
 * Calculate Dynamic Time Periods Based on Activity Duration
 *
 * @param array $transaction_data Transaction data containing first and last activity
 * @return array Dynamic time periods configuration
 */
function solanawp_calculate_dynamic_time_periods( $transaction_data ) {
    try {
        // Extract first and last activity dates
        $first_activity = $transaction_data['first_transaction'] ?? 'Unknown';
        $last_activity = $transaction_data['last_transaction'] ?? 'Unknown';

        // Default periods if dates are not available
        $default_periods = array(
            'periods' => array('5m', '1h', '6h', '24h', '3 days', '7 days', '30 days'),
            'period_keys' => array('5m', '1h', '6h', '24h', '3d', '7d', '30d'),
            'dt_hours' => 0,
            'first_activity' => $first_activity,
            'last_activity' => $last_activity,
            'calculation_method' => 'default_all_periods'
        );

        if ( $first_activity === 'Unknown' || $last_activity === 'Unknown' ||
            $first_activity === $last_activity ) {
            error_log( 'SolanaWP: Using default time periods - insufficient date data' );
            return $default_periods;
        }

        // Convert dates to timestamps
        $first_timestamp = strtotime( $first_activity );
        $last_timestamp = strtotime( $last_activity );

        if ( $first_timestamp === false || $last_timestamp === false ) {
            error_log( 'SolanaWP: Invalid date format, using default periods' );
            return $default_periods;
        }

        // Calculate Dt in hours
        $dt_seconds = $last_timestamp - $first_timestamp;
        $dt_hours = $dt_seconds / 3600; // Convert to hours

        error_log( "SolanaWP: Activity duration calculated - Dt: {$dt_hours} hours" );

        // Dynamic Sub-section Display Logic with period keys mapping
        $periods = array();
        $period_keys = array();

        if ( $dt_hours > (5/60) && $dt_hours <= 1 ) {
            // 5 minutes < Dt ≤ 1 hour
            $periods = array('5m', '1h');
            $period_keys = array('5m', '1h');
            $calculation_method = 'range_5m_to_1h';
        } elseif ( $dt_hours >= 1 && $dt_hours <= 6 ) {
            // 1 hour ≤ Dt ≤ 6 hours
            $periods = array('5m', '1h', '6h');
            $period_keys = array('5m', '1h', '6h');
            $calculation_method = 'range_1h_to_6h';
        } elseif ( $dt_hours > 6 && $dt_hours <= 24 ) {
            // 6 hours < Dt ≤ 24 hours
            $periods = array('5m', '1h', '6h', '24h');
            $period_keys = array('5m', '1h', '6h', '24h');
            $calculation_method = 'range_6h_to_24h';
        } elseif ( $dt_hours > 24 && $dt_hours <= 72 ) {
            // 24 hours < Dt ≤ 72 hours (3 days)
            $periods = array('5m', '1h', '6h', '24h', '3 days');
            $period_keys = array('5m', '1h', '6h', '24h', '3d');
            $calculation_method = 'range_24h_to_72h';
        } elseif ( $dt_hours > 72 && $dt_hours <= 168 ) {
            // 72 hours < Dt ≤ 168 hours (7 days)
            $periods = array('5m', '1h', '6h', '24h', '3 days', '7 days');
            $period_keys = array('5m', '1h', '6h', '24h', '3d', '7d');
            $calculation_method = 'range_72h_to_168h';
        } elseif ( $dt_hours > 168 && $dt_hours <= 720 ) {
            // 168 hours < Dt ≤ 720 hours (30 days)
            $periods = array('5m', '1h', '6h', '24h', '3 days', '7 days', '30 days');
            $period_keys = array('5m', '1h', '6h', '24h', '3d', '7d', '30d');
            $calculation_method = 'range_168h_to_720h';
        } else {
            // Outside defined ranges, show all periods
            $periods = array('5m', '1h', '6h', '24h', '3 days', '7 days', '30 days');
            $period_keys = array('5m', '1h', '6h', '24h', '3d', '7d', '30d');
            $calculation_method = 'outside_ranges_all_periods';
        }

        $result = array(
            'periods' => $periods,
            'period_keys' => $period_keys,
            'dt_hours' => round( $dt_hours, 2 ),
            'dt_days' => round( $dt_hours / 24, 2 ),
            'first_activity' => $first_activity,
            'last_activity' => $last_activity,
            'first_timestamp' => $first_timestamp,
            'last_timestamp' => $last_timestamp,
            'calculation_method' => $calculation_method,
            'periods_count' => count( $periods )
        );

        error_log( "SolanaWP: Dynamic periods calculated - Method: {$calculation_method}, Periods: " . implode( ', ', $periods ) );

        return $result;

    } catch ( Exception $e ) {
        error_log( 'SolanaWP: Error calculating dynamic time periods: ' . $e->getMessage() );

        return array(
            'periods' => array('5m', '1h', '6h', '24h', '3 days', '7 days', '30 days'),
            'period_keys' => array('5m', '1h', '6h', '24h', '3d', '7d', '30d'),
            'dt_hours' => 0,
            'first_activity' => $first_activity ?? 'Unknown',
            'last_activity' => $last_activity ?? 'Unknown',
            'calculation_method' => 'error_fallback',
            'error' => $e->getMessage()
        );
    }
}

// ============================================================================
// NEW: ENHANCED MORALIS API INTEGRATION FUNCTIONS - UPDATED WITH IMPROVED DATA STRUCTURE
// ============================================================================

/**
 * NEW: Enhanced Fetch Moralis Token Data for Standalone Token Distribution Analysis
 * UPDATED: Improved Total Holders extraction and enhanced data validation
 * UPDATED: Better error handling and data structure optimization for frontend requirements
 */
function solanawp_fetch_moralis_token_data( $address ) {
    try {
        // Use the provided API key and correct endpoint
        $moralis_api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6ImE5NzdiOGVjLTA5OTctNGI2My1hZmNlLTY5YWNkZjNhMGFjZSIsIm9yZ0lkIjoiNDU0ODYzIiwidXNlcklkIjoiNDY3OTk0IiwidHlwZUlkIjoiNmU5YTg4ZjQtYTc3Zi00ODc2LWI0OGYtM2E1M2IxOTI3NmRhIiwidHlwZSI6IlBST0pFQ1QiLCJpYXQiOjE3NTAzNjY3NTIsImV4cCI6NDkwNjEyNjc1Mn0.pE-UnXUgg8NHWfogpwP7SpjNGESX9oVvLnHFuKHQVYQ';

        // Correct Moralis endpoint for token holders
        $holders_url = "https://solana-gateway.moralis.io/token/mainnet/holders/{$address}";

        error_log( "SolanaWP: Fetching enhanced Moralis data from: {$holders_url}" );

        // Fetch token holders data with enhanced error handling
        $holders_response = solanawp_fetch_moralis_endpoint(
            $holders_url,
            $moralis_api_key
        );

        // Process and enhance the data with improved structure
        $enhanced_data = solanawp_process_moralis_data(
            $holders_response,
            $address
        );

        // UPDATED: Additional validation for Total Holders extraction
        if ( isset( $enhanced_data['totalHolders'] ) && $enhanced_data['totalHolders'] > 0 ) {
            error_log( 'SolanaWP: Successfully extracted Total Holders from Moralis: ' . $enhanced_data['totalHolders'] );
        } else {
            error_log( 'SolanaWP: Warning - Total Holders not found in Moralis response for: ' . $address );
        }

        error_log( 'SolanaWP: Successfully fetched and processed enhanced Moralis data for: ' . $address );

        return $enhanced_data;

    } catch ( Exception $e ) {
        error_log( 'SolanaWP: Enhanced Moralis API exception: ' . $e->getMessage() );
        return solanawp_get_default_moralis_data();
    }
}

/**
 * Helper function to fetch data from Moralis API endpoints
 * UPDATED: Enhanced error handling and timeout management
 */
function solanawp_fetch_moralis_endpoint( $url, $api_key ) {
    $response = wp_remote_get( $url, array(
        'timeout' => 25, // Increased timeout for better reliability
        'headers' => array(
            'Accept' => 'application/json',
            'X-API-Key' => $api_key,
            'User-Agent' => 'SolanaWP/2.1-Enhanced-Moralis'
        )
    ) );

    if ( is_wp_error( $response ) ) {
        throw new Exception( 'Enhanced Moralis API request failed: ' . $response->get_error_message() );
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    if ( $response_code !== 200 ) {
        $body = wp_remote_retrieve_body( $response );
        error_log( "SolanaWP: Enhanced Moralis API returned HTTP {$response_code}, Body: " . $body );
        throw new Exception( "Enhanced Moralis API returned HTTP {$response_code}" );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        throw new Exception( 'Enhanced Moralis API returned invalid JSON: ' . json_last_error_msg() );
    }

    return $data;
}

/**
 * Process and enhance Moralis data for the standalone token distribution section
 * UPDATED: Enhanced Total Holders extraction with multiple fallback methods
 * UPDATED: Improved data structure for frontend banners and formatting requirements
 * UPDATED: Better validation and data consistency checks
 */
/**
 * Process and enhance Moralis data for the standalone token distribution section
 * UPDATED: Enhanced Total Holders extraction with multiple fallback methods
 * UPDATED: Improved data structure for frontend banners and formatting requirements
 * UPDATED: Better validation and data consistency checks
 * UPDATED: Dynamic filtering of holders growth data based on allowed time periods
 */
function solanawp_process_moralis_data( $moralis_response, $address, $allowed_period_keys = null ) {
    $processed_data = array(
        'concentration' => array(),
        'holdersGrowth' => array(),
        'holdersCategories' => array(),
        'metadata' => array(),
        'totalHolders' => 0,
        'data_source' => 'moralis_enhanced_v2',
        'fetched_at' => time(),
        'address' => $address
    );

    try {
        // ENHANCED: Multiple methods to extract Total Holders with validation
        $total_holders_extracted = false;

        // Method 1: Direct totalHolders field
        if ( isset( $moralis_response['totalHolders'] ) && is_numeric( $moralis_response['totalHolders'] ) ) {
            $processed_data['totalHolders'] = intval( $moralis_response['totalHolders'] );
            $total_holders_extracted = true;
            error_log( 'SolanaWP: Total Holders extracted via Method 1 (direct): ' . $processed_data['totalHolders'] );
        }

        // Method 2: Calculate from holders array if available
        if ( !$total_holders_extracted && isset( $moralis_response['holders'] ) && is_array( $moralis_response['holders'] ) ) {
            $processed_data['totalHolders'] = count( $moralis_response['holders'] );
            $total_holders_extracted = true;
            error_log( 'SolanaWP: Total Holders extracted via Method 2 (count): ' . $processed_data['totalHolders'] );
        }

        // Method 3: Extract from pagination metadata if available
        if ( !$total_holders_extracted && isset( $moralis_response['total'] ) && is_numeric( $moralis_response['total'] ) ) {
            $processed_data['totalHolders'] = intval( $moralis_response['total'] );
            $total_holders_extracted = true;
            error_log( 'SolanaWP: Total Holders extracted via Method 3 (total): ' . $processed_data['totalHolders'] );
        }

        // ENHANCED: Extract concentration metrics with improved validation
        if ( isset( $moralis_response['holderSupply'] ) && is_array( $moralis_response['holderSupply'] ) ) {
            $holder_supply = $moralis_response['holderSupply'];

            $processed_data['concentration'] = array(
                'top_50_percentage' => isset( $holder_supply['top50']['supplyPercent'] ) ?
                    floatval( $holder_supply['top50']['supplyPercent'] ) : 0,
                'top_100_percentage' => isset( $holder_supply['top100']['supplyPercent'] ) ?
                    floatval( $holder_supply['top100']['supplyPercent'] ) : 0,
                'top_250_percentage' => isset( $holder_supply['top250']['supplyPercent'] ) ?
                    floatval( $holder_supply['top250']['supplyPercent'] ) : 0,
                'top_500_percentage' => isset( $holder_supply['top500']['supplyPercent'] ) ?
                    floatval( $holder_supply['top500']['supplyPercent'] ) : 0
            );

            error_log( 'SolanaWP: Concentration metrics extracted successfully' );
        } else {
            // Provide default concentration structure
            $processed_data['concentration'] = array(
                'top_50_percentage' => 0,
                'top_100_percentage' => 0,
                'top_250_percentage' => 0,
                'top_500_percentage' => 0
            );
        }

        // ENHANCED: Extract holders growth analysis with improved data handling and dynamic filtering
        if ( isset( $moralis_response['holderChange'] ) && is_array( $moralis_response['holderChange'] ) ) {
            $holder_change = $moralis_response['holderChange'];

            // Complete holders growth data structure
            $all_holders_growth = array(
                'change_5m' => isset( $holder_change['5min']['change'] ) ?
                    intval( $holder_change['5min']['change'] ) : 0,
                'percent_5m' => isset( $holder_change['5min']['changePercent'] ) ?
                    floatval( $holder_change['5min']['changePercent'] ) : 0,
                'change_1h' => isset( $holder_change['1h']['change'] ) ?
                    intval( $holder_change['1h']['change'] ) : 0,
                'percent_1h' => isset( $holder_change['1h']['changePercent'] ) ?
                    floatval( $holder_change['1h']['changePercent'] ) : 0,
                'change_6h' => isset( $holder_change['6h']['change'] ) ?
                    intval( $holder_change['6h']['change'] ) : 0,
                'percent_6h' => isset( $holder_change['6h']['changePercent'] ) ?
                    floatval( $holder_change['6h']['changePercent'] ) : 0,
                'change_24h' => isset( $holder_change['24h']['change'] ) ?
                    intval( $holder_change['24h']['change'] ) : 0,
                'percent_24h' => isset( $holder_change['24h']['changePercent'] ) ?
                    floatval( $holder_change['24h']['changePercent'] ) : 0,
                'change_3d' => isset( $holder_change['3d']['change'] ) ?
                    intval( $holder_change['3d']['change'] ) : 0,
                'percent_3d' => isset( $holder_change['3d']['changePercent'] ) ?
                    floatval( $holder_change['3d']['changePercent'] ) : 0,
                'change_7d' => isset( $holder_change['7d']['change'] ) ?
                    intval( $holder_change['7d']['change'] ) : 0,
                'percent_7d' => isset( $holder_change['7d']['changePercent'] ) ?
                    floatval( $holder_change['7d']['changePercent'] ) : 0,
                'change_30d' => isset( $holder_change['30d']['change'] ) ?
                    intval( $holder_change['30d']['change'] ) : 0,
                'percent_30d' => isset( $holder_change['30d']['changePercent'] ) ?
                    floatval( $holder_change['30d']['changePercent'] ) : 0
            );

            // NEW: Dynamic filtering based on allowed time periods
            if ( $allowed_period_keys && is_array( $allowed_period_keys ) ) {
                $filtered_holders_growth = array();

                // Map display periods to data keys
                $period_mapping = array(
                    '5m' => array('change_5m', 'percent_5m'),
                    '1h' => array('change_1h', 'percent_1h'),
                    '6h' => array('change_6h', 'percent_6h'),
                    '24h' => array('change_24h', 'percent_24h'),
                    '3d' => array('change_3d', 'percent_3d'),
                    '7d' => array('change_7d', 'percent_7d'),
                    '30d' => array('change_30d', 'percent_30d')
                );

                // Only include data for allowed periods
                foreach ( $allowed_period_keys as $period_key ) {
                    if ( isset( $period_mapping[$period_key] ) ) {
                        $change_key = $period_mapping[$period_key][0];
                        $percent_key = $period_mapping[$period_key][1];

                        if ( isset( $all_holders_growth[$change_key] ) ) {
                            $filtered_holders_growth[$change_key] = $all_holders_growth[$change_key];
                        }
                        if ( isset( $all_holders_growth[$percent_key] ) ) {
                            $filtered_holders_growth[$percent_key] = $all_holders_growth[$percent_key];
                        }
                    }
                }

                $processed_data['holdersGrowth'] = $filtered_holders_growth;
                error_log( 'SolanaWP: Holders growth analysis filtered for periods: ' . implode( ', ', $allowed_period_keys ) );
            } else {
                // Use all periods if no filtering specified
                $processed_data['holdersGrowth'] = $all_holders_growth;
                error_log( 'SolanaWP: Holders growth analysis extracted successfully (all periods)' );
            }
        } else {
            // Provide default growth structure
            $processed_data['holdersGrowth'] = array(
                'change_5m' => 0, 'percent_5m' => 0,
                'change_1h' => 0, 'percent_1h' => 0,
                'change_6h' => 0, 'percent_6h' => 0,
                'change_24h' => 0, 'percent_24h' => 0,
                'change_3d' => 0, 'percent_3d' => 0,
                'change_7d' => 0, 'percent_7d' => 0,
                'change_30d' => 0, 'percent_30d' => 0
            );
        }

        // ENHANCED: Extract holders categories with validation
        if ( isset( $moralis_response['holderDistribution'] ) && is_array( $moralis_response['holderDistribution'] ) ) {
            $holder_distribution = $moralis_response['holderDistribution'];

            $processed_data['holdersCategories'] = array(
                'whales' => isset( $holder_distribution['whales'] ) ?
                    intval( $holder_distribution['whales'] ) : 0,
                'sharks' => isset( $holder_distribution['sharks'] ) ?
                    intval( $holder_distribution['sharks'] ) : 0,
                'dolphins' => isset( $holder_distribution['dolphins'] ) ?
                    intval( $holder_distribution['dolphins'] ) : 0,
                'fish' => isset( $holder_distribution['fish'] ) ?
                    intval( $holder_distribution['fish'] ) : 0,
                'octopus' => isset( $holder_distribution['octopus'] ) ?
                    intval( $holder_distribution['octopus'] ) : 0,
                'crabs' => isset( $holder_distribution['crabs'] ) ?
                    intval( $holder_distribution['crabs'] ) : 0,
                'shrimps' => isset( $holder_distribution['shrimps'] ) ?
                    intval( $holder_distribution['shrimps'] ) : 0
            );

            error_log( 'SolanaWP: Holders categories extracted successfully' );
        } else {
            // Provide default categories structure
            $processed_data['holdersCategories'] = array(
                'whales' => 0, 'sharks' => 0, 'dolphins' => 0, 'fish' => 0,
                'octopus' => 0, 'crabs' => 0, 'shrimps' => 0
            );
        }

        // ENHANCED: Extract holders by acquisition if available
        if ( isset( $moralis_response['holdersByAcquisition'] ) && is_array( $moralis_response['holdersByAcquisition'] ) ) {
            $processed_data['holdersByAcquisition'] = array(
                'swap' => isset( $moralis_response['holdersByAcquisition']['swap'] ) ?
                    intval( $moralis_response['holdersByAcquisition']['swap'] ) : 0,
                'transfer' => isset( $moralis_response['holdersByAcquisition']['transfer'] ) ?
                    intval( $moralis_response['holdersByAcquisition']['transfer'] ) : 0,
                'airdrop' => isset( $moralis_response['holdersByAcquisition']['airdrop'] ) ?
                    intval( $moralis_response['holdersByAcquisition']['airdrop'] ) : 0
            );
        }

        // ENHANCED: Extract metadata with better handling
        if ( isset( $moralis_response['tokenMetadata'] ) && is_array( $moralis_response['tokenMetadata'] ) ) {
            $metadata = $moralis_response['tokenMetadata'];
            $processed_data['metadata'] = array(
                'name' => $metadata['name'] ?? 'Unknown Token',
                'symbol' => $metadata['symbol'] ?? 'Unknown',
                'decimals' => isset( $metadata['decimals'] ) ? intval( $metadata['decimals'] ) : 9,
                'supply' => isset( $metadata['supply'] ) ? intval( $metadata['supply'] ) : 0
            );
        }

        // Final validation log
        error_log( 'SolanaWP: Enhanced Moralis data processing completed - Total Holders: ' . $processed_data['totalHolders'] );

        return $processed_data;

    } catch ( Exception $e ) {
        error_log( 'SolanaWP: Error processing enhanced Moralis data: ' . $e->getMessage() );
        return solanawp_get_default_moralis_data();
    }
}
/**
 * Get enhanced default Moralis data when API fails
 * UPDATED: Improved default structure matching the enhanced API response
 * UPDATED: Better default values for banners and frontend requirements
 */
function solanawp_get_default_moralis_data() {
    return array(
        'concentration' => array(
            'top_50_percentage' => 0,
            'top_100_percentage' => 0,
            'top_250_percentage' => 0,
            'top_500_percentage' => 0
        ),
        'holdersGrowth' => array(
            'change_5m' => 0, 'percent_5m' => 0,
            'change_1h' => 0, 'percent_1h' => 0,
            'change_6h' => 0, 'percent_6h' => 0,
            'change_24h' => 0, 'percent_24h' => 0,
            'change_3d' => 0, 'percent_3d' => 0,
            'change_7d' => 0, 'percent_7d' => 0,
            'change_30d' => 0, 'percent_30d' => 0
        ),
        'holdersCategories' => array(
            'whales' => 0, 'sharks' => 0, 'dolphins' => 0, 'fish' => 0,
            'octopus' => 0, 'crabs' => 0, 'shrimps' => 0
        ),
        'holdersByAcquisition' => array(
            'swap' => 0, 'transfer' => 0, 'airdrop' => 0
        ),
        'totalHolders' => 0,
        'metadata' => array(
            'name' => 'Unknown Token',
            'symbol' => 'Unknown',
            'decimals' => 9,
            'supply' => 0
        ),
        'error' => 'Unable to fetch enhanced Moralis data',
        'data_source' => 'default_enhanced_moralis',
        'fetched_at' => time()
    );
}

// ============================================================================
// EXISTING ENHANCED RUGCHECK FUNCTIONS (Keep existing)
// ============================================================================

/**
 * ENHANCED: Fetch RugCheck Data from api.rugcheck.xyz with enhanced error handling
 * UPDATED: Added extraction of totalHolders and topHolders count for frontend
 */
function solanawp_fetch_enhanced_rugcheck_data( $address ) {
    try {
        $rugcheck_url = "https://api.rugcheck.xyz/v1/tokens/{$address}/report";

        error_log( "SolanaWP: Fetching enhanced RugCheck data from: {$rugcheck_url}" );

        $response = wp_remote_get( $rugcheck_url, array(
            'timeout' => 20, // Increased timeout for enhanced data
            'headers' => array(
                'User-Agent' => 'SolanaWP/2.0-Enhanced',
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate'
            )
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'SolanaWP: Enhanced RugCheck API request failed: ' . $response->get_error_message() );
            return solanawp_get_enhanced_default_rugcheck_data();
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            error_log( "SolanaWP: Enhanced RugCheck API returned HTTP {$response_code}" );
            return solanawp_get_enhanced_default_rugcheck_data();
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( 'SolanaWP: Enhanced RugCheck API returned invalid JSON: ' . json_last_error_msg() );
            return solanawp_get_enhanced_default_rugcheck_data();
        }

        if ( !$data || !is_array( $data ) ) {
            error_log( 'SolanaWP: Enhanced RugCheck API returned empty or invalid data' );
            return solanawp_get_enhanced_default_rugcheck_data();
        }

        // Enhanced data processing
        $enhanced_data = solanawp_process_enhanced_rugcheck_data( $data );

        error_log( 'SolanaWP: Successfully fetched and processed enhanced RugCheck data for: ' . $address );

        return $enhanced_data;

    } catch ( Exception $e ) {
        error_log( 'SolanaWP: Enhanced RugCheck API exception: ' . $e->getMessage() );
        return solanawp_get_enhanced_default_rugcheck_data();
    }
}

/**
 * Process and enhance RugCheck data for better frontend consumption
 * UPDATED: Added extraction of totalHolders and topHolders count
 */
function solanawp_process_enhanced_rugcheck_data( $raw_data ) {
    $processed_data = array(
        'score' => $raw_data['score_normalised'] ?? 0, // Use score_normalised for Rug Risk Score display
        'score_normalised' => $raw_data['score_normalised'] ?? 0,
        'rugged' => $raw_data['rugged'] ?? false,
        'detectedAt' => $raw_data['detectedAt'] ?? null,
        'tokenMeta' => array(
            'mutable' => $raw_data['tokenMeta']['mutable'] ?? null,
            'name' => $raw_data['tokenMeta']['name'] ?? 'Unknown',
            'symbol' => $raw_data['tokenMeta']['symbol'] ?? 'Unknown'
        ),
        'mintAuthority' => $raw_data['mintAuthority'] ?? null,
        'freezeAuthority' => $raw_data['freezeAuthority'] ?? null,
        'markets' => array(),
        'risks' => array(),
        'creatorTokens' => array(),
        'insiderNetworks' => $raw_data['insiderNetworks'] ?? array(),
        'lockers' => $raw_data['lockers'] ?? array(),
        'topHolders' => $raw_data['topHolders'] ?? array(),
        // NEW: Extract totalHolders and top holders count for frontend distribution display
        'totalHolders' => $raw_data['totalHolders'] ?? 0,
        'topHoldersCount' => is_array($raw_data['topHolders'] ?? null) ? count($raw_data['topHolders']) : 0,
        'data_source' => 'rugcheck_enhanced',
        'fetched_at' => time()
    );

    // Enhanced markets processing
    if ( isset( $raw_data['markets'] ) && is_array( $raw_data['markets'] ) ) {
        foreach ( $raw_data['markets'] as $market ) {
            $processed_market = array(
                'dex' => $market['dex'] ?? 'Unknown',
                'pairAddress' => $market['pairAddress'] ?? null,
                'lp' => array(
                    'lpLockedPct' => $market['lp']['lpLockedPct'] ?? 0,
                    'lpLocked' => $market['lp']['lpLocked'] ?? false,
                    'lpBurned' => $market['lp']['lpBurned'] ?? false
                ),
                'liquidity' => array(
                    'usd' => $market['liquidity']['usd'] ?? 0,
                    'base' => $market['liquidity']['base'] ?? 0,
                    'quote' => $market['liquidity']['quote'] ?? 0
                )
            );
            $processed_data['markets'][] = $processed_market;
        }
    }

    // Enhanced risks processing with original level preservation
    if ( isset( $raw_data['risks'] ) && is_array( $raw_data['risks'] ) ) {
        foreach ( $raw_data['risks'] as $risk ) {
            $processed_risk = array(
                'name' => $risk['name'] ?? 'Unknown Risk',
                'description' => $risk['description'] ?? 'No description available',
                'level' => $risk['level'] ?? 'Unknown',
                'score' => $risk['score'] ?? null,
                'value' => $risk['value'] ?? null,
                'category' => solanawp_categorize_risk_type( $risk['name'] ?? '' )
            );
            $processed_data['risks'][] = $processed_risk;
        }
    }

    // Enhanced creator tokens processing
    if ( isset( $raw_data['creatorTokens'] ) && is_array( $raw_data['creatorTokens'] ) ) {
        foreach ( $raw_data['creatorTokens'] as $token ) {
            $processed_token = array(
                'mint' => $token['mint'] ?? null,
                'name' => $token['tokenMeta']['name'] ?? 'Unknown',
                'symbol' => $token['tokenMeta']['symbol'] ?? 'Unknown',
                'createdAt' => $token['createdAt'] ?? null,
                'marketCap' => $token['marketCap'] ?? 0,
                'rugged' => $token['rugged'] ?? false,
                'score' => $token['score'] ?? 0
            );
            $processed_data['creatorTokens'][] = $processed_token;
        }

        // Sort by creation date (newest first)
        usort( $processed_data['creatorTokens'], function( $a, $b ) {
            return strtotime( $b['createdAt'] ?? '1970-01-01' ) - strtotime( $a['createdAt'] ?? '1970-01-01' );
        } );
    }

    return $processed_data;
}

/**
 * Categorize risk level based on risk data
 */
function solanawp_categorize_risk_level( $risk ) {
    $score = $risk['score'] ?? 0;
    $name = strtolower( $risk['name'] ?? '' );

    // Critical risks
    if ( strpos( $name, 'rug' ) !== false ||
        strpos( $name, 'scam' ) !== false ||
        strpos( $name, 'malicious' ) !== false ||
        $score >= 80 ) {
        return 'CRITICAL';
    }

    // High risks
    if ( strpos( $name, 'authority' ) !== false ||
        strpos( $name, 'mint' ) !== false ||
        strpos( $name, 'freeze' ) !== false ||
        $score >= 60 ) {
        return 'High';
    }

    // Medium risks
    if ( strpos( $name, 'liquidity' ) !== false ||
        strpos( $name, 'concentration' ) !== false ||
        $score >= 30 ) {
        return 'Medium';
    }

    // Low risks
    return 'Low';
}

/**
 * Categorize risk type for better organization
 */
function solanawp_categorize_risk_type( $risk_name ) {
    $name = strtolower( $risk_name );

    if ( strpos( $name, 'liquidity' ) !== false ) {
        return 'liquidity';
    } elseif ( strpos( $name, 'authority' ) !== false || strpos( $name, 'mint' ) !== false || strpos( $name, 'freeze' ) !== false ) {
        return 'authority';
    } elseif ( strpos( $name, 'holder' ) !== false || strpos( $name, 'concentration' ) !== false ) {
        return 'distribution';
    } elseif ( strpos( $name, 'creator' ) !== false || strpos( $name, 'insider' ) !== false ) {
        return 'creator';
    } else {
        return 'general';
    }
}

/**
 * Get enhanced default RugCheck data when API fails
 * UPDATED: Added default values for totalHolders and topHoldersCount
 */
function solanawp_get_enhanced_default_rugcheck_data() {
    return array(
        'score' => 0,
        'score_normalised' => 0,
        'rugged' => false,
        'detectedAt' => null,
        'tokenMeta' => array(
            'mutable' => null,
            'name' => 'Unknown',
            'symbol' => 'Unknown'
        ),
        'mintAuthority' => 'Unknown',
        'freezeAuthority' => 'Unknown',
        'markets' => array(),
        'risks' => array(),
        'creatorTokens' => array(),
        'insiderNetworks' => array(),
        'lockers' => array(),
        'topHolders' => array(),
        // NEW: Default values for new fields
        'totalHolders' => 0,
        'topHoldersCount' => 0,
        'error' => 'Unable to fetch enhanced RugCheck data',
        'data_source' => 'default_enhanced',
        'fetched_at' => time()
    );
}

// ============================================================================
// EXISTING FUNCTIONS (KEEP ALL UNCHANGED)
// ============================================================================

/**
 * PHASE 1: Enhanced Account Details with Free Solana RPC API (UPDATED)
 * Updated to use free Solana RPC endpoint: https://api.mainnet-beta.solana.com
 */
function solanawp_fetch_enhanced_account_data( $address ) {
    try {
        // Use free Solana RPC endpoint
        $solana_rpc_url = 'https://api.mainnet-beta.solana.com';

        // Prepare JSON-RPC payload as specified
        $payload = json_encode( array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getAccountInfo',
            'params' => array(
                $address,
                array(
                    'encoding' => 'base64'
                )
            )
        ) );

        error_log( "SolanaWP: Requesting Free Solana RPC: {$solana_rpc_url}" );
        error_log( "SolanaWP: JSON-RPC Payload: {$payload}" );

        // Make POST request
        $response = wp_remote_post( $solana_rpc_url, array(
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'body' => $payload
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'SolanaWP: Free Solana RPC request failed: ' . $response->get_error_message() );
            return solanawp_get_default_account_data();
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        error_log( "SolanaWP: Free Solana RPC Response Code: {$response_code}" );
        error_log( "SolanaWP: Free Solana RPC Response Body: " . substr($body, 0, 500) );

        if ( $response_code !== 200 ) {
            error_log( "SolanaWP: Free Solana RPC returned HTTP {$response_code}, Body: {$body}" );
            return solanawp_get_default_account_data();
        }

        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( 'SolanaWP: Free Solana RPC returned invalid JSON: ' . json_last_error_msg() . ', Body: ' . $body );
            return solanawp_get_default_account_data();
        }

        if ( !$data || !is_array( $data ) ) {
            error_log( 'SolanaWP: Free Solana RPC returned empty or invalid data: ' . print_r($data, true) );
            return solanawp_get_default_account_data();
        }

        // Process JSON-RPC response
        if ( isset( $data['result']['value'] ) && $data['result']['value'] !== null ) {
            $account_info = $data['result']['value'];

            error_log( 'SolanaWP: Free Solana RPC Success - Account Info: ' . print_r($account_info, true) );

            $owner = $account_info['owner'] ?? null;
            $executable = $account_info['executable'] ?? null;
            $data_size = isset( $account_info['data'][0] ) ? strlen( $account_info['data'][0] ) : 0;
            $rent_epoch = $account_info['rentEpoch'] ?? null;
            $lamports = $account_info['lamports'] ?? 0;

            // Determine if it's a token based on owner
            $is_token = ( $owner === 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA' );

            $result = array(
                'is_token' => $is_token,
                // PHASE 1 REQUIREMENTS using Free Solana RPC response fields:
                'owner' => $owner ?: 'Unknown',                                    // OWNER
                'executable' => $executable !== null ? ($executable ? 'Yes' : 'No') : 'Unknown',  // EXECUTABLE
                'data_size' => $data_size ?: 0,                                   // DATA SIZE
                'rent_epoch' => $rent_epoch ?: 0,                                 // RENT EPOCH
                'lamports' => $lamports,                                           // LAMPORTS
                'account_type' => $is_token ? 'Token Mint' : 'Wallet Account',
                'data' => $account_info['data'] ?? null,                          // Store raw data field if needed
                'api_source' => 'free_solana_rpc'
            );

            error_log( 'SolanaWP: Free Solana RPC Final Result: ' . print_r($result, true) );
            return $result;

        } else {
            error_log( 'SolanaWP: Free Solana RPC - No account found or null value' );
            return solanawp_get_default_account_data();
        }

    } catch ( Exception $e ) {
        error_log( 'SolanaWP: Free Solana RPC exception: ' . $e->getMessage() );
        return solanawp_get_default_account_data();
    }
}

/**
 * Helper function to return default account data when API fails
 */
function solanawp_get_default_account_data() {
    return array(
        'is_token' => false,
        'owner' => 'Unknown',
        'executable' => 'Unknown',
        'data_size' => 0,
        'rent_epoch' => 0,
        'account_type' => 'Unknown',
        'error' => 'Unable to fetch account data'
    );
}

/**
 * PHASE 3: Token Distribution Analysis (Keep existing Alchemy implementation)
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
 * Extract Token Analytics from DexScreener Data (Keep existing)
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
 * ENHANCED: Calculate final scores WITHOUT Security Analysis (removed) with enhanced RugCheck integration
 */
function solanawp_calculate_final_scores_without_security_enhanced( $validation, $balance, $transactions, $rugcheck, $social ) {
    $trust_score = 50;
    $activity_score = 50;
    $overall_score = 50;
    $recommendation = 'Analysis completed.';

    try {
        // Enhanced RugCheck scoring with more granular analysis
        if ( isset( $rugcheck['score'] ) || isset( $rugcheck['score_normalised'] ) ) {
            $rug_score = $rugcheck['score_normalised'] ?? 0;

            // More granular scoring based on RugCheck data
            if ( $rug_score <= 20 ) {
                $trust_score += 40; // Very low risk
            } elseif ( $rug_score <= 40 ) {
                $trust_score += 25; // Low risk
            } elseif ( $rug_score <= 60 ) {
                $trust_score += 10; // Medium-low risk
            } elseif ( $rug_score <= 80 ) {
                $trust_score -= 15; // Medium-high risk
            } else {
                $trust_score -= 40; // High risk
            }

            // Enhanced penalty system for confirmed issues
            if ( isset( $rugcheck['rugged'] ) && $rugcheck['rugged'] ) {
                $trust_score -= 60; // Severe penalty for confirmed rug pull
            }

            // Authority-based scoring
            if ( isset( $rugcheck['mintAuthority'] ) ) {
                if ( $rugcheck['mintAuthority'] === null || $rugcheck['mintAuthority'] === 'null' ) {
                    $trust_score += 15; // Bonus for renounced mint authority
                } else {
                    $trust_score -= 25; // Penalty for active mint authority
                }
            }

            if ( isset( $rugcheck['freezeAuthority'] ) ) {
                if ( $rugcheck['freezeAuthority'] === null || $rugcheck['freezeAuthority'] === 'null' ) {
                    $trust_score += 10; // Bonus for renounced freeze authority
                } else {
                    $trust_score -= 20; // Penalty for active freeze authority
                }
            }

            // Liquidity lock scoring
            if ( isset( $rugcheck['markets'] ) && !empty( $rugcheck['markets'] ) ) {
                $market = $rugcheck['markets'][0];
                $liquidityPct = $market['lp']['lpLockedPct'] ?? 0;

                if ( $liquidityPct >= 80 ) {
                    $trust_score += 20; // High liquidity lock
                } elseif ( $liquidityPct >= 50 ) {
                    $trust_score += 10; // Medium liquidity lock
                } else {
                    $trust_score -= 15; // Low/no liquidity lock
                }
            }

            // Risk factors analysis
            if ( isset( $rugcheck['risks'] ) && is_array( $rugcheck['risks'] ) ) {
                foreach ( $rugcheck['risks'] as $risk ) {
                    $level = $risk['level'] ?? 'Low';

                    switch ( $level ) {
                        case 'CRITICAL':
                            $trust_score -= 30;
                            break;
                        case 'High':
                            $trust_score -= 15;
                            break;
                        case 'Medium':
                            $trust_score -= 8;
                            break;
                        case 'Low':
                            $trust_score -= 3;
                            break;
                    }
                }
            }

            // Creator history analysis
            if ( isset( $rugcheck['creatorTokens'] ) && is_array( $rugcheck['creatorTokens'] ) ) {
                $ruggedTokensCount = 0;
                $totalTokensCount = count( $rugcheck['creatorTokens'] );

                foreach ( $rugcheck['creatorTokens'] as $token ) {
                    if ( isset( $token['rugged'] ) && $token['rugged'] ) {
                        $ruggedTokensCount++;
                    }
                }

                if ( $totalTokensCount > 0 ) {
                    $ruggedRatio = $ruggedTokensCount / $totalTokensCount;

                    if ( $ruggedRatio >= 0.5 ) {
                        $trust_score -= 35; // High percentage of rugged tokens
                    } elseif ( $ruggedRatio >= 0.25 ) {
                        $trust_score -= 20; // Some rugged tokens
                    } elseif ( $ruggedRatio > 0 ) {
                        $trust_score -= 10; // Few rugged tokens
                    }

                    // Bonus for multiple successful tokens
                    if ( $totalTokensCount >= 3 && $ruggedRatio === 0 ) {
                        $trust_score += 15; // Experienced creator with clean record
                    }
                }
            }
        }

        // Transaction activity scoring (enhanced)
        if ( isset( $transactions['total_transactions'] ) ) {
            $tx_count = $transactions['total_transactions'];
            if ( $tx_count > 10000 ) {
                $activity_score += 50; // Very high activity
            } elseif ( $tx_count > 5000 ) {
                $activity_score += 40; // High activity
            } elseif ( $tx_count > 1000 ) {
                $activity_score += 30; // Good activity
            } elseif ( $tx_count > 100 ) {
                $activity_score += 20; // Moderate activity
            } elseif ( $tx_count > 10 ) {
                $activity_score += 10; // Low activity
            } else {
                $activity_score -= 20; // Very low activity
            }
        }

        // Balance activity scoring (enhanced)
        if ( isset( $balance['token_count'] ) ) {
            $token_count = $balance['token_count'];
            if ( $token_count > 20 ) {
                $activity_score += 15; // High token diversity
            } elseif ( $token_count > 10 ) {
                $activity_score += 10; // Good token diversity
            } elseif ( $token_count > 5 ) {
                $activity_score += 5; // Some token diversity
            }
        }

        // Social presence scoring (enhanced)
        $social_count = 0;
        $verified_social = false;

        if ( isset( $social['twitterInfo']['handle'] ) && $social['twitterInfo']['handle'] !== 'Not found' ) {
            $social_count++;
            if ( isset( $social['twitterInfo']['verified'] ) && $social['twitterInfo']['verified'] ) {
                $verified_social = true;
                $trust_score += 15; // Bonus for verified Twitter
            }
        }
        if ( isset( $social['telegramInfo']['channel'] ) && $social['telegramInfo']['channel'] !== 'Not found' ) {
            $social_count++;
        }
        if ( isset( $social['webInfo']['website'] ) && $social['webInfo']['website'] !== 'Not found' ) {
            $social_count++;
        }
        if ( isset( $social['discordInfo']['invite'] ) && $social['discordInfo']['invite'] !== 'Not found' ) {
            $social_count++;
        }
        if ( isset( $social['githubInfo']['repository'] ) && $social['githubInfo']['repository'] !== 'Not found' ) {
            $social_count++;
        }

        // Social scoring
        $trust_score += ( $social_count * 6 ); // Base social presence bonus

        if ( $verified_social ) {
            $trust_score += 10; // Additional verified social bonus
        }

        // Calculate overall score with weighted average
        $overall_score = round( ( $trust_score * 0.7 + $activity_score * 0.3 ) );

        // Normalize scores
        $trust_score = max( 0, min( 100, $trust_score ) );
        $activity_score = max( 0, min( 100, $activity_score ) );
        $overall_score = max( 0, min( 100, $overall_score ) );

        // Enhanced recommendation based on multiple factors
        if ( isset( $rugcheck['rugged'] ) && $rugcheck['rugged'] ) {
            $recommendation = '🚨 CRITICAL WARNING: This token has been confirmed as a rug pull. Do not interact under any circumstances.';
        } elseif ( $overall_score >= 85 ) {
            $recommendation = '✅ EXCELLENT: This address shows exceptional indicators of legitimacy and strong activity. Considered very safe for interaction.';
        } elseif ( $overall_score >= 70 ) {
            $recommendation = '✅ GOOD: This address shows strong indicators of legitimacy and good activity. Generally safe for interaction with standard precautions.';
        } elseif ( $overall_score >= 55 ) {
            $recommendation = '⚠️ MODERATE: This address shows mixed indicators. Exercise caution and conduct additional research before significant interactions.';
        } elseif ( $overall_score >= 40 ) {
            $recommendation = '⚠️ CONCERNING: This address has several risk factors. Proceed with extreme caution and limited exposure.';
        } else {
            $recommendation = '🚨 HIGH RISK: This address shows multiple concerning indicators. Strongly recommend avoiding interaction.';
        }

    } catch ( Exception $e ) {
        error_log( 'Enhanced score calculation error: ' . $e->getMessage() );
    }

    return array(
        'trust_score' => $trust_score,
        'activity_score' => $activity_score,
        'overall_score' => $overall_score,
        'recommendation' => $recommendation
    );
}

// ============================================================================
// EXISTING FUNCTIONS (Keep existing)
// ============================================================================

/**
 * Fetch DexScreener Data (PRIMARY SOURCE) - Keep existing
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
 * Address Validation - Keep existing
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
 * Balance & Holdings - Keep existing
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
 * Transaction Analysis - Keep existing
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
 * Website & Social with X API Integration - Keep existing
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
// HELPER FUNCTIONS (Keep existing)
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
/**
 * NEW: Enhanced Fetch Moralis Token Data with Dynamic Period Filtering
 * UPDATED: Integrates dynamic time periods calculation with Moralis data processing
 */
function solanawp_fetch_moralis_token_data_with_dynamic_periods( $address, $dynamic_time_periods ) {
    try {
        // Use the provided API key and correct endpoint
        $moralis_api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6ImE5NzdiOGVjLTA5OTctNGI2My1hZmNlLTY5YWNkZjNhMGFjZSIsIm9yZ0lkIjoiNDU0ODYzIiwidXNlcklkIjoiNDY3OTk0IiwidHlwZUlkIjoiNmU5YTg4ZjQtYTc3Zi00ODc2LWI0OGYtM2E1M2IxOTI3NmRhIiwidHlwZSI6IlBST0pFQ1QiLCJpYXQiOjE3NTAzNjY3NTIsImV4cCI6NDkwNjEyNjc1Mn0.pE-UnXUgg8NHWfogpwP7SpjNGESX9oVvLnHFuKHQVYQ';

        // Correct Moralis endpoint for token holders
        $holders_url = "https://solana-gateway.moralis.io/token/mainnet/holders/{$address}";

        error_log( "SolanaWP: Fetching enhanced Moralis data with dynamic periods from: {$holders_url}" );

        // Fetch token holders data with enhanced error handling
        $holders_response = solanawp_fetch_moralis_endpoint(
            $holders_url,
            $moralis_api_key
        );

        // Extract allowed period keys from dynamic time periods
        $allowed_period_keys = isset( $dynamic_time_periods['period_keys'] ) ? $dynamic_time_periods['period_keys'] : null;

        // Process and enhance the data with improved structure and dynamic filtering
        $enhanced_data = solanawp_process_moralis_data(
            $holders_response,
            $address,
            $allowed_period_keys
        );

        // Add dynamic time periods metadata to the response
        $enhanced_data['dynamic_periods_applied'] = $allowed_period_keys;
        $enhanced_data['dt_hours'] = $dynamic_time_periods['dt_hours'] ?? 0;

        // UPDATED: Additional validation for Total Holders extraction
        if ( isset( $enhanced_data['totalHolders'] ) && $enhanced_data['totalHolders'] > 0 ) {
            error_log( 'SolanaWP: Successfully extracted Total Holders from Moralis: ' . $enhanced_data['totalHolders'] );
        } else {
            error_log( 'SolanaWP: Warning - Total Holders not found in Moralis response for: ' . $address );
        }

        error_log( 'SolanaWP: Successfully fetched and processed enhanced Moralis data with dynamic filtering for: ' . $address );

        return $enhanced_data;

    } catch ( Exception $e ) {
        error_log( 'SolanaWP: Enhanced Moralis API with dynamic periods exception: ' . $e->getMessage() );
        return solanawp_get_default_moralis_data();
    }
}
?>
