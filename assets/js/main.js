/**
 * SolanaWP Main JavaScript File - ENHANCED WITH SOLANA.FM API INTEGRATION
 * File location: assets/js/main.js
 *
 * Enhanced with Token Analytics and REAL Authority Data from Solana.fm API
 * Version: SOLANA.FM INTEGRATION - Real Mint/Freeze Authority Analysis
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
                        $(this).empty().append('<p class="loading-initial-data">' + (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.loading_text || 'Loading...' : 'Loading...') + '</p>');
                    }
                }
            });
        }

        // Helper to update validation UI
        function updateValidationUI(validation) {
            const isValid = validation.isValid || validation.valid;
            const $validationCard = $('#addressValidationCard');

            $('#validationStatus').text(isValid ? 'Valid' : 'Invalid');
            $('#validationFormat').text(validation.format || 'Unknown');
            $('#validationLength').text(validation.length || 'Unknown');
            $('#validationType').text(validation.type || 'Unknown');

            $validationCard.show();

            const $banner = $('#validationNoteBanner');
            const $bannerText = $('#validationNoteText');

            if (isValid) {
                $banner.removeClass('error-banner').addClass('success-banner');
                $bannerText.text(validation.message || 'Valid Solana address detected');
                $banner.show();
            } else {
                $banner.removeClass('success-banner').addClass('error-banner');
                $bannerText.text(validation.message || 'Invalid address format');
                $banner.show();
            }
        }

        /**
         * Helper function to format numbers with appropriate suffixes
         */
        function formatNumber(num) {
            if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
            if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
            return num.toFixed(2);
        }

        /**
         * Helper function to update price changes with color coding
         */
        function updatePriceChange(elementId, change) {
            const $element = $(elementId);
            if (change !== undefined && change !== null) {
                const changeValue = parseFloat(change).toFixed(2);
                $element.text((change >= 0 ? '+' : '') + changeValue + '%');

                // Color coding
                if (change > 0) {
                    $element.css('color', '#10b981'); // Green for positive
                } else if (change < 0) {
                    $element.css('color', '#ef4444'); // Red for negative
                } else {
                    $element.css('color', '#6b7280'); // Gray for neutral
                }
            } else {
                $element.text('-').css('color', '#6b7280');
            }
        }

        /**
         * Update progress bar
         */
        function updateProgressBar(barId, value) {
            const $bar = $('#' + barId);
            if ($bar.length) {
                $bar.css('width', value + '%');

                // Update color based on value
                if (value >= 70) {
                    $bar.removeClass('medium low').addClass('high');
                } else if (value >= 40) {
                    $bar.removeClass('high low').addClass('medium');
                } else {
                    $bar.removeClass('high medium').addClass('low');
                }
            }
        }

        /**
         * Update token distribution chart
         */
        function updateTokenDistributionChart(data) {
            if (!window.tokenDistributionChart || !Array.isArray(data)) {
                console.log('Chart not available or invalid data');
                return;
            }

            console.log('Updating token distribution chart with:', data);

            try {
                window.tokenDistributionChart.data.labels = data.map(d => d.label || 'Unknown');
                window.tokenDistributionChart.data.datasets[0].data = data.map(d => d.percentage || 0);
                window.tokenDistributionChart.data.datasets[0].backgroundColor = data.map(d => d.color || '#6b7280');
                window.tokenDistributionChart.update();

                console.log('Token distribution chart updated successfully');
            } catch (error) {
                console.error('Error updating token distribution chart:', error);
            }
        }

        /**
         * Update Token Analytics UI with DexScreener data
         */
        function updateTokenAnalyticsUI(tokenData, dexscreenerData) {
            if (!dexscreenerData) {
                console.log('No DexScreener data available for Token Analytics');
                return;
            }

            console.log('Updating Token Analytics with:', dexscreenerData);

            try {
                // Price Information
                $('#tokenPriceUsd').text(dexscreenerData.priceUsd ? '$' + parseFloat(dexscreenerData.priceUsd).toFixed(6) : '-');
                $('#tokenPriceNative').text(dexscreenerData.priceNative ? parseFloat(dexscreenerData.priceNative).toFixed(6) + ' SOL' : '-');

                // Liquidity and Market Cap
                const liquidity = dexscreenerData.liquidity?.usd || 0;
                $('#tokenLiquidity').text(liquidity ? '$' + formatNumber(liquidity) : '-');

                const marketCap = dexscreenerData.fdv || dexscreenerData.marketCap || 0;
                $('#tokenMarketCap').text(marketCap ? '$' + formatNumber(marketCap) : '-');

                // Volume Information
                const volume24h = dexscreenerData.volume?.h24 || 0;
                const volume6h = dexscreenerData.volume?.h6 || 0;
                const volume1h = dexscreenerData.volume?.h1 || 0;

                $('#tokenVolume24h').text(volume24h ? '$' + formatNumber(volume24h) : '-');
                $('#tokenVolume6h').text(volume6h ? '$' + formatNumber(volume6h) : '-');
                $('#tokenVolume1h').text(volume1h ? '$' + formatNumber(volume1h) : '-');

                // Transaction counts
                const txns24h = (dexscreenerData.txns?.h24?.buys || 0) + (dexscreenerData.txns?.h24?.sells || 0);
                $('#tokenTransactions24h').text(txns24h || '-');

                // Price Changes with color coding
                updatePriceChange('#tokenPriceChange5m', dexscreenerData.priceChange?.m5);
                updatePriceChange('#tokenPriceChange1h', dexscreenerData.priceChange?.h1);
                updatePriceChange('#tokenPriceChange6h', dexscreenerData.priceChange?.h6);
                updatePriceChange('#tokenPriceChange24h', dexscreenerData.priceChange?.h24);

                // Trading Activity
                $('#tokenBuys24h').text(dexscreenerData.txns?.h24?.buys || '-');
                $('#tokenSells24h').text(dexscreenerData.txns?.h24?.sells || '-');
                $('#tokenBuys6h').text(dexscreenerData.txns?.h6?.buys || '-');
                $('#tokenSells6h').text(dexscreenerData.txns?.h6?.sells || '-');
                $('#tokenBuys1h').text(dexscreenerData.txns?.h1?.buys || '-');
                $('#tokenSells1h').text(dexscreenerData.txns?.h1?.sells || '-');

                // Show the Token Analytics card
                $('#tokenAnalyticsCard').show();

                console.log('Token Analytics updated successfully');

            } catch (error) {
                console.error('Error updating Token Analytics:', error);
            }
        }

        /**
         * 🔥 ENHANCED: Update Account Details UI with Solana.fm Enhanced Data
         */
        function updateAccountDetailsUI(accountData, solanafmData) {
            console.log('🔥 UPDATING ACCOUNT DETAILS with Solana.fm data:');
            console.log('Account Data:', accountData);
            console.log('Solana.fm Data:', solanafmData);

            try {
                if (accountData && accountData.success) {
                    if (accountData.is_token) {
                        // Enhanced token display with Solana.fm data
                        $('#accOwner').text(accountData.account_type || 'Token Mint');
                        $('#accExecutable').text(accountData.executable || 'Token Program');

                        // Show enhanced token information from Solana.fm
                        if (solanafmData && solanafmData.tokenList) {
                            const tokenInfo = solanafmData.tokenList;
                            $('#accDataSize').html(`<strong>${tokenInfo.name || 'Unknown Token'}</strong>`);
                            $('#accRentEpoch').html(`${tokenInfo.symbol || 'Unknown'} • ${solanafmData.decimals || 'Unknown'} decimals`);

                            console.log('✅ Enhanced token display:', {
                                name: tokenInfo.name,
                                symbol: tokenInfo.symbol,
                                decimals: solanafmData.decimals
                            });
                        } else if (accountData.token_name && accountData.token_name !== 'Unknown Token') {
                            $('#accDataSize').text(accountData.token_name);
                            $('#accRentEpoch').text((accountData.token_symbol || 'Unknown') + ' • ' + (accountData.decimals || 'Unknown') + ' decimals');
                        } else {
                            $('#accDataSize').text(accountData.decimals !== 'Unknown' ? accountData.decimals + ' decimals' : 'Unknown');
                            $('#accRentEpoch').text(accountData.supply !== 'Unknown' ? accountData.supply : 'Unknown');
                        }

                        // Update labels for enhanced token view
                        $('#accountDetailsCard .metric-label').eq(0).text('Type:');
                        $('#accountDetailsCard .metric-label').eq(1).text('Program:');
                        $('#accountDetailsCard .metric-label').eq(2).text('Token:');
                        $('#accountDetailsCard .metric-label').eq(3).text('Details:');

                        console.log('✅ Account Details updated for token with Solana.fm data');
                    } else {
                        // Display wallet-specific information
                        $('#accOwner').text(accountData.owner || 'Unknown');
                        $('#accExecutable').text(accountData.executable || 'Unknown');
                        $('#accDataSize').text(accountData.data_size || 'Unknown');
                        $('#accRentEpoch').text(accountData.rent_epoch || 'Unknown');

                        // Reset labels for wallet view
                        $('#accountDetailsCard .metric-label').eq(0).text('Owner:');
                        $('#accountDetailsCard .metric-label').eq(1).text('Executable:');
                        $('#accountDetailsCard .metric-label').eq(2).text('Data Size:');
                        $('#accountDetailsCard .metric-label').eq(3).text('Rent Epoch:');

                        console.log('✅ Account Details updated for wallet/account');
                    }

                } else {
                    // Handle error case
                    $('#accOwner').text('Error');
                    $('#accExecutable').text('Unknown');
                    $('#accDataSize').text('Unknown');
                    $('#accRentEpoch').text('Unknown');

                    console.error('❌ Account data error:', accountData?.error || 'Unknown error');
                }

            } catch (error) {
                console.error('❌ Error updating Account Details UI:', error);
            }
        }

        /**
         * 🔥 COMPLETE: Update Rug Pull Risk UI with REAL Solana.fm Authority Data
         * EXACT STYLING FORMAT IMPLEMENTATION
         */
        /**
         * 🔥 FIXED: Update Rug Pull Risk UI with REAL Solana.fm Authority Data
         * Replace the existing updateRugPullRiskUI function with this
         */
        /**
         * FIXED: Update Account Details UI - Shows different data for each token
         */
        function updateAccountDetailsUI(accountData, solanafmData) {
            console.log('🔥 UPDATING ACCOUNT DETAILS for new address');
            console.log('Account Data:', accountData);

            try {
                if (accountData && accountData.success) {
                    // ALWAYS use the fresh RPC data from backend
                    const owner = accountData.owner || 'Unknown';
                    const executable = accountData.executable || 'Unknown';
                    const dataSize = accountData.data_size || 'Unknown';
                    const rentEpoch = accountData.rent_epoch || 'Unknown';

                    // Update with REAL data from RPC call
                    $('#accOwner').text(owner);
                    $('#accExecutable').text(executable);
                    $('#accDataSize').text(dataSize);
                    $('#accRentEpoch').text(rentEpoch);

                    // Update labels based on account type
                    if (accountData.is_token) {
                        // Update labels for token view
                        $('#accountDetailsCard .metric-label').eq(0).text('Owner:');
                        $('#accountDetailsCard .metric-label').eq(1).text('Executable:');
                        $('#accountDetailsCard .metric-label').eq(2).text('Data Size:');
                        $('#accountDetailsCard .metric-label').eq(3).text('Rent Epoch:');

                        console.log('✅ Token account updated:', {
                            owner: owner,
                            executable: executable,
                            dataSize: dataSize,
                            rentEpoch: rentEpoch
                        });
                    } else {
                        // Update labels for wallet view
                        $('#accountDetailsCard .metric-label').eq(0).text('Owner:');
                        $('#accountDetailsCard .metric-label').eq(1).text('Executable:');
                        $('#accountDetailsCard .metric-label').eq(2).text('Data Size:');
                        $('#accountDetailsCard .metric-label').eq(3).text('Rent Epoch:');

                        console.log('✅ Wallet account updated:', {
                            owner: owner,
                            executable: executable,
                            dataSize: dataSize,
                            rentEpoch: rentEpoch
                        });
                    }

                } else {
                    // Handle error case
                    $('#accOwner').text('Error');
                    $('#accExecutable').text('Unknown');
                    $('#accDataSize').text('Unknown');
                    $('#accRentEpoch').text('Unknown');
                    console.error('❌ Account data error:', accountData?.error || 'Unknown error');
                }

            } catch (error) {
                console.error('❌ Error updating Account Details UI:', error);
                // Reset to error state
                $('#accOwner').text('Error');
                $('#accExecutable').text('Error');
                $('#accDataSize').text('Error');
                $('#accRentEpoch').text('Error');
            }
        }
// Note: Add the authority display CSS styles to your main.css file
        /**
         * Update the results UI with fetched data
         * ENHANCED: Now includes real Solana.fm Authority data integration
         */
        function populateResults(data) {
            console.log('🚀 POPULATING RESULTS with REAL Solana.fm authority data:');
            console.log('Full Response Data:', data);

            // Check if we have Solana.fm data
            if (data.solanafm_data) {
                console.log('🎯 Solana.fm API Data Available:', {
                    tokenName: data.solanafm_data.tokenList?.name,
                    tokenSymbol: data.solanafm_data.tokenList?.symbol,
                    mintAuthority: data.solanafm_data.mintAuthority,
                    freezeAuthority: data.solanafm_data.freezeAuthority,
                    decimals: data.solanafm_data.decimals
                });
            } else {
                console.warn('⚠️ No Solana.fm data in response - authority analysis will be limited');
            }

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

            // 2. TOKEN ANALYTICS CARD - Show for valid tokens
            if (data.dexscreener_data || data.token_analytics) {
                updateTokenAnalyticsUI(data.token_analytics, data.dexscreener_data);
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

                // Populate recent transactions list
                const $txList = $('#recentTransactionsList').empty();
                if (ta.recent_transactions && ta.recent_transactions.length > 0) {
                    ta.recent_transactions.forEach(tx => {
                        const $item = $('<div class="recent-transaction-item"></div>');
                        $item.html(`
                            <div class="tx-type">Type: ${tx.type || 'Unknown'}</div>
                            <div class="tx-signature">Signature: ${tx.signature || 'N/A'}</div>
                            <div class="tx-amount">${tx.description || 'Transaction'}</div>
                            <div class="tx-time">${tx.date || 'Unknown'}</div>
                        `);
                        $txList.append($item);
                    });
                } else {
                    $txList.append('<p>No recent transactions found.</p>');
                }
                $('#transactionAnalysisCard').show();
            }

            // 5. ACCOUNT DETAILS & SECURITY ANALYSIS (Grid Layout)
            let accountSecurityVisible = false;

            // 🔥 ENHANCED: Account Details with Solana.fm Enhanced Data
            if (data.account) {
                updateAccountDetailsUI(data.account, data.solanafm_data);
                $('#accountDetailsCard').show();
                accountSecurityVisible = true;
            }

            // Security Analysis
            if (data.security) {
                const sa = data.security;
                $('#secRiskLevel').text(sa.risk_level || 'Unknown')
                    .css('color', sa.risk_level === 'Low' ? '#10b981' :
                        sa.risk_level === 'High' ? '#ef4444' : '#f59e0b');

                $('#knownScamStatus').text(sa.known_scam.text || 'Unknown')
                    .css('color', sa.known_scam.isScam ? '#ef4444' : '#10b981');

                $('#suspiciousActivity').text(sa.suspicious_activity.text || 'Unknown')
                    .css('color', sa.suspicious_activity.found ? '#ef4444' : '#10b981');

                $('#securityAnalysisCard').show();
                accountSecurityVisible = true;
            }

            // Show account/security grid if either has data
            if (accountSecurityVisible) {
                $('#accountAndSecurityOuterGrid').css('display', 'grid');
            }

            // 6. 🔥 ENHANCED: RUG PULL RISK CARD with REAL Solana.fm Authority Data
            if (data.rugpull) {
                updateRugPullRiskUI(data.rugpull, data.solanafm_data);
                $('#rugPullRiskCard').show();

                console.log('🎯 RUG PULL ANALYSIS COMPLETE with EXACT STYLING FORMAT');
            }

            // 7. WEBSITE & SOCIAL ACCOUNTS CARD
            if (data.social) {
                const ws = data.social;

                // Web info
                if (ws.webInfo) {
                    const web = ws.webInfo;
                    $('#webInfoAddress').text(web.website || 'Not found');
                    $('#webInfoRegDate').text(web.registrationDate || 'Unknown');
                    $('#webInfoRegCountry').text(web.registrationCountry || 'Unknown');
                }

                // Twitter info
                if (ws.twitterInfo) {
                    const twitter = ws.twitterInfo;
                    $('#twitterHandle').text(twitter.handle || 'Not found');
                    $('#twitterVerified')
                        .text(twitter.verified ? 'Yes' : 'No')
                        .css('color', twitter.verified ? '#10b981' : '#ef4444');
                }

                // Telegram info
                if (ws.telegramInfo) {
                    const telegram = ws.telegramInfo;
                    $('#telegramChannel').text(telegram.channel || 'Not found');
                }

                // Discord info
                if (ws.discordInfo) {
                    const discord = ws.discordInfo;
                    $('#discordServer').text(discord.invite || 'Not found');
                    $('#discordName').text(discord.serverName || 'Unknown');
                } else {
                    $('#discordServer').text('Not found');
                    $('#discordName').text('Unknown');
                }

                // GitHub info
                if (ws.githubInfo) {
                    const github = ws.githubInfo;
                    $('#githubRepo').text(github.repository || 'Not found');
                    $('#githubOrg').text(github.organization || 'Unknown');
                } else {
                    $('#githubRepo').text('Not found');
                    $('#githubOrg').text('Unknown');
                }

                $('#websiteSocialCard').show();
            }

            // RECOMMENDED SECURITY TOOLS SECTION
            if ($('#affiliateSection').children().length > 0) {
                $('#affiliateSection').show();
            }

            // 8. FINAL RESULTS CARD
            if (data.scores) {
                const scores = data.scores;
                $('#finalTrustScore').text((scores.trust_score || 0) + '/100');
                $('#finalReliabilityScore').text((scores.activity_score || 0) + '/100');
                $('#finalOverallRating').text((scores.overall_score || 0) + '/100');
                $('#finalSummary').text(scores.recommendation || 'Analysis completed.');

                // Update progress bars
                updateProgressBar('trustScoreBar', scores.trust_score || 0);
                updateProgressBar('reliabilityScoreBar', scores.activity_score || 0);
                updateProgressBar('overallRatingBar', scores.overall_score || 0);

                $('#finalResultsCard').show();
            }

            // Scroll to results smoothly
            $('html, body').animate({
                scrollTop: $('#resultsSection').offset().top - 100
            }, 500);

            console.log('✅ ALL RESULTS POPULATED with EXACT STYLING FORMAT applied');
        }

        /**
         * Initialize charts
         */
        function initializeCharts() {
            const tokenDistCanvas = document.getElementById('tokenDistributionChart');
            if (tokenDistCanvas) {
                const ctx = tokenDistCanvas.getContext('2d');
                window.tokenDistributionChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [],
                            borderWidth: 2,
                            borderColor: '#1f2937'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#9ca3af',
                                    padding: 10,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // ===================================================================
        // EVENT LISTENER FOR THE CHECK BUTTON - SOLANA.FM INTEGRATION
        // ===================================================================
        if ($checkAddressBtn.length && $solanaAddressInput.length) {
            $checkAddressBtn.on('click', function() {
                const address = $solanaAddressInput.val().trim();

                console.log('🚀 SolanaWP: Button clicked with Solana.fm integration, address:', address);

                if (address === '') {
                    resetResultAreas();
                    updateValidationUI({
                        valid: false,
                        message: (typeof solanaWP_ajax_object !== 'undefined' ?
                            solanaWP_ajax_object.error_enter_address :
                            'Please enter a Solana address.')
                    });
                    return;
                }

                setButtonLoading(true);
                resetResultAreas();

                console.log('📡 SolanaWP: Making Solana.fm-enhanced API call for address:', address);

                // Check if AJAX object is available
                if (typeof solanaWP_ajax_object === 'undefined') {
                    console.error('❌ AJAX Error: solanaWP_ajax_object not found.');
                    updateValidationUI({
                        valid: false,
                        message: 'Configuration error. Please refresh the page and try again.'
                    });
                    setButtonLoading(false);
                    return;
                }

                // ===================================================================
                // REAL WORDPRESS AJAX CALL - NOW WITH SOLANA.FM INTEGRATION
                // ===================================================================
                $.ajax({
                    url: solanaWP_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'solanawp_check_address',
                        address: address,
                        nonce: solanaWP_ajax_object.nonce
                    },
                    dataType: 'json',
                    timeout: 60000, // 60 second timeout for multiple API calls
                    beforeSend: function() {
                        console.log('📡 SolanaWP: Sending Solana.fm-enhanced AJAX request...');
                    },
                    success: function(response) {
                        console.log('📥 SolanaWP: Solana.fm-enhanced response received:', response);

                        if (response.success && response.data) {
                            // 🔥 LOG SOLANA.FM AUTHORITY DATA IMMEDIATELY
                            if (response.data.solanafm_data) {
                                console.log('🎯 SOLANA.FM AUTHORITY DATA RECEIVED:');
                                console.log({
                                    token: response.data.solanafm_data.tokenList?.name || 'Unknown',
                                    symbol: response.data.solanafm_data.tokenList?.symbol || 'Unknown',
                                    mintAuthority: response.data.solanafm_data.mintAuthority || 'RENOUNCED',
                                    freezeAuthority: response.data.solanafm_data.freezeAuthority || 'RENOUNCED',
                                    mintRenounced: response.data.solanafm_data.mintAuthority === null,
                                    freezeRenounced: response.data.solanafm_data.freezeAuthority === null,
                                    dataSource: 'solana.fm'
                                });
                            } else {
                                console.warn('⚠️ No Solana.fm data in backend response');
                            }

                            // Use the real data from enhanced backend with Solana.fm authority data
                            populateResults(response.data);
                            console.log('✅ Solana.fm authority data integrated successfully with EXACT STYLING');

                            // Log rug pull risk assessment
                            if (response.data.rugpull) {
                                console.log('🎯 Rug Pull Risk Assessment:', {
                                    riskLevel: response.data.rugpull.risk_level,
                                    riskPercentage: response.data.rugpull.risk_percentage,
                                    mintAuthority: response.data.rugpull.mint_authority,
                                    freezeAuthority: response.data.rugpull.freeze_authority,
                                    overallSafety: response.data.rugpull.ownership_renounced
                                });
                            }
                        } else {
                            // Handle API errors gracefully
                            let errorMessage = 'Error processing address.';
                            if (response.data && response.data.message) {
                                errorMessage = response.data.message;
                            } else if (typeof solanaWP_ajax_object !== 'undefined') {
                                errorMessage = solanaWP_ajax_object.error_general_ajax;
                            }

                            updateValidationUI({
                                valid: false,
                                message: errorMessage
                            });
                            console.error('❌ SolanaWP: Backend Error:', response.data);
                        }
                        setButtonLoading(false);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('❌ SolanaWP: AJAX Error:', textStatus, jqXHR.responseText, errorThrown);

                        let errorMessage = 'Network error occurred while checking the address.';

                        // Provide more specific error messages
                        if (textStatus === 'timeout') {
                            errorMessage = 'Request timed out. The Solana.fm API might be slow. Please try again.';
                        } else if (textStatus === 'parsererror') {
                            errorMessage = 'Server response error. Please try again.';
                        } else if (jqXHR.status === 403) {
                            errorMessage = 'Access denied. Please refresh the page and try again.';
                        } else if (jqXHR.status === 500) {
                            errorMessage = 'Server error occurred. Please try again in a moment.';
                        } else if (jqXHR.status === 0) {
                            errorMessage = 'Connection failed. Please check your internet connection.';
                        }

                        updateValidationUI({
                            valid: false,
                            message: errorMessage
                        });
                        setButtonLoading(false);
                    }
                });
            });
        } else {
            console.error('❌ SolanaWP: Button or input elements not found!');
            console.log('Button found:', $checkAddressBtn.length > 0);
            console.log('Input found:', $solanaAddressInput.length > 0);
        }

        // Handle example button clicks
        $('.example-btn').on('click', function() {
            const address = $(this).data('address');
            if (address && $solanaAddressInput.length) {
                $solanaAddressInput.val(address);
                $checkAddressBtn.trigger('click');
            }
        });

        // Initialize charts
        initializeCharts();

        // Add keyboard support
        $solanaAddressInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $checkAddressBtn.trigger('click');
            }
        });

        // Auto-trim whitespace on input blur
        $solanaAddressInput.on('blur', function() {
            $(this).val($(this).val().trim());
        });

        // 🔥 ENHANCED: Add click-to-copy functionality for authority addresses
        $(document).on('click', '#mintAuthority, #freezeAuthority', function() {
            const title = $(this).attr('title');
            if (title && title.includes('Full Address:')) {
                const address = title.split('Full Address: ')[1].split('\n')[0];
                if (address && address.length > 20) {
                    navigator.clipboard.writeText(address).then(function() {
                        // Show temporary feedback
                        const $element = $(this);
                        const originalText = $element.text();
                        $element.text('Copied!').addClass('copied-feedback');
                        setTimeout(() => {
                            $element.text(originalText).removeClass('copied-feedback');
                        }, 1000);
                    }.bind(this)).catch(function(err) {
                        console.error('Failed to copy address:', err);
                    });
                }
            }
        });

        // Debug info
        console.log('🚀 SolanaWP: Main JavaScript initialized with SOLANA.FM API INTEGRATION');
        console.log('🔥 SolanaWP: Enhanced with REAL Authority Data from Solana.fm API');
        console.log('🎯 SolanaWP: EXACT STYLING FORMAT implemented');
        console.log('✅ SolanaWP: AJAX object available:', typeof solanaWP_ajax_object !== 'undefined');

        if (typeof solanaWP_ajax_object !== 'undefined') {
            console.log('📡 SolanaWP: AJAX URL:', solanaWP_ajax_object.ajax_url);
            console.log('🔐 SolanaWP: Nonce present:', !!solanaWP_ajax_object.nonce);
        }
    });

})(jQuery);
