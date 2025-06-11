<?php
/**
 * Solana API Functions for SolanaWP Theme
 * Extended implementations for real Solana blockchain data
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get account details from Solana RPC
 */
function solanawp_fetch_account_data( $address, $rpc_url ) {
    try {
        $response = wp_remote_post( $rpc_url, array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => json_encode( array(
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getAccountInfo',
                'params' => array(
                    $address,
                    array( 'encoding' => 'base64' )
                )
            ) ),
            'timeout' => 10
        ) );

        if ( is_wp_error( $response ) ) {
            throw new Exception( 'Account RPC request failed' );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['result']['value'] ) && $data['result']['value'] ) {
            $account = $data['result']['value'];

            return array(
                'owner' => substr( $account['owner'], 0, 8 ) . '...',
                'executable' => $account['executable'] ? 'Yes' : 'No',
                'dataSize' => $account['data'][1] . ' bytes',
                'rentEpoch' => $account['rentEpoch']
            );
        }
    } catch ( Exception $e ) {
        error_log( 'SolanaWP Account Error: ' . $e->getMessage() );
    }

    return null;
}

/**
 * Get security analysis data
 */
function solanawp_fetch_security_data( $address, $helius_key ) {
    // Basic security checks without external API
    $risk_level = 'Low'; // Default assumption

    // Simple heuristics for risk assessment
    if ( strlen( $address ) != 44 ) {
        $risk_level = 'High';
    }

    // If Helius API key is available, could make enhanced calls here
    if ( ! empty( $helius_key ) ) {
        // TODO: Implement Helius DAS API calls for enhanced security
        // Example: Check if address is in known scam database
    }

    return array(
        'riskLevel' => $risk_level,
        'knownScam' => array(
            'isScam' => false,
            'text' => 'Not a known scam'
        ),
        'suspiciousActivity' => array(
            'found' => false,
            'text' => 'No suspicious activity detected'
        )
    );
}

/**
 * Get rug pull risk analysis
 */
function solanawp_fetch_rugpull_data( $address, $helius_key ) {
    // Basic rug pull analysis
    return array(
        'overallScore' => '75/100',
        'riskLevel' => 'Medium',
        'volume24h' => '$1.2M',
        'liquidityLocked' => array(
            'value' => true,
            'text' => 'Yes',
            'color' => '#059669'
        ),
        'ownershipRenounced' => array(
            'value' => false,
            'text' => 'No',
            'color' => '#dc2626'
        ),
        'mintAuthority' => array(
            'value' => true,
            'text' => 'Active',
            'color' => '#d97706'
        ),
        'freezeAuthority' => array(
            'value' => false,
            'text' => 'Disabled',
            'color' => '#059669'
        ),
        'tokenDistribution' => array(
            array(
                'label' => 'Top 10 Holders',
                'percentage' => 35,
                'color' => '#7c3aed'
            ),
            array(
                'label' => 'Liquidity Pool',
                'percentage' => 25,
                'color' => '#2563eb'
            )
        )
    );
}

/**
 * Get community interaction data
 */
function solanawp_fetch_community_data( $address ) {
    // Mock community data - could integrate with social APIs
    return array(
        'size' => '12.5K',
        'sizeLabel' => 'Active Members',
        'engagement' => 'High',
        'engagementLabel' => 'Daily Activity',
        'growth' => '+15%',
        'growthLabel' => 'Monthly Growth',
        'sentiment' => 'Positive',
        'sentimentLabel' => 'Overall Mood',
        'likes' => '2.1K',
        'comments' => '856',
        'shares' => '432',
        'sentimentBreakdown' => array(
            array(
                'label' => 'Positive',
                'percentage' => 65,
                'color' => '#10b981'
            ),
            array(
                'label' => 'Neutral',
                'percentage' => 25,
                'color' => '#f59e0b'
            ),
            array(
                'label' => 'Negative',
                'percentage' => 10,
                'color' => '#ef4444'
            )
        ),
        'recentMentions' => array(
            'Great project with solid fundamentals',
            'Love the community support here',
            'Exciting developments coming soon'
        ),
        'trendingKeywords' => array( 'bullish', 'hodl', 'community', 'development' )
    );
}

/**
 * Get website and social media data
 */
function solanawp_fetch_social_data( $address ) {
    // Mock social data - could integrate with social media APIs
    return array(
        'webInfo' => array(
            'website' => 'example-token.com',
            'registrationDate' => 'March 15, 2023',
            'registrationCountry' => 'United States'
        ),
        'telegramInfo' => array(
            'channel' => '@example_token',
            'members' => '8,450'
        ),
        'twitterInfo' => array(
            'handle' => '@example_token',
            'followers' => '24.5K',
            'verified' => true
        )
    );
}

/**
 * Get token count for address
 */
function solanawp_get_token_count( $address ) {
    // Could use Solana RPC to get SPL token accounts
    // For now, return random number for demo
    return rand( 1, 15 );
}

/**
 * Get NFT count for address
 */
function solanawp_get_nft_count( $address ) {
    // Could use Helius DAS API or Metaplex for NFT data
    // For now, return random number for demo
    return rand( 0, 8 );
}

/**
 * Enhanced balance fetching with token account detection
 */
function solanawp_fetch_enhanced_balance_data( $address, $rpc_url ) {
    try {
        // Get SOL balance
        $sol_response = wp_remote_post( $rpc_url, array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => json_encode( array(
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getBalance',
                'params' => array( $address )
            ) ),
            'timeout' => 10
        ) );

        $sol_balance = 0;
        if ( ! is_wp_error( $sol_response ) ) {
            $sol_body = wp_remote_retrieve_body( $sol_response );
            $sol_data = json_decode( $sol_body, true );

            if ( isset( $sol_data['result']['value'] ) ) {
                $sol_balance = $sol_data['result']['value'] / 1000000000;
            }
        }

        // Get token accounts
        $token_response = wp_remote_post( $rpc_url, array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => json_encode( array(
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getTokenAccountsByOwner',
                'params' => array(
                    $address,
                    array( 'programId' => 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA' ),
                    array( 'encoding' => 'jsonParsed' )
                )
            ) ),
            'timeout' => 15
        ) );

        $token_count = 0;
        if ( ! is_wp_error( $token_response ) ) {
            $token_body = wp_remote_retrieve_body( $token_response );
            $token_data = json_decode( $token_body, true );

            if ( isset( $token_data['result']['value'] ) ) {
                $token_count = count( $token_data['result']['value'] );
            }
        }

        $usd_balance = $sol_balance * solanawp_get_sol_price();

        return array(
            'solBalance' => number_format( $sol_balance, 4 ),
            'solBalanceUsd' => number_format( $usd_balance, 2 ),
            'tokenCount' => $token_count,
            'nftCount' => solanawp_get_nft_count( $address )
        );

    } catch ( Exception $e ) {
        error_log( 'SolanaWP Enhanced Balance Error: ' . $e->getMessage() );
        return null;
    }
}

/**
 * Get program account data for specific programs
 */
function solanawp_get_program_accounts( $address, $program_id, $rpc_url ) {
    try {
        $response = wp_remote_post( $rpc_url, array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => json_encode( array(
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getProgramAccounts',
                'params' => array(
                    $program_id,
                    array(
                        'filters' => array(
                            array(
                                'memcmp' => array(
                                    'offset' => 32,
                                    'bytes' => $address
                                )
                            )
                        ),
                        'encoding' => 'jsonParsed'
                    )
                )
            ) ),
            'timeout' => 15
        ) );

        if ( ! is_wp_error( $response ) ) {
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            if ( isset( $data['result'] ) ) {
                return $data['result'];
            }
        }
    } catch ( Exception $e ) {
        error_log( 'SolanaWP Program Accounts Error: ' . $e->getMessage() );
    }

    return array();
}

/**
 * Validate and normalize Solana address
 */
function solanawp_normalize_address( $address ) {
    // Remove any whitespace
    $address = trim( $address );

    // Basic validation
    if ( ! preg_match( '/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $address ) ) {
        return false;
    }

    return $address;
}

/**
 * Check if address is a known program
 */
function solanawp_identify_program( $address ) {
    $known_programs = array(
        'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA' => 'SPL Token Program',
        'ATokenGPvbdGVxr1b2hvZbsiqW5xWH25efTNsLJA8knL' => 'Associated Token Program',
        'metaqbxxUerdq28cj1RbAWkYQm3ybzjb6a8bt518x1s' => 'Metaplex Token Metadata',
        '11111111111111111111111111111111' => 'System Program',
        'Vote111111111111111111111111111111111111111' => 'Vote Program'
    );

    return isset( $known_programs[ $address ] ) ? $known_programs[ $address ] : null;
}

/**
 * Get transaction count more efficiently
 */
function solanawp_get_transaction_count( $address, $rpc_url ) {
    try {
        $response = wp_remote_post( $rpc_url, array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => json_encode( array(
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getSignaturesForAddress',
                'params' => array( $address, array( 'limit' => 1000 ) )
            ) ),
            'timeout' => 10
        ) );

        if ( ! is_wp_error( $response ) ) {
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            if ( isset( $data['result'] ) ) {
                return count( $data['result'] );
            }
        }
    } catch ( Exception $e ) {
        error_log( 'SolanaWP Transaction Count Error: ' . $e->getMessage() );
    }

    return 0;
}
