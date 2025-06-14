/**
 * SolanaWP Main JavaScript File - ENHANCED WITH TOKEN ANALYTICS
 * File location: assets/js/main.js
 *
 * Enhanced with Token Analytics section positioned AFTER Address Validation and BEFORE Balance & Holdings
 * Version: Token Analytics Enhancement
 */

(function($) { // Use jQuery no-conflict wrapper

    // Document Ready
    $(function() {

        // --- Solana Address Checker Logic ---
        const $checkAddressBtn = $('#checkAddressBtn');
        const $solanaAddressInput = $('#solanaAddressInput');
        const $resultsSection = $('#resultsSection');

        // Helper to show/hide loading state on button
        function setButtonLoading(isLoading) {
            if (isLoading) {
                $checkAddressBtn.html('<svg class="icon animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2a8.001 8.001 0 0015.357 2M15 15h-5"></path></svg>' + (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.checking_text : 'Checking...')).prop('disabled', true);
            } else {
                $checkAddressBtn.html('<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>' + (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.check_address_text : 'Check Address')).prop('disabled', false);
            }
        }

        // Helper to reset all result areas
        function resetResultAreas() {
            $resultsSection.find('.card, #accountAndSecurityOuterGrid, #affiliateSection').hide();
            $resultsSection.find('[id]').each(function() {
                const id = $(this).attr('id');
                if (id && id !== 'resultsSection' && !$(this).is('input, button, h2, h3, h4, div.affiliate-title')) {
                    if ($(this).is('span:not(.dist-label):not(.dist-percentage), div.metric-value, div.score-value, div.risk-level-indicator, p#finalSummaryText') || $(this).hasClass('value-placeholder')) {
                        if (!$(this).children(':not(svg)').length) {
                            $(this).text('-');
                        }
                    } else if (id === 'recentTransactionsList' || id === 'rugTokenDistribution' || id === 'communityCardContent') {
                        $(this).empty().append('<p class="loading-initial-data">' + (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.loading_text : 'Loading...') + '</p>');
                    }
                }
            });
        }

        // Helper to update validation UI
        function updateValidationUI(validation) {
            $('#validationStatus').text(validation.exists || 'Unknown');
            $('#validationFormat').text(validation.format || 'Unknown');
            $('#validationLength').text(validation.length || 0);
            $('#validationType').text(validation.type || 'Unknown');
            $('#validationNoteText').text(validation.message || 'Valid Solana address');

            if (validation.valid) {
                $('#validationNoteBanner').show();
                $('#addressValidationCard').show();
            } else {
                $('#validationNoteBanner').hide();
                $('#addressValidationCard').show();
            }
        }

        // NEW: Helper to update Token Analytics UI
        function updateTokenAnalyticsUI(analytics) {
            console.log('Updating Token Analytics UI with data:', analytics);

            // Price Information
            $('#tokenPriceUsd').text(analytics.price_usd !== 'N/A' ? '$' + analytics.price_usd : 'N/A');
            $('#tokenPriceNative').text(analytics.price_native !== 'N/A' ? analytics.price_native + ' SOL' : 'N/A');
            $('#tokenLiquidity').text(analytics.liquidity_usd || 'N/A');
            $('#tokenMarketCap').text(analytics.market_cap || 'N/A');

            // Volume Information
            $('#tokenVolume24h').text(analytics.volume_24h || 'N/A');
            $('#tokenVolume6h').text(analytics.volume_6h || 'N/A');
            $('#tokenVolume1h').text(analytics.volume_1h || 'N/A');

            // 24h Transactions
            let txn24hText = 'N/A';
            if (analytics.transactions_24h && analytics.transactions_24h.buys !== 'N/A' && analytics.transactions_24h.sells !== 'N/A') {
                const totalTxns = parseInt(analytics.transactions_24h.buys) + parseInt(analytics.transactions_24h.sells);
                txn24hText = totalTxns.toString();
            }
            $('#tokenTransactions24h').text(txn24hText);

            // Price Changes with color coding
            updatePriceChange('#tokenPriceChange5m', analytics.price_change_5m);
            updatePriceChange('#tokenPriceChange1h', analytics.price_change_1h);
            updatePriceChange('#tokenPriceChange6h', analytics.price_change_6h);
            updatePriceChange('#tokenPriceChange24h', analytics.price_change_24h);

            // Trading Activity
            $('#tokenBuys24h').text(analytics.transactions_24h?.buys || 'N/A');
            $('#tokenSells24h').text(analytics.transactions_24h?.sells || 'N/A');
            $('#tokenBuys6h').text(analytics.transactions_6h?.buys || 'N/A');
            $('#tokenSells6h').text(analytics.transactions_6h?.sells || 'N/A');
            $('#tokenBuys1h').text(analytics.transactions_1h?.buys || 'N/A');
            $('#tokenSells1h').text(analytics.transactions_1h?.sells || 'N/A');

            // Show the Token Analytics card
            $('#tokenAnalyticsCard').show();
        }

        // Helper to update price change with color coding
        function updatePriceChange(elementId, changeValue) {
            const $element = $(elementId);
            $element.text(changeValue || 'N/A');

            if (changeValue && changeValue !== 'N/A') {
                // Remove existing color classes
                $element.removeClass('price-positive price-negative price-neutral');

                // Parse the percentage value
                const numericValue = parseFloat(changeValue.replace('%', ''));

                if (!isNaN(numericValue)) {
                    if (numericValue > 0) {
                        $element.addClass('price-positive');
                    } else if (numericValue < 0) {
                        $element.addClass('price-negative');
                    } else {
                        $element.addClass('price-neutral');
                    }
                }
            }
        }

        // Main function to populate results with real API data
        function populateResults(data) {
            console.log('SolanaWP: Populating results with enhanced Token Analytics data:', data);

            // Clear previous results
            $('.card').hide();
            $('#accountAndSecurityOuterGrid').css('display', 'none');

            // Show results section
            $('#resultsSection').show();

            // 1. VALIDATION CARD - Always show first
            if (data.validation) {
                updateValidationUI(data.validation);
            }

            // Stop here if address is not valid
            if (!data.validation || !data.validation.valid) {
                return;
            }

            // 2. TOKEN ANALYTICS CARD - NEW SECTION (positioned after validation, before balance)
            if (data.token_analytics) {
                updateTokenAnalyticsUI(data.token_analytics);
            }

            // 3. BALANCE & HOLDINGS CARD
            if (data.balance) {
                const bh = data.balance;
                $('#solBalanceValue').text(bh.sol_balance_formatted || '0 SOL');
                $('#solBalanceUsdValue').text('$' + (bh.sol_balance_usd || '0') + ' USD');
                $('#tokenCount').text(bh.token_count || '0');
                $('#nftCount').text(bh.nft_count || '0');
                $('#balanceHoldingsCard').show();
            }

            // 4. TRANSACTION ANALYSIS CARD
            if (data.transactions) {
                const ta = data.transactions;
                $('#totalTransactions').text(ta.total_transactions || '0');
                $('#firstActivity').text(ta.first_transaction || 'Unknown');
                $('#lastActivity').text(ta.last_transaction || 'Unknown');

                // Recent transactions
                const $recentList = $('#recentTransactionsList').empty();
                if (ta.recent_transactions && ta.recent_transactions.length > 0) {
                    ta.recent_transactions.forEach(tx => {
                        $recentList.append(`
                            <div class="recent-transaction-item">
                                <div>
                                    <span class="tx-type">${tx.type}</span>
                                    <span class="tx-signature">${tx.signature}</span>
                                    <span class="tx-time">${tx.date}</span>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    $recentList.append('<p>No recent transactions found</p>');
                }

                $('#transactionAnalysisCard').show();
            }

            // 5. ACCOUNT DETAILS & SECURITY ANALYSIS CARDS
            if (data.account && data.security) {
                // Account Details
                const ad = data.account;
                $('#accountOwner').text(ad.owner || 'Unknown');
                $('#accountExecutable').text(ad.executable || 'Unknown');
                $('#accountLamports').text(ad.lamports || '0');
                $('#accountDataSize').text(ad.data_size || '0');
                $('#accountRentEpoch').text(ad.rent_epoch || '0');
                $('#accountType').text(ad.account_type || 'Unknown');

                // Security Analysis
                const sa = data.security;
                $('#securityRiskLevel').text(sa.risk_level || 'Unknown');
                $('#securityRiskScore').text((sa.risk_score || '0') + '/100');
                $('#securityScamProbability').text(sa.scam_probability || 'Unknown');
                $('#securitySuspiciousActivity').text(sa.suspicious_activity || 'None detected');
                $('#securityLastCheck').text(sa.last_security_check || 'Unknown');

                $('#accountAndSecurityOuterGrid').show();
            }

            // 6. RUG PULL RISK ANALYSIS CARD
            if (data.rugpull) {
                const rp = data.rugpull;
                $('#rugPullRiskPercentage').text(rp.risk_percentage + '%');
                $('#rugPullOverallScore').text(rp.overall_score + '/100');
                $('#rugPullLiquidityRisk').text(rp.liquidity_risk || 'Unknown');
                $('#rugPullOwnershipRisk').text(rp.ownership_risk || 'Unknown');
                $('#rugPullAuthorityStatus').text(rp.authority_status || 'Unknown');

                // Warning indicators
                const $warningsList = $('#rugPullWarningsList').empty();
                if (rp.warning_indicators && rp.warning_indicators.length > 0) {
                    rp.warning_indicators.forEach(warning => {
                        $warningsList.append(`<li class="warning-item">${warning}</li>`);
                    });
                } else {
                    $warningsList.append('<li class="neutral-item">No major warnings detected</li>');
                }

                // Safe indicators
                const $safeList = $('#rugPullSafeIndicatorsList').empty();
                if (rp.safe_indicators && rp.safe_indicators.length > 0) {
                    rp.safe_indicators.forEach(indicator => {
                        $safeList.append(`<li class="safe-item">${indicator}</li>`);
                    });
                } else {
                    $safeList.append('<li class="neutral-item">No specific safety indicators found</li>');
                }

                $('#rugPullRiskCard').show();
            }

            // 7. WEBSITE & SOCIAL ACCOUNTS CARD
            if (data.social) {
                const ws = data.social;

                // Website information
                if (ws.website) {
                    $('#webInfoUrl').text(ws.website).attr('href', ws.website);
                    $('#webInfoUrl').closest('.web-info-item').show();
                } else {
                    $('#webInfoUrl').closest('.web-info-item').hide();
                }

                // WHOIS data
                if (ws.whois_data) {
                    $('#webInfoRegCountry').text(ws.whois_data.country || 'unavailable');
                }

                // Twitter information
                if (ws.twitter_handle) {
                    $('#twitterHandle').html(`<a href="${ws.twitter_handle}" target="_blank" rel="noopener">${ws.twitter_handle}</a>`);
                } else {
                    $('#twitterHandle').text('Not found');
                }

                // Telegram information
                if (ws.telegram_channel) {
                    $('#telegramChannel').html(`<a href="${ws.telegram_channel}" target="_blank" rel="noopener">${ws.telegram_channel}</a>`);
                } else {
                    $('#telegramChannel').text('Not found');
                }

                $('#websiteSocialCard').show();
            }

            // 8. FINAL RESULTS CARD
            if (data.scores) {
                const scores = data.scores;
                $('#finalTrustScore').text(scores.trust_score + '/100');
                $('#finalReliabilityScore').text(scores.activity_score + '/100');
                $('#finalOverallRating').text(scores.overall_score + '/100');
                $('#finalSummary').text(scores.recommendation);
                $('#finalResultsCard').show();
            }

            // Show affiliate section
            $('#affiliateSection').show();

            console.log('SolanaWP: All results populated successfully');
        }

        // Event handler for Check Address button
        $checkAddressBtn.on('click', function(e) {
            e.preventDefault();

            const address = $solanaAddressInput.val().trim();

            if (!address) {
                alert(typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.error_enter_address : 'Please enter a Solana address.');
                return;
            }

            console.log('SolanaWP: Checking address:', address);

            // Set loading state
            setButtonLoading(true);
            resetResultAreas();

            // Make AJAX call
            $.ajax({
                url: typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'solanawp_check_solana_address',
                    address: address,
                    nonce: typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.nonce : ''
                },
                timeout: 30000,
                success: function(response) {
                    console.log('SolanaWP: AJAX Success Response:', response);

                    if (response.success && response.data) {
                        populateResults(response.data);
                    } else {
                        console.error('SolanaWP: API Error:', response.data || 'Unknown error');
                        alert('Error: ' + (response.data && response.data.message ? response.data.message : 'An unknown error occurred.'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('SolanaWP: AJAX Error:', status, error);
                    alert(typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.error_general_ajax : 'An error occurred. Please try again.');
                },
                complete: function() {
                    setButtonLoading(false);
                }
            });
        });

        // Event handler for Enter key in address input
        $solanaAddressInput.on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $checkAddressBtn.click();
            }
        });

        console.log('SolanaWP: Enhanced main.js with Token Analytics loaded successfully');
    });

})(jQuery);
