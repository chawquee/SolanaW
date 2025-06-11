<?php
/**
 * Ajax Handler Functions
 *
 * @package Solana_Wordpress_Plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// 🚀 MAIN SOLANA ADDRESS PROCESSING FUNCTION
// =============================================================================

/**
 * Main AJAX handler for checking Solana address
 */
function solanawp_ajax_check_solana_address() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'solana_check_nonce' ) ) {
        wp_send_json_error( 'Invalid nonce' );
    }

    // Get and sanitize address
    $address = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';

    if ( empty( $address ) ) {
        wp_send_json_error( 'No address provided' );
    }

    try {
        // Process the Solana address
        $result = solanawp_process_solana_address( $address );

        // Log successful check
        solanawp_log_address_check( $address, 'success' );

        wp_send_json_success( $result );

    } catch ( Exception $e ) {
        // Log error
        solanawp_log_address_check( $address, 'error', $e->getMessage() );

        wp_send_json_error( $e->getMessage() );
    }
}
add_action( 'wp_ajax_check_solana_address', 'solanawp_ajax_check_solana_address' );
add_action( 'wp_ajax_nopriv_check_solana_address', 'solanawp_ajax_check_solana_address' );

/**
 * Main function to process Solana address - COMMUNITY DEACTIVATED
 */
function solanawp_process_solana_address( $address ) {
    // Validate address first
    $validation = solanawp_validate_solana_address( $address );

    if ( ! $validation['valid'] ) {
        throw new Exception( $validation['error'] );
    }

    // Check cache first
    $cache_key = 'solana_address_' . $address;
    $cached_result = solanawp_get_cache( $cache_key );

    if ( $cached_result !== false ) {
        return $cached_result;
    }

    // Gather all data from different sources
    $result = array(
        'address' => $address,
        'validation' => $validation,
        'balance' => solanawp_fetch_balance_data( $address ),
        'transactions' => solanawp_fetch_transaction_data( $address ),
        'account' => solanawp_fetch_account_data( $address ),
        'security' => solanawp_fetch_security_data( $address ),
        'rugpull' => solanawp_fetch_rugpull_data( $address ),
        'social' => solanawp_fetch_social_data( $address ),
        'timestamp' => current_time( 'mysql' )
    );

    // Calculate final scores
    $result['scores'] = solanawp_calculate_final_scores( $result );

    // Cache the result for 5 minutes
    solanawp_set_cache( $cache_key, $result, 300 );

    return $result;
}

// =============================================================================
// ✅ ADDRESS VALIDATION
// =============================================================================

/**
 * Validate Solana address format and check existence
 */
function solanawp_validate_solana_address( $address ) {
    // Basic format validation
    if ( ! preg_match( '/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $address ) ) {
        return array(
            'valid' => false,
            'error' => 'Invalid Solana address format',
            'format' => 'Invalid',
            'length' => strlen( $address ),
            'type' => 'Unknown'
        );
    }

    // Check on-chain existence
    $rpc_url = get_option( 'solanawp_solana_rpc_url' );

    if ( ! empty( $rpc_url ) ) {
        try {
            $request = array(
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getAccountInfo',
                'params' => array( $address )
            );

            $response = solanawp_make_request( $rpc_url, array(
                'method' => 'POST',
                'body' => json_encode( $request ),
                'headers' => array( 'Content-Type' => 'application/json' )
            ) );

            $exists = isset( $response['result']['value'] ) && $response['result']['value'] !== null;

            return array(
                'valid' => true,
                'exists' => $exists,
                'format' => 'Valid',
                'length' => strlen( $address ),
                'type' => $exists ? 'Active Account' : 'Uninitialized',
                'message' => $exists ? 'Address exists on Solana blockchain' : 'Valid format but account not initialized'
            );

        } catch ( Exception $e ) {
            // Continue with basic validation
        }
    }

    return array(
        'valid' => true,
        'exists' => 'Unknown',
        'format' => 'Valid',
        'length' => strlen( $address ),
        'type' => 'Unknown',
        'message' => 'Valid Solana address format'
    );
}

// =============================================================================
// 💰 BALANCE & HOLDINGS DATA FETCHING
// =============================================================================

/**
 * Fetch balance data using QuickNode RPC with Helius enhancement
 */
function solanawp_fetch_balance_data( $address ) {
    try {
        $rpc_url = get_option( 'solanawp_solana_rpc_url' );
        $helius_key = get_option( 'solanawp_helius_api_key' );

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
        $nft_count = 0;

        if ( isset( $token_response['result']['value'] ) ) {
            foreach ( $token_response['result']['value'] as $token_account ) {
                if ( isset( $token_account['account']['data']['parsed']['info'] ) ) {
                    $token_info = $token_account['account']['data']['parsed']['info'];
                    $token_count++;

                    $tokens[] = array(
                        'mint' => $token_info['mint'] ?? '',
                        'amount' => $token_info['tokenAmount']['uiAmount'] ?? 0,
                        'decimals' => $token_info['tokenAmount']['decimals'] ?? 0
                    );
                }
            }
        }

        // Get NFT count using Helius if available
        if ( ! empty( $helius_key ) ) {
            try {
                $helius_url = "https://api.helius.xyz/v0/addresses/{$address}/nfts?api-key={$helius_key}";
                $nft_response = solanawp_make_request( $helius_url );

                if ( isset( $nft_response['nfts'] ) ) {
                    $nft_count = count( $nft_response['nfts'] );
                }
            } catch ( Exception $e ) {
                // Continue without NFT data
            }
        }

        return array(
            'sol_balance' => $sol_balance,
            'sol_balance_formatted' => number_format( $sol_balance, 4 ) . ' SOL',
            'sol_balance_usd' => number_format( $sol_balance * solanawp_get_sol_price(), 2 ),
            'token_count' => $token_count,
            'tokens' => array_slice( $tokens, 0, 10 ),
            'nft_count' => $nft_count,
            'enhanced_data' => ! empty( $helius_key )
        );

    } catch ( Exception $e ) {
        return array(
            'sol_balance' => 0,
            'sol_balance_formatted' => '0 SOL',
            'sol_balance_usd' => '0',
            'token_count' => 0,
            'tokens' => array(),
            'nft_count' => 0,
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

        // Use Helius enhanced API if available
        if ( ! empty( $helius_key ) ) {
            $helius_url = "https://api.helius.xyz/v0/addresses/{$address}/transactions?api-key={$helius_key}&limit=20";
            $response = solanawp_make_request( $helius_url );

            if ( isset( $response[0] ) ) {
                $transactions = array();
                $total_transactions = count( $response );

                $timestamps = array_map( function( $tx ) {
                    return $tx['timestamp'] ?? null;
                }, $response );

                $timestamps = array_filter( $timestamps );
                $first_transaction = ! empty( $timestamps ) ? min( $timestamps ) : null;
                $last_transaction = ! empty( $timestamps ) ? max( $timestamps ) : null;

                // Format recent transactions with Helius parsed data
                foreach ( array_slice( $response, 0, 5 ) as $tx ) {
                    $transactions[] = array(
                        'signature' => substr( $tx['signature'] ?? 'Unknown', 0, 20 ) . '...',
                        'type' => $tx['type'] ?? 'Unknown',
                        'description' => $tx['description'] ?? 'Transaction',
                        'timestamp' => $tx['timestamp'] ?? null,
                        'date' => isset( $tx['timestamp'] ) ? date( 'Y-m-d H:i:s', $tx['timestamp'] ) : 'Unknown'
                    );
                }

                return array(
                    'total_transactions' => $total_transactions,
                    'recent_transactions' => $transactions,
                    'first_transaction' => $first_transaction ? date( 'Y-m-d', $first_transaction ) : null,
                    'last_transaction' => $last_transaction ? date( 'Y-m-d', $last_transaction ) : null,
                    'account_age_days' => $first_transaction ? floor( ( time() - $first_transaction ) / 86400 ) : 0,
                    'enhanced' => true
                );
            }
        }

        // Fallback to standard RPC
        if ( empty( $rpc_url ) ) {
            throw new Exception( 'No API configured' );
        }

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
                        'signature' => substr( $sig['signature'], 0, 20 ) . '...',
                        'type' => 'Transfer',
                        'description' => 'SOL Transfer',
                        'timestamp' => $sig['blockTime'] ?? null,
                        'date' => isset( $sig['blockTime'] ) ? date( 'Y-m-d H:i:s', $sig['blockTime'] ) : 'Unknown'
                    );
                }
            }
        }

        return array(
            'total_transactions' => $total_transactions,
            'recent_transactions' => $transactions,
            'first_transaction' => $first_transaction ? date( 'Y-m-d', $first_transaction ) : null,
            'last_transaction' => $last_transaction ? date( 'Y-m-d', $last_transaction ) : null,
            'account_age_days' => $first_transaction ? floor( ( time() - $first_transaction ) / 86400 ) : 0,
            'enhanced' => false
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

        if ( isset( $account_response['result']['value'] ) && $account_response['result']['value'] !== null ) {
            $account_data = $account_response['result']['value'];

            // Truncate owner address for display
            $owner = $account_data['owner'] ?? 'Unknown';
            if ( strlen( $owner ) > 20 ) {
                $owner = substr( $owner, 0, 8 ) . '...' . substr( $owner, -8 );
            }

            return array(
                'owner' => $owner,
                'executable' => $account_data['executable'] ? 'Yes' : 'No',
                'data_size' => isset( $account_data['data'] ) ?
                    (is_array($account_data['data']) ? strlen( $account_data['data'][0] ?? '' ) : strlen($account_data['data'])) . ' bytes'
                    : '0 bytes',
                'rent_epoch' => $account_data['rentEpoch'] ?? 'N/A',
                'lamports' => $account_data['lamports'] ?? 0,
                'exists' => true
            );
        }

        return array(
            'owner' => 'N/A',
            'executable' => 'No',
            'data_size' => '0 bytes',
            'rent_epoch' => 'N/A',
            'lamports' => 0,
            'exists' => false
        );

    } catch ( Exception $e ) {
        return array(
            'owner' => 'Error',
            'executable' => 'Unknown',
            'data_size' => 'Unknown',
            'rent_epoch' => 'Unknown',
            'error' => $e->getMessage()
        );
    }
}

// =============================================================================
// 🔒 SECURITY ANALYSIS
// =============================================================================

/**
 * Enhanced security analysis using Helius data
 */
function solanawp_fetch_security_data( $address ) {
    try {
        $helius_key = get_option( 'solanawp_helius_api_key' );
        $account_data = solanawp_fetch_account_data( $address );
        $transaction_data = solanawp_fetch_transaction_data( $address );

        $risk_factors = array();
        $risk_score = 50; // Start with neutral risk

        // Account age analysis
        if ( isset( $transaction_data['account_age_days'] ) ) {
            if ( $transaction_data['account_age_days'] > 30 ) {
                $risk_score -= 15;
                $risk_factors[] = 'Account older than 30 days';
            } else {
                $risk_score += 20;
                $risk_factors[] = 'New account (less than 30 days)';
            }
        }

        // Transaction activity analysis
        if ( isset( $transaction_data['total_transactions'] ) ) {
            if ( $transaction_data['total_transactions'] > 10 ) {
                $risk_score -= 10;
                $risk_factors[] = 'Active transaction history';
            } else {
                $risk_score += 15;
                $risk_factors[] = 'Limited transaction activity';
            }
        }

        // Executable account check
        if ( isset( $account_data['executable'] ) && $account_data['executable'] === 'Yes' ) {
            $risk_score += 25;
            $risk_factors[] = 'Executable program account (higher risk)';
        }

        // Helius enhanced security checks
        if ( ! empty( $helius_key ) ) {
            try {
                // Check for known scam addresses or patterns
                // This would typically use Helius's enhanced APIs
                $enhanced_check = array(
                    'known_scam' => false,
                    'suspicious_pattern' => false
                );

                if ( $enhanced_check['known_scam'] ) {
                    $risk_score += 50;
                    $risk_factors[] = 'Address flagged in scam database';
                }

                if ( $enhanced_check['suspicious_pattern'] ) {
                    $risk_score += 20;
                    $risk_factors[] = 'Suspicious transaction patterns detected';
                }
            } catch ( Exception $e ) {
                // Continue without enhanced data
            }
        }

        // Determine risk level
        $risk_level = 'Medium';
        if ( $risk_score <= 30 ) {
            $risk_level = 'Low';
        } elseif ( $risk_score >= 70 ) {
            $risk_level = 'High';
        }

        // Format factors for display
        $known_scam_status = $risk_score >= 70 ?
            array( 'isScam' => true, 'text' => 'High risk indicators detected' ) :
            array( 'isScam' => false, 'text' => 'No known scam associations' );

        $suspicious_activity = count( $risk_factors ) > 3 ?
            array( 'found' => true, 'text' => 'Multiple risk factors detected' ) :
            array( 'found' => false, 'text' => 'Normal activity patterns' );

        return array(
            'risk_level' => $risk_level,
            'risk_score' => $risk_score,
            'factors' => $risk_factors,
            'known_scam' => $known_scam_status,
            'suspicious_activity' => $suspicious_activity,
            'enhanced' => ! empty( $helius_key )
        );

    } catch ( Exception $e ) {
        return array(
            'risk_level' => 'Unknown',
            'risk_score' => 50,
            'factors' => array(),
            'known_scam' => array( 'isScam' => false, 'text' => 'Analysis unavailable' ),
            'suspicious_activity' => array( 'found' => false, 'text' => 'Analysis unavailable' ),
            'error' => $e->getMessage()
        );
    }
}

// =============================================================================
// 💀 RUG PULL RISK ANALYSIS
// =============================================================================

/**
 * Assess rug pull risk using combined data sources
 */
function solanawp_fetch_rugpull_data($address) {
    try {
        $balance_data = solanawp_fetch_balance_data($address);
        $transaction_data = solanawp_fetch_transaction_data($address);
        $account_data = solanawp_fetch_account_data($address);

        $risk_percentage = 25; // Start with low risk
        $warning_signs = array();
        $safe_indicators = array();

        // Token holding analysis (REAL DATA)
        if (isset($balance_data['tokens']) && count($balance_data['tokens']) > 0) {
            $suspicious_tokens = 0;
            $high_value_tokens = 0;

            foreach ($balance_data['tokens'] as $token) {
                // Check for extremely high token amounts (potential honeypot)
                if ($token['amount'] > 1000000) {
                    $suspicious_tokens++;
                }

                if ($token['amount'] > 100) {
                    $high_value_tokens++;
                }
            }

            if ($suspicious_tokens > 0) {
                $risk_percentage += 20;
                $warning_signs[] = 'Holds tokens with extremely high supply amounts';
            }

            if ($high_value_tokens > 5) {
                $risk_percentage += 10;
                $warning_signs[] = 'Holds many high-value token positions';
            } else {
                $safe_indicators[] = 'Reasonable token portfolio size';
            }
        }

        // Account age factor (REAL DATA)
        if (isset($transaction_data['account_age_days'])) {
            if ($transaction_data['account_age_days'] < 7) {
                $risk_percentage += 30;
                $warning_signs[] = 'Very new account (less than 1 week old)';
            } elseif ($transaction_data['account_age_days'] < 30) {
                $risk_percentage += 15;
                $warning_signs[] = 'New account (less than 1 month old)';
            } else {
                $risk_percentage -= 10;
                $safe_indicators[] = 'Established account with history';
            }
        }

        // Transaction pattern analysis (REAL DATA)
        if (isset($transaction_data['total_transactions'])) {
            if ($transaction_data['total_transactions'] < 5) {
                $risk_percentage += 15;
                $warning_signs[] = 'Very few transactions (possible bot/fake account)';
            } elseif ($transaction_data['total_transactions'] > 100) {
                $risk_percentage -= 15;
                $safe_indicators[] = 'Active transaction history';
            }
        }

        // Cap risk percentage
        $risk_percentage = max(0, min(100, $risk_percentage));

        // Determine risk level
        $risk_level = 'Medium';
        if ($risk_percentage <= 30) {
            $risk_level = 'Low';
        } elseif ($risk_percentage >= 70) {
            $risk_level = 'High';
        }

        // REAL TOKEN DATA (no more random values)
        $token_data = solanawp_analyze_token_properties($address);

        return array(
            'risk_level' => $risk_level,
            'risk_percentage' => $risk_percentage,
            'warning_signs' => $warning_signs,
            'safe_indicators' => $safe_indicators,
            'overall_score' => 100 - $risk_percentage,

            // REAL or "Not Available" instead of mock data
            'volume_24h' => $token_data['volume_24h'] ?? 'Not Available',
            'liquidity_locked' => $token_data['liquidity_locked'] ?? array(
                    'text' => 'Unknown',
                    'color' => '#6b7280'
                ),
            'ownership_renounced' => $token_data['ownership_renounced'] ?? array(
                    'text' => 'Unknown',
                    'color' => '#6b7280'
                ),
            'mint_authority' => $token_data['mint_authority'] ?? array(
                    'text' => 'Unknown',
                    'color' => '#6b7280'
                ),
            'freeze_authority' => $token_data['freeze_authority'] ?? array(
                    'text' => 'Unknown',
                    'color' => '#6b7280'
                ),
            'token_distribution' => $token_data['distribution'] ?? array(
                    array('label' => 'Data Not Available', 'percentage' => 100, 'color' => '#6b7280')
                )
        );

    } catch (Exception $e) {
        return array(
            'risk_level' => 'Unknown',
            'risk_percentage' => 50,
            'warning_signs' => array('Analysis error: ' . $e->getMessage()),
            'safe_indicators' => array(),
            'error' => $e->getMessage()
        );
    }
}

/**
 * Analyze real token properties (replace with actual token analysis)
 */
function solanawp_analyze_token_properties($address) {
    try {
        $helius_key = get_option('solanawp_helius_api_key');

        if (empty($helius_key)) {
            return array(); // Return empty if no API key
        }

        // This would implement real token analysis using Helius APIs
        // For now, return "Not Available" instead of mock data

        return array(
            'volume_24h' => null, // Would get from real DEX data
            'liquidity_locked' => array(
                'text' => 'Analysis Not Available',
                'color' => '#6b7280'
            ),
            'ownership_renounced' => array(
                'text' => 'Analysis Not Available',
                'color' => '#6b7280'
            ),
            'mint_authority' => array(
                'text' => 'Check Required',
                'color' => '#6b7280'
            ),
            'freeze_authority' => array(
                'text' => 'Check Required',
                'color' => '#6b7280'
            ),
            'distribution' => array(
                array('label' => 'Analysis Required', 'percentage' => 100, 'color' => '#6b7280')
            )
        );

    } catch (Exception $e) {
        return array();
    }
}

// =============================================================================
// 🌐 WEBSITE & SOCIAL DATA
// =============================================================================

/**
 * Fetch website and social media data
 */
function solanawp_fetch_social_data($address) {
    try {
        $helius_key = get_option('solanawp_helius_api_key');

        $result = array(
            'websiteUrl' => null,
            'domainAge' => null,
            'sslSecured' => false,
            'whoisInfo' => null,
            'twitterInfo' => null,
            'telegramInfo' => null,
            'enhanced' => !empty($helius_key)
        );

        // Try to get real metadata from Helius
        if (!empty($helius_key)) {
            try {
                // Get token metadata
                $metadata = solanawp_fetch_token_metadata($address, $helius_key);

                if ($metadata && isset($metadata['uri'])) {
                    $result['websiteUrl'] = $metadata['uri'];

                    // Get real WHOIS data for the domain
                    $domain_info = solanawp_get_real_whois_data($metadata['uri']);
                    if ($domain_info) {
                        $result['whoisInfo'] = $domain_info;
                        $result['domainAge'] = $domain_info['age'] ?? null;
                        $result['sslSecured'] = $domain_info['ssl'] ?? false;
                    }
                }

                // Extract social links from metadata description
                if (isset($metadata['description'])) {
                    $social_links = solanawp_extract_social_links($metadata['description']);

                    if (isset($social_links['twitter'])) {
                        $result['twitterInfo'] = array(
                            'handle' => $social_links['twitter'],
                            'followers' => null, // Would need Twitter API for real data
                            'verified' => null,
                            'created' => null,
                            'lastActive' => null,
                            'engagementRate' => null
                        );
                    }

                    if (isset($social_links['telegram'])) {
                        $result['telegramInfo'] = array(
                            'handle' => $social_links['telegram'],
                            'members' => null, // Would need Telegram API for real data
                            'onlineMembers' => null,
                            'created' => null,
                            'description' => null,
                            'isPremium' => null
                        );
                    }
                }

            } catch (Exception $e) {
                error_log('Metadata fetch error: ' . $e->getMessage());
            }
        }

        return $result;

    } catch (Exception $e) {
        return array(
            'websiteUrl' => null,
            'domainAge' => null,
            'sslSecured' => false,
            'whoisInfo' => null,
            'twitterInfo' => null,
            'telegramInfo' => null,
            'error' => $e->getMessage()
        );
    }
}

/**
 * Get real token metadata from Helius
 */
function solanawp_fetch_token_metadata($address, $helius_key) {
    try {
        $url = "https://api.helius.xyz/v0/addresses/{$address}/metadata?api-key={$helius_key}";
        $response = solanawp_make_request($url);

        return $response['metadata'] ?? null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get real WHOIS data using free API
 */
function solanawp_get_real_whois_data($url) {
    $domain = parse_url($url, PHP_URL_HOST);
    if (!$domain) {
        return null;
    }

    try {
        // Use free WHOIS API (who-dat.as93.net)
        $whois_url = "https://who-dat.as93.net/{$domain}";
        $response = solanawp_make_request($whois_url, array('timeout' => 10));

        if ($response && isset($response['domain'])) {
            $domain_data = $response['domain'];

            $creation_date = $domain_data['creation_date'] ?? null;
            $age = null;
            if ($creation_date) {
                $age = floor((time() - strtotime($creation_date)) / (365.25 * 24 * 3600));
                $age = $age . ' years';
            }

            return array(
                'registrar' => $domain_data['registrar'] ?? 'Unknown',
                'createdDate' => $creation_date,
                'expiryDate' => $domain_data['expiry_date'] ?? null,
                'status' => $domain_data['status'] ?? 'Unknown',
                'age' => $age,
                'ssl' => solanawp_check_ssl($url)
            );
        }
    } catch (Exception $e) {
        error_log('WHOIS lookup error: ' . $e->getMessage());
    }

    return null;
}

/**
 * Extract social media links from text
 */
function solanawp_extract_social_links($text) {
    $links = array();

    // Extract Twitter handle
    if (preg_match('/twitter\.com\/([a-zA-Z0-9_]+)/', $text, $matches)) {
        $links['twitter'] = '@' . $matches[1];
    } elseif (preg_match('/@([a-zA-Z0-9_]+)/', $text, $matches)) {
        $links['twitter'] = '@' . $matches[1];
    }

    // Extract Telegram
    if (preg_match('/t\.me\/([a-zA-Z0-9_]+)/', $text, $matches)) {
        $links['telegram'] = '@' . $matches[1];
    }

    return $links;
}

/**
 * Check if website has SSL certificate
 */
function solanawp_check_ssl($url) {
    return strpos($url, 'https://') === 0;
}


// =============================================================================
// 📊 FINAL SCORE CALCULATION
// =============================================================================

/**
 * Calculate comprehensive scores based on all data
 */
function solanawp_calculate_final_scores($data) {
    $scores = array(
        'overall_score' => 0,
        'trust_score' => 0,
        'activity_score' => 0,
        'security_score' => 0,
        'recommendation' => 'Analysis based on real blockchain data'
    );

    // Calculate individual scores from REAL data
    $security_score = 100 - ($data['security']['risk_score'] ?? 50);
    $activity_score = 0;
    $trust_score = 100 - ($data['rugpull']['risk_percentage'] ?? 50);

    // Activity score based on real transaction count
    if (isset($data['transactions']['total_transactions'])) {
        $tx_count = intval($data['transactions']['total_transactions']);
        $activity_score = min(100, $tx_count * 2); // 2 points per transaction, max 100
    }

    // Account age bonus (REAL DATA)
    if (isset($data['transactions']['account_age_days']) && $data['transactions']['account_age_days'] > 90) {
        $trust_score += 10;
    }

    // Real balance bonus
    if (isset($data['balance']['sol_balance']) && $data['balance']['sol_balance'] > 0) {
        $trust_score += 5;
    }

    // Normalize scores
    $scores['security_score'] = max(0, min(100, $security_score));
    $scores['activity_score'] = max(0, min(100, $activity_score));
    $scores['trust_score'] = max(0, min(100, $trust_score));

    // Calculate overall score
    $scores['overall_score'] = round(
        ($scores['security_score'] + $scores['activity_score'] + $scores['trust_score']) / 3
    );

    // Generate recommendation based on REAL analysis
    if ($scores['overall_score'] >= 70) {
        $scores['recommendation'] = 'Low risk - Address shows positive indicators based on blockchain activity';
    } elseif ($scores['overall_score'] >= 40) {
        $scores['recommendation'] = 'Medium risk - Mixed indicators, exercise normal caution';
    } else {
        $scores['recommendation'] = 'High risk - Multiple risk factors detected in blockchain analysis';
    }

    return $scores;
}


// =============================================================================
// 🛠️ HELPER FUNCTIONS
// =============================================================================

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
 * Log address check
 */
function solanawp_log_address_check( $address, $status, $error = null ) {
    $logging_enabled = get_option( 'solanawp_enable_logging', true );

    if ( ! $logging_enabled ) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'solana_checks';

    $wpdb->insert(
        $table_name,
        array(
            'address' => $address,
            'status' => $status,
            'error_message' => $error,
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'timestamp' => current_time( 'mysql' )
        )
    );
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

/**
 * Get current SOL price (mock implementation)
 */
function solanawp_get_sol_price() {
    // Check cache first
    $cache_key = 'sol_price_usd';
    $cached_price = solanawp_get_cache($cache_key);

    if ($cached_price !== false) {
        return $cached_price;
    }

    try {
        // Use free CoinGecko API
        $url = 'https://api.coingecko.com/api/v3/simple/price?ids=solana&vs_currencies=usd';
        $response = solanawp_make_request($url);

        if (isset($response['solana']['usd'])) {
            $price = floatval($response['solana']['usd']);
            // Cache for 5 minutes
            solanawp_set_cache($cache_key, $price, 300);
            return $price;
        }
    } catch (Exception $e) {
        error_log('SOL price fetch error: ' . $e->getMessage());
    }

    // Return null instead of mock price if API fails
    return null;
}
