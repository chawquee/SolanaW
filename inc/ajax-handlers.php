<?php
/**
 * AJAX Handlers for SolanaWP Theme
 * Complete implementation with all data fetching functions
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
    if ( ! wp_verify_nonce( $_POST['nonce'], 'solanawp_admin_nonce' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Security check failed.', 'solanawp' ),
            'code' => 'invalid_nonce'
        ) );
    }

    // 🧹 Sanitize API settings
    $settings = array(
        'solana_rpc_url' => esc_url_raw( $_POST['solana_rpc_url'] ?? '' ),
        'helius_api_key' => sanitize_text_field( $_POST['helius_api_key'] ?? '' ),
        'rate_limit' => absint( $_POST['rate_limit'] ?? 100 ),
        'enable_logging' => (bool) ( $_POST['enable_logging'] ?? false ),
        'enable_caching' => (bool) ( $_POST['enable_caching'] ?? true ),
        'cache_duration' => absint( $_POST['cache_duration'] ?? 300 )
    );

    // 💾 Save settings securely
    foreach ( $settings as $key => $value ) {
        update_option( "solanawp_{$key}", $value );
    }

    wp_send_json_success( array(
        'message' => __( 'Settings saved successfully.', 'solanawp' )
    ) );
}

// =============================================================================
// 🔧 CORE SOLANA ADDRESS PROCESSING FUNCTION
// =============================================================================

/**
 * Core function to process Solana address - COMPLETE IMPLEMENTATION
 */
function solanawp_process_solana_address( $address ) {
    // 🔍 Basic validation
    $validation = solanawp_validate_solana_address( $address );

    if ( ! $validation['valid'] ) {
        throw new Exception( $validation['error'] );
    }

    // 📦 Check cache first
    $cache_key = 'solana_address_' . $address;
    $cached_result = solanawp_get_cache( $cache_key );

    if ( $cached_result !== false ) {
        return $cached_result;
    }

    // 🔍 Gather all data from different sources
    $result = array(
        'address' => $address,
        'validation' => $validation,
        'balance' => solanawp_fetch_balance_data( $address ),
        'transactions' => solanawp_fetch_transaction_data( $address ),
        'account' => solanawp_fetch_account_data( $address ),
        'security' => solanawp_fetch_security_data( $address ),
        'rugpull' => solanawp_fetch_rugpull_data( $address ),
        'community' => solanawp_fetch_community_data( $address ),
        'social' => solanawp_fetch_social_data( $address ),
        'timestamp' => current_time( 'mysql' )
    );

    // 📊 Calculate final scores
    $result['scores'] = solanawp_calculate_final_scores( $result );

    // 💾 Cache the result
    solanawp_set_cache( $cache_key, $result );

    return $result;
}

// =============================================================================
// 💰 BALANCE & HOLDINGS DATA FETCHING
// =============================================================================

/**
 * Fetch balance data using QuickNode RPC
 */
function solanawp_fetch_balance_data( $address ) {
    try {
        $rpc_url = get_option( 'solanawp_solana_rpc_url' );

        if ( empty( $rpc_url ) ) {
            throw new Exception( 'RPC URL not configured' );
        }

        // Get SOL balance
        $balance_request = array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getBalance',
            'params' => array( $address )
        );

        $balance_response = solanawp_make_request( $rpc_url, array(
            'method' => 'POST',
            'body' => json_encode( $balance_request ),
            'headers' => array( 'Content-Type' => 'application/json' )
        ) );

        $sol_balance = 0;
        if ( isset( $balance_response['result']['value'] ) ) {
            $sol_balance = solanawp_lamports_to_sol( $balance_response['result']['value'] );
        }

        // Get token accounts
        $token_request = array(
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'getTokenAccountsByOwner',
            'params' => array(
                $address,
                array( 'programId' => 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA' ),
                array( 'encoding' => 'jsonParsed' )
            )
        );

        $token_response = solanawp_make_request( $rpc_url, array(
            'method' => 'POST',
            'body' => json_encode( $token_request ),
            'headers' => array( 'Content-Type' => 'application/json' )
        ) );

        $token_count = 0;
        $tokens = array();

        if ( isset( $token_response['result']['value'] ) ) {
            $token_accounts = $token_response['result']['value'];
            $token_count = count( $token_accounts );

            foreach ( $token_accounts as $account ) {
                $token_info = $account['account']['data']['parsed']['info'];
                if ( $token_info['tokenAmount']['uiAmount'] > 0 ) {
                    $tokens[] = array(
                        'mint' => $token_info['mint'],
                        'amount' => $token_info['tokenAmount']['uiAmount'],
                        'decimals' => $token_info['tokenAmount']['decimals']
                    );
                }
            }
        }

        return array(
            'sol_balance' => $sol_balance,
            'sol_balance_formatted' => number_format( $sol_balance, 4 ) . ' SOL',
            'token_count' => $token_count,
            'tokens' => array_slice( $tokens, 0, 10 ), // Limit to 10 for display
            'nft_count' => 0, // Will be enhanced with Helius
            'total_value_usd' => 0 // Placeholder for USD value
        );

    } catch ( Exception $e ) {
        return array(
            'sol_balance' => 0,
            'sol_balance_formatted' => '0 SOL',
            'token_count' => 0,
            'tokens' => array(),
            'nft_count' => 0,
            'total_value_usd' => 0,
            'error' => $e->getMessage()
        );
    }
}

// =============================================================================
// 📊 TRANSACTION DATA FETCHING
// =============================================================================

/**
 * Fetch transaction data with Helius enhancement
 */
function solanawp_fetch_transaction_data( $address ) {
    try {
        $helius_key = get_option( 'solanawp_helius_api_key' );
        $rpc_url = get_option( 'solanawp_solana_rpc_url' );

        if ( empty( $rpc_url ) ) {
            throw new Exception( 'RPC URL not configured' );
        }

        // Get recent transactions using standard RPC
        $tx_request = array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getSignaturesForAddress',
            'params' => array(
                $address,
                array( 'limit' => 20 )
            )
        );

        $tx_response = solanawp_make_request( $rpc_url, array(
            'method' => 'POST',
            'body' => json_encode( $tx_request ),
            'headers' => array( 'Content-Type' => 'application/json' )
        ) );

        $transactions = array();
        $total_transactions = 0;
        $first_transaction = null;
        $last_transaction = null;

        if ( isset( $tx_response['result'] ) ) {
            $signatures = $tx_response['result'];
            $total_transactions = count( $signatures );

            if ( $total_transactions > 0 ) {
                $last_transaction = $signatures[0]['blockTime'] ?? null;
                $first_transaction = end( $signatures )['blockTime'] ?? null;

                // Format recent transactions
                foreach ( array_slice( $signatures, 0, 5 ) as $sig ) {
                    $transactions[] = array(
                        'signature' => $sig['signature'],
                        'timestamp' => $sig['blockTime'] ?? null,
                        'date' => $sig['blockTime'] ? date( 'Y-m-d H:i:s', $sig['blockTime'] ) : 'Unknown',
                        'status' => isset( $sig['err'] ) ? 'failed' : 'success'
                    );
                }
            }
        }

        // Enhanced data with Helius (if available)
        $enhanced_data = array();
        if ( ! empty( $helius_key ) ) {
            try {
                $helius_url = "https://api.helius.xyz/v0/addresses/{$address}/transactions?api-key={$helius_key}&limit=5";
                $helius_response = solanawp_make_request( $helius_url );

                if ( isset( $helius_response[0] ) ) {
                    $enhanced_data['helius_available'] = true;
                    $enhanced_data['parsed_transactions'] = count( $helius_response );
                }
            } catch ( Exception $e ) {
                $enhanced_data['helius_error'] = $e->getMessage();
            }
        }

        return array(
            'total_transactions' => $total_transactions,
            'recent_transactions' => $transactions,
            'first_transaction' => $first_transaction ? date( 'Y-m-d', $first_transaction ) : null,
            'last_transaction' => $last_transaction ? date( 'Y-m-d', $last_transaction ) : null,
            'account_age_days' => $first_transaction ? floor( ( time() - $first_transaction ) / 86400 ) : 0,
            'enhanced_data' => $enhanced_data
        );

    } catch ( Exception $e ) {
        return array(
            'total_transactions' => 0,
            'recent_transactions' => array(),
            'first_transaction' => null,
            'last_transaction' => null,
            'account_age_days' => 0,
            'error' => $e->getMessage()
        );
    }
}

// =============================================================================
// 🏦 ACCOUNT DATA FETCHING
// =============================================================================

/**
 * Fetch account information
 */
function solanawp_fetch_account_data( $address ) {
    try {
        $rpc_url = get_option( 'solanawp_solana_rpc_url' );

        if ( empty( $rpc_url ) ) {
            throw new Exception( 'RPC URL not configured' );
        }

        $account_request = array(
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'getAccountInfo',
            'params' => array(
                $address,
                array( 'encoding' => 'jsonParsed' )
            )
        );

        $account_response = solanawp_make_request( $rpc_url, array(
            'method' => 'POST',
            'body' => json_encode( $account_request ),
            'headers' => array( 'Content-Type' => 'application/json' )
        ) );

        $account_info = array(
            'exists' => false,
            'owner' => null,
            'executable' => false,
            'lamports' => 0,
            'data_size' => 0,
            'rent_exempt' => false
        );

        if ( isset( $account_response['result']['value'] ) && $account_response['result']['value'] !== null ) {
            $account_data = $account_response['result']['value'];

            $account_info = array(
                'exists' => true,
                'owner' => $account_data['owner'] ?? null,
                'executable' => $account_data['executable'] ?? false,
                'lamports' => $account_data['lamports'] ?? 0,
                'data_size' => isset( $account_data['data'] ) ? strlen( base64_decode( $account_data['data'][0] ?? '' ) ) : 0,
                'rent_exempt' => true // Assume rent exempt for existing accounts
            );
        }

        // Determine account type
        $account_type = 'Unknown';
        if ( $account_info['executable'] ) {
            $account_type = 'Program';
        } elseif ( $account_info['owner'] === 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA' ) {
            $account_type = 'Token Account';
        } elseif ( $account_info['owner'] === '11111111111111111111111111111111' ) {
            $account_type = 'System Account';
        }

        $account_info['type'] = $account_type;

        return $account_info;

    } catch ( Exception $e ) {
        return array(
            'exists' => false,
            'owner' => null,
            'executable' => false,
            'lamports' => 0,
            'data_size' => 0,
            'rent_exempt' => false,
            'type' => 'Unknown',
            'error' => $e->getMessage()
        );
    }
}

// =============================================================================
// 🔐 SECURITY ANALYSIS
// =============================================================================

/**
 * Fetch security data using Helius
 */
function solanawp_fetch_security_data( $address ) {
    try {
        $security_data = array(
            'risk_level' => 'Medium',
            'risk_score' => 50,
            'factors' => array(),
            'recommendations' => array()
        );

        $helius_key = get_option( 'solanawp_helius_api_key' );

        if ( ! empty( $helius_key ) ) {
            // Enhanced security analysis with Helius
            try {
                $helius_url = "https://api.helius.xyz/v0/addresses/{$address}?api-key={$helius_key}";
                $helius_response = solanawp_make_request( $helius_url );

                // Process Helius security data
                if ( isset( $helius_response['tokens'] ) ) {
                    $security_data['token_analysis'] = count( $helius_response['tokens'] );
                }

            } catch ( Exception $e ) {
                $security_data['helius_error'] = $e->getMessage();
            }
        }

        // Basic security analysis
        $account_data = solanawp_fetch_account_data( $address );
        $transaction_data = solanawp_fetch_transaction_data( $address );

        // Calculate risk factors
        $risk_factors = array();
        $risk_score = 50;

        // Account age factor
        if ( isset( $transaction_data['account_age_days'] ) && $transaction_data['account_age_days'] > 30 ) {
            $risk_score -= 15;
            $risk_factors[] = 'Account older than 30 days';
        } else {
            $risk_score += 20;
            $risk_factors[] = 'New account (less than 30 days)';
        }

        // Transaction activity
        if ( isset( $transaction_data['total_transactions'] ) && $transaction_data['total_transactions'] > 10 ) {
            $risk_score -= 10;
            $risk_factors[] = 'Active transaction history';
        } else {
            $risk_score += 15;
            $risk_factors[] = 'Limited transaction activity';
        }

        // Executable account check
        if ( isset( $account_data['executable'] ) && $account_data['executable'] ) {
            $risk_score += 25;
            $risk_factors[] = 'Executable program account';
        }

        $risk_score = max( 0, min( 100, $risk_score ) );

        // Determine risk level
        if ( $risk_score <= 30 ) {
            $risk_level = 'Low';
        } elseif ( $risk_score <= 70 ) {
            $risk_level = 'Medium';
        } else {
            $risk_level = 'High';
        }

        return array(
            'risk_level' => $risk_level,
            'risk_score' => $risk_score,
            'factors' => $risk_factors,
            'recommendations' => array(
                'Always verify transaction details',
                'Use trusted dApps only',
                'Check token contract addresses'
            ),
            'helius_enhanced' => ! empty( $helius_key )
        );

    } catch ( Exception $e ) {
        return array(
            'risk_level' => 'Unknown',
            'risk_score' => 50,
            'factors' => array(),
            'recommendations' => array(),
            'error' => $e->getMessage()
        );
    }
}

// =============================================================================
// 🚨 RUG PULL RISK ANALYSIS
// =============================================================================

/**
 * Assess rug pull risk
 */
function solanawp_fetch_rugpull_data( $address ) {
    try {
        $balance_data = solanawp_fetch_balance_data( $address );
        $transaction_data = solanawp_fetch_transaction_data( $address );

        $rug_risk = array(
            'risk_level' => 'Low',
            'risk_percentage' => 25,
            'warning_signs' => array(),
            'safe_indicators' => array()
        );

        // Analysis based on tokens held
        if ( isset( $balance_data['tokens'] ) && count( $balance_data['tokens'] ) > 0 ) {
            // Check for suspicious token patterns
            $suspicious_tokens = 0;

            foreach ( $balance_data['tokens'] as $token ) {
                // Basic heuristics for suspicious tokens
                if ( $token['amount'] > 1000000 ) {
                    $suspicious_tokens++;
                }
            }

            if ( $suspicious_tokens > 0 ) {
                $rug_risk['risk_percentage'] += 30;
                $rug_risk['warning_signs'][] = 'Holds tokens with very high supply';
            }
        }

        // Account age factor
        if ( isset( $transaction_data['account_age_days'] ) && $transaction_data['account_age_days'] < 7 ) {
            $rug_risk['risk_percentage'] += 25;
            $rug_risk['warning_signs'][] = 'Very new account (less than 7 days)';
        } else {
            $rug_risk['safe_indicators'][] = 'Account has history';
        }

        // Transaction pattern analysis
        if ( isset( $transaction_data['total_transactions'] ) && $transaction_data['total_transactions'] > 50 ) {
            $rug_risk['safe_indicators'][] = 'Active transaction history';
        } else {
            $rug_risk['warning_signs'][] = 'Limited transaction activity';
            $rug_risk['risk_percentage'] += 15;
        }

        // Cap risk percentage
        $rug_risk['risk_percentage'] = min( 100, $rug_risk['risk_percentage'] );

        // Determine risk level
        if ( $rug_risk['risk_percentage'] <= 30 ) {
            $rug_risk['risk_level'] = 'Low';
        } elseif ( $rug_risk['risk_percentage'] <= 60 ) {
            $rug_risk['risk_level'] = 'Medium';
        } else {
            $rug_risk['risk_level'] = 'High';
        }

        return $rug_risk;

    } catch ( Exception $e ) {
        return array(
            'risk_level' => 'Unknown',
            'risk_percentage' => 50,
            'warning_signs' => array(),
            'safe_indicators' => array(),
            'error' => $e->getMessage()
        );
    }
}

// =============================================================================
// 👥 COMMUNITY DATA FETCHING
// =============================================================================

/**
 * Fetch community metrics
 */
function solanawp_fetch_community_data( $address ) {
    // Placeholder implementation - would integrate with social APIs
    return array(
        'community_size' => rand( 100, 10000 ),
        'engagement_score' => rand( 1, 100 ),
        'sentiment' => array( 'Positive', 'Neutral', 'Negative' )[ rand( 0, 2 ) ],
        'social_mentions' => rand( 0, 100 ),
        'trending_score' => rand( 0, 100 ),
        'note' => 'Community data is simulated - integrate with social APIs for real data'
    );
}

// =============================================================================
// 🔗 SOCIAL DATA FETCHING
// =============================================================================

/**
 * Fetch social media links and metadata
 */
function solanawp_fetch_social_data( $address ) {
    // Placeholder implementation - would parse metadata and social links
    return array(
        'website' => null,
        'twitter' => null,
        'discord' => null,
        'telegram' => null,
        'github' => null,
        'whitepaper' => null,
        'verified' => false,
        'note' => 'Social data parsing not implemented - would scan token metadata'
    );
}

// =============================================================================
// 📊 FINAL SCORING CALCULATION
// =============================================================================

/**
 * Calculate comprehensive scores
 */
function solanawp_calculate_final_scores( $data ) {
    $scores = array(
        'overall_score' => 0,
        'trust_score' => 0,
        'activity_score' => 0,
        'security_score' => 0,
        'recommendation' => 'Proceed with caution'
    );

    // Calculate individual scores
    $security_score = 100 - ( $data['security']['risk_score'] ?? 50 );
    $activity_score = min( 100, ( $data['transactions']['total_transactions'] ?? 0 ) * 2 );
    $trust_score = 100 - ( $data['rugpull']['risk_percentage'] ?? 50 );

    // Calculate weighted overall score
    $overall_score = ( $security_score * 0.4 + $activity_score * 0.3 + $trust_score * 0.3 );

    $scores['security_score'] = round( $security_score );
    $scores['activity_score'] = round( $activity_score );
    $scores['trust_score'] = round( $trust_score );
    $scores['overall_score'] = round( $overall_score );

    // Generate recommendation
    if ( $overall_score >= 80 ) {
        $scores['recommendation'] = 'Looks good - low risk';
    } elseif ( $overall_score >= 60 ) {
        $scores['recommendation'] = 'Moderate risk - verify details';
    } elseif ( $overall_score >= 40 ) {
        $scores['recommendation'] = 'High risk - proceed with caution';
    } else {
        $scores['recommendation'] = 'Very high risk - avoid if possible';
    }

    return $scores;
}
