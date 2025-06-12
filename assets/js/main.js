/**
 * SolanaWP Main JavaScript File - REAL API Integration
 * File location: assets/js/main.js
 *
 * Contains functionality for the Solana Address Checker with REAL API calls.
 * Uses solanaWP_ajax_object localized from inc/enqueue.php for AJAX calls.
 * Version: REAL API Integration - NO SIMULATION
 */

(function($) { // Use jQuery no-conflict wrapper

    // Document Ready
    $(function() {

        // --- Solana Address Checker Logic ---
        const $checkAddressBtn = $('#checkAddressBtn'); // From template-parts/checker/input-section.php
        const $solanaAddressInput = $('#solanaAddressInput'); // From template-parts/checker/input-section.php
        const $resultsSection = $('#resultsSection'); // From template-address-checker.php

        // Helper to show/hide loading state on button
        function setButtonLoading(isLoading) {
            if (isLoading) {
                $checkAddressBtn.html('<svg class="icon animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2a8.001 8.001 0 0015.357 2M15 15h-5"></path></svg>' + (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.checking_text : 'Checking...')).prop('disabled', true);
            } else {
                // Original button content from input-section.php
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
                        if (!$(this).children(':not(svg)').length) { // Only clear if it's a direct text holder or placeholder span
                            $(this).text('-');
                        }
                    } else if (id === 'recentTransactionsList' || id === 'rugTokenDistribution' || id === 'communityCardContent') {
                        $(this).empty().append('<p class="loading-initial-data">' + (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.loading_text || 'Loading...' : 'Loading...') + '</p>');
                    }
                }
            });
        }

        // Helper to update validation UI - FIXED ELEMENT IDS
        function updateValidationUI(validation) {
            const isValid = validation.isValid || validation.valid;
            const $validationCard = $('#addressValidationCard'); // Correct ID from template

            // Update validation status indicators - CORRECT IDS
            $('#validationStatus').text(isValid ? 'Valid' : 'Invalid');
            $('#validationFormat').text(validation.format || 'Unknown');
            $('#validationLength').text(validation.length || 'Unknown');
            $('#validationType').text(validation.type || 'Unknown');

            // Show validation card
            $validationCard.show();

            // Show success or error banner
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
         * Update the results UI with fetched data
         * This function takes real API data and populates all the result cards
         */
        function populateResults(data) {
            console.log('SolanaWP: Populating results with real API data:', data);

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

            // 2. BALANCE & HOLDINGS CARD
            if (data.balance) {
                const bh = data.balance;
                $('#solBalanceValue').text(bh.sol_balance_formatted || '0 SOL');
                $('#solBalanceUsdValue').text('$' + (bh.sol_balance_usd || '0') + ' USD');
                $('#tokenCount').text(bh.token_count || '0');
                $('#nftCount').text(bh.nft_count || '0');
                $('#balanceHoldingsCard').show();
            }

            // 3. TRANSACTION ANALYSIS CARD
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

            // 4. ACCOUNT DETAILS & SECURITY ANALYSIS (Grid Layout)
            let accountSecurityVisible = false;

            // Account Details
            if (data.account) {
                const ad = data.account;

                // Check if this is a token or wallet
                if (ad.is_token) {
                    // Display token-specific information
                    $('#accOwner').text(ad.account_type || 'Token Mint');
                    $('#accExecutable').text('Token Program');
                    $('#accDataSize').text((ad.decimals || 'Unknown') + ' decimals');
                    $('#accRentEpoch').text((ad.supply || 'Unknown supply'));

                    // Update labels for token view
                    $('#accountDetailsCard .metric-label').eq(0).text('Type:');
                    $('#accountDetailsCard .metric-label').eq(1).text('Program:');
                    $('#accountDetailsCard .metric-label').eq(2).text('Decimals:');
                    $('#accountDetailsCard .metric-label').eq(3).text('Supply:');
                } else {
                    // Display wallet-specific information
                    $('#accOwner').text(ad.owner || 'Unknown');
                    $('#accExecutable').text(ad.executable || 'Unknown');
                    $('#accDataSize').text(ad.data_size || 'Unknown');
                    $('#accRentEpoch').text(ad.rent_epoch || 'Unknown');

                    // Reset labels for wallet view
                    $('#accountDetailsCard .metric-label').eq(0).text('Owner:');
                    $('#accountDetailsCard .metric-label').eq(1).text('Executable:');
                    $('#accountDetailsCard .metric-label').eq(2).text('Data Size:');
                    $('#accountDetailsCard .metric-label').eq(3).text('Rent Epoch:');
                }

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

            // 5. RUG PULL RISK CARD
            if (data.rugpull) {
                const rp = data.rugpull;

                // Update risk level with proper styling
                $('#rugPullRiskLevel').text(rp.risk_level || 'Unknown')
                    .removeClass('low medium high')
                    .addClass(rp.risk_level ? rp.risk_level.toLowerCase() : '');

                $('#rugPullRiskPercentage').text((rp.risk_percentage || '0') + '%');

                // Warning signs
                const $warnList = $('#rugPullWarningsList').empty();
                if (rp.warning_signs && rp.warning_signs.length > 0) {
                    rp.warning_signs.forEach(sign => {
                        $warnList.append(`<li class="warning-item">${sign}</li>`);
                    });
                } else {
                    $warnList.append('<li class="safe-item">No warning signs detected</li>');
                }

                // Safe indicators
                const $safeList = $('#rugPullSafeIndicatorsList').empty();
                if (rp.safe_indicators && rp.safe_indicators.length > 0) {
                    rp.safe_indicators.forEach(indicator => {
                        $safeList.append(`<li class="safe-item">${indicator}</li>`);
                    });
                } else {
                    $safeList.append('<li class="neutral-item">No safe indicators found</li>');
                }

                // Update metrics with proper styling
                $('#overallScore').text(rp.overall_score || '0');
                $('#volume24h').text(rp.volume_24h || 'Unknown');

                // FIXED: Properly display authority status with colors
                if (rp.liquidity_locked) {
                    $('#liquidityLocked').text(rp.liquidity_locked.text || 'Unknown')
                        .css('color', rp.liquidity_locked.color || '#6b7280');
                }

                if (rp.ownership_renounced) {
                    $('#ownershipRenounced').text(rp.ownership_renounced.text || 'Unknown')
                        .css('color', rp.ownership_renounced.color || '#6b7280');
                }

                if (rp.mint_authority) {
                    $('#mintAuthority').text(rp.mint_authority.text || 'Unknown')
                        .css('color', rp.mint_authority.color || '#6b7280');
                }

                if (rp.freeze_authority) {
                    $('#freezeAuthority').text(rp.freeze_authority.text || 'Unknown')
                        .css('color', rp.freeze_authority.color || '#6b7280');
                }

                // FIXED: Update token distribution chart with real data
                if (rp.token_distribution && Array.isArray(rp.token_distribution)) {
                    updateTokenDistributionChart(rp.token_distribution);

                    // Also update the text list if it exists
                    const $distList = $('#rugTokenDistribution').empty();
                    rp.token_distribution.forEach(item => {
                        $distList.append(`
                <div class="distribution-item">
                    <span class="dist-color" style="background-color: ${item.color}"></span>
                    <span class="dist-label">${item.label}</span>
                    <span class="dist-percentage">${item.percentage}%</span>
                </div>
            `);
                    });
                }

                $('#rugPullRiskCard').show();
            }


            // 6. WEBSITE & SOCIAL ACCOUNTS CARD
            // Updated Website & Social Accounts section handling
            // Updated Website & Social Accounts section handling
            if (data.social) {
                const ws = data.social;

                // Web info
                if (ws.webInfo) {
                    const web = ws.webInfo;
                    $('#webInfoAddress').text(web.website || 'Not found');
                    $('#webInfoRegDate').text(web.registrationDate || 'Unknown');
                    $('#webInfoRegCountry').text(web.registrationCountry || 'Unknown');
                }

                // Twitter info - UPDATED: Removed followers, kept verified
                if (ws.twitterInfo) {
                    const twitter = ws.twitterInfo;
                    $('#twitterHandle').text(twitter.handle || 'Not found');
                    $('#twitterVerified')
                        .text(twitter.verified ? 'Yes' : 'No')
                        .css('color', twitter.verified ? '#10b981' : '#ef4444');
                }

                // Telegram info - UPDATED: Removed members
                if (ws.telegramInfo) {
                    const telegram = ws.telegramInfo;
                    $('#telegramChannel').text(telegram.channel || 'Not found');
                }

                // NEW: Discord info
                if (ws.discordInfo) {
                    const discord = ws.discordInfo;
                    $('#discordServer').text(discord.invite || 'Not found');
                    $('#discordName').text(discord.serverName || 'Unknown');
                } else {
                    $('#discordServer').text('Not found');
                    $('#discordName').text('Unknown');
                }

                // NEW: GitHub info
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


        // ===================================================================
        // EVENT LISTENER FOR THE CHECK BUTTON - REAL API IMPLEMENTATION
        // ===================================================================
        if ($checkAddressBtn.length && $solanaAddressInput.length) {
            $checkAddressBtn.on('click', function() {
                const address = $solanaAddressInput.val().trim();

                console.log('SolanaWP: Button clicked, address:', address);

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

                console.log('SolanaWP: Making REAL API call for address:', address);

                // Check if AJAX object is available
                if (typeof solanaWP_ajax_object === 'undefined') {
                    console.error('AJAX Error: solanaWP_ajax_object not found.');
                    updateValidationUI({
                        valid: false,
                        message: 'Configuration error. Please refresh the page and try again.'
                    });
                    setButtonLoading(false);
                    return;
                }

                // ===================================================================
                // REAL WORDPRESS AJAX CALL - CONNECTS TO YOUR BACKEND APIs
                // ===================================================================
                $.ajax({
                    url: solanaWP_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'solanawp_check_address',  // This matches your ajax-handlers.php
                        address: address,
                        nonce: solanaWP_ajax_object.nonce
                    },
                    dataType: 'json',
                    timeout: 45000, // 45 second timeout for blockchain API calls
                    beforeSend: function() {
                        console.log('SolanaWP: Sending AJAX request to backend...');
                    },
                    success: function(response) {
                        console.log('SolanaWP: Backend response received:', response);

                        if (response.success && response.data) {
                            // Use the real data from your Helius/QuickNode backend
                            populateResults(response.data);
                            console.log('SolanaWP: Real blockchain data populated successfully');
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
                            console.error('SolanaWP: Backend Error:', response.data);
                        }
                        setButtonLoading(false);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('SolanaWP: AJAX Error:', textStatus, jqXHR.responseText, errorThrown);

                        let errorMessage = 'Network error occurred while checking the address.';

                        // Provide more specific error messages
                        if (textStatus === 'timeout') {
                            errorMessage = 'Request timed out. The Solana network might be slow. Please try again.';
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
            console.error('SolanaWP: Button or input elements not found!');
            console.log('Button found:', $checkAddressBtn.length > 0);
            console.log('Input found:', $solanaAddressInput.length > 0);
        }

        // Handle example button clicks (if they exist in your theme)
        $('.example-btn').on('click', function() {
            const address = $(this).data('address');
            if (address && $solanaAddressInput.length) {
                $solanaAddressInput.val(address);
                $checkAddressBtn.trigger('click');
            }
        });

        // Initialize any charts that might be needed
        initializeCharts();

        /**
         * Initialize charts
         */
        function initializeCharts() {
            // Initialize token distribution chart if canvas exists
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

        // Add keyboard support for better accessibility
        $solanaAddressInput.on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $checkAddressBtn.trigger('click');
            }
        });

        // Auto-trim whitespace on input blur
        $solanaAddressInput.on('blur', function() {
            $(this).val($(this).val().trim());
        });

        // Debug info
        console.log('SolanaWP: Main JavaScript initialized with REAL API integration');
        console.log('SolanaWP: AJAX object available:', typeof solanaWP_ajax_object !== 'undefined');

        if (typeof solanaWP_ajax_object !== 'undefined') {
            console.log('SolanaWP: AJAX URL:', solanaWP_ajax_object.ajax_url);
            console.log('SolanaWP: Nonce present:', !!solanaWP_ajax_object.nonce);
        }
    });

})(jQuery);
