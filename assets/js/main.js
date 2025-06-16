/**
 * SolanaWP Main JavaScript File - ENHANCED WITH ALL 3 PHASES
 * File location: assets/js/main.js
 *
 * PHASE 1: Enhanced Account Details
 * PHASE 2: Authority Risk Analysis
 * PHASE 3: Token Distribution Analysis
 * Version: COMPLETE ROADMAP IMPLEMENTATION
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

                $('#tokenAnalyticsCard').show();

                console.log('Token Analytics updated successfully');

            } catch (error) {
                console.error('Error updating Token Analytics:', error);
            }
        }

        /**
         * PHASE 1: Enhanced Account Details Update
         */
        function updateEnhancedAccountDetailsUI(accountData) {
            if (!accountData) {
                console.log('No account data available for enhanced display');
                return;
            }

            console.log('Updating enhanced account details with:', accountData);

            try {
                // PHASE 1: Update the 4 required fields from roadmap
                $('#accOwner').text(accountData.owner || 'Unknown');
                $('#accExecutable').text(accountData.executable || 'Unknown');
                $('#accDataSize').text(accountData.data_size ? accountData.data_size + ' bytes' : 'Unknown');
                $('#accRentEpoch').text(accountData.rent_epoch || 'Unknown');
                $('#accAccountType').text(accountData.account_type || 'Unknown');

                // Update metric labels based on account type
                if (accountData.is_token) {
                    updateAccountLabelsForToken();
                } else {
                    updateAccountLabelsForWallet();
                }

                $('#accountDetailsCard').show();
                console.log('Enhanced account details updated successfully');

            } catch (error) {
                console.error('Error updating enhanced account details:', error);
            }
        }

        /**
         * Helper function to update labels for token accounts
         */
        function updateAccountLabelsForToken() {
            const labels = $('#accountDetailsCard .metric-label');
            if (labels.length >= 4) {
                labels.eq(0).text('Program Owner:');
                labels.eq(1).text('Executable:');
                labels.eq(2).text('Data Size:');
                labels.eq(3).text('Rent Epoch:');
            }
        }

        /**
         * Helper function to update labels for wallet accounts
         */
        function updateAccountLabelsForWallet() {
            const labels = $('#accountDetailsCard .metric-label');
            if (labels.length >= 4) {
                labels.eq(0).text('Account Owner:');
                labels.eq(1).text('Executable:');
                labels.eq(2).text('Data Size:');
                labels.eq(3).text('Rent Epoch:');
            }
        }

        /**
         * PHASE 2: Authority Analysis Update
         */
        function updateAuthorityAnalysisUI(authorityData) {
            if (!authorityData) {
                console.log('No authority data available');
                return;
            }

            console.log('Updating authority analysis with:', authorityData);

            try {
                // Update Mint Authority
                if (authorityData.mint_authority) {
                    const mintAuth = authorityData.mint_authority;

                    const mintIcon = mintAuth.status === 'SAFE' ? '✅' : '🚨';
                    $('#mintAuthorityIcon').text(mintIcon);

                    $('#mintAuthorityText')
                        .text(mintAuth.text || 'Unknown')
                        .css('color', mintAuth.color || '#6b7280');

                    $('#mintAuthorityExplanation').text(mintAuth.explanation || 'No explanation available');

                    const $mintContainer = $('#mintAuthorityStatus');
                    $mintContainer.removeClass('risk-low risk-medium risk-high');
                    if (mintAuth.status === 'SAFE') {
                        $mintContainer.addClass('risk-low');
                    } else if (mintAuth.status === 'DANGER') {
                        $mintContainer.addClass('risk-high');
                    }
                }

                // Update Freeze Authority
                if (authorityData.freeze_authority) {
                    const freezeAuth = authorityData.freeze_authority;

                    const freezeIcon = freezeAuth.status === 'SAFE' ? '✅' : '🚨';
                    $('#freezeAuthorityIcon').text(freezeIcon);

                    $('#freezeAuthorityText')
                        .text(freezeAuth.text || 'Unknown')
                        .css('color', freezeAuth.color || '#6b7280');

                    $('#freezeAuthorityExplanation').text(freezeAuth.explanation || 'No explanation available');

                    const $freezeContainer = $('#freezeAuthorityStatus');
                    $freezeContainer.removeClass('risk-low risk-medium risk-high');
                    if (freezeAuth.status === 'SAFE') {
                        $freezeContainer.addClass('risk-low');
                    } else if (freezeAuth.status === 'DANGER') {
                        $freezeContainer.addClass('risk-high');
                    }
                }

                console.log('Authority analysis updated successfully');

            } catch (error) {
                console.error('Error updating authority analysis:', error);
            }
        }

        /**
         * PHASE 3: Token Distribution Analysis Update
         */
        function updateTokenDistributionUI(distributionData) {
            if (!distributionData) {
                console.log('No distribution data available');
                return;
            }

            console.log('Updating token distribution with:', distributionData);

            try {
                // Update concentration metrics
                $('#top1HolderPercentage').text(distributionData.top_1_percentage ? distributionData.top_1_percentage + '%' : '-');
                $('#top5HoldersPercentage').text(distributionData.top_5_percentage ? distributionData.top_5_percentage + '%' : '-');
                $('#top20HoldersPercentage').text(distributionData.top_20_percentage ? distributionData.top_20_percentage + '%' : '-');

                // Update risk assessment
                if (distributionData.risk_assessment) {
                    const riskAssessment = distributionData.risk_assessment;

                    $('#distributionRiskText')
                        .text(riskAssessment.level || 'Unknown')
                        .css('color', riskAssessment.color || '#6b7280');

                    $('#distributionRiskExplanation').text(riskAssessment.explanation || 'No risk assessment available');

                    const $riskContainer = $('#distributionRiskAssessment');
                    $riskContainer.removeClass('risk-low risk-medium risk-high');

                    if (riskAssessment.level && riskAssessment.level.includes('LOW RISK')) {
                        $riskContainer.addClass('risk-low');
                        $('#distributionRiskIcon').text('✅');
                    } else if (riskAssessment.level && riskAssessment.level.includes('MEDIUM RISK')) {
                        $riskContainer.addClass('risk-medium');
                        $('#distributionRiskIcon').text('⚠️');
                    } else if (riskAssessment.level && riskAssessment.level.includes('HIGH RISK')) {
                        $riskContainer.addClass('risk-high');
                        $('#distributionRiskIcon').text('🚨');
                    }
                }

                // Update largest holders list if available
                if (distributionData.largest_holders && distributionData.largest_holders.length > 0) {
                    updateLargestHoldersList(distributionData.largest_holders);
                }

                console.log('Token distribution updated successfully');

            } catch (error) {
                console.error('Error updating token distribution:', error);
            }
        }

        /**
         * Helper function to update largest holders list
         */
        function updateLargestHoldersList(holders) {
            const $distributionContainer = $('#rugTokenDistribution');
            $distributionContainer.empty();

            holders.forEach((holder, index) => {
                if (index < 5) { // Show top 5 holders
                    const $holderItem = $('<div class="distribution-item"></div>');
                    $holderItem.html(`
                        <div style="display: flex; align-items: center;">
                            <span class="dist-color" style="background-color: ${getDistributionColor(holder.percentage)}"></span>
                            <span class="dist-label">Rank ${holder.rank}: ${holder.percentage}%</span>
                        </div>
                        <span class="dist-percentage" style="font-size: 0.8em; color: #6b7280;">
                            ${holder.address ? holder.address.substring(0, 6) + '...' : 'Unknown'}
                        </span>
                    `);
                    $distributionContainer.append($holderItem);
                }
            });
        }

        /**
         * Helper function to get color based on distribution percentage
         */
        function getDistributionColor(percentage) {
            if (percentage > 30) return '#ef4444'; // Red for high concentration
            if (percentage > 15) return '#f59e0b'; // Orange for medium
            if (percentage > 5) return '#3b82f6';  // Blue for moderate
            return '#10b981'; // Green for low/healthy
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
         * Helper function to update warning signs list
         */
        function updateWarningSignsList(warningSigns) {
            const $warnList = $('#rugPullWarningsList').empty();

            if (warningSigns && warningSigns.length > 0) {
                warningSigns.forEach(sign => {
                    $warnList.append(`<li class="warning-item">${sign}</li>`);
                });
            } else {
                $warnList.append('<li class="safe-item">No major warning signs detected</li>');
            }
        }

        /**
         * Helper function to update safe indicators list
         */
        function updateSafeIndicatorsList(safeIndicators) {
            const $safeList = $('#rugPullSafeIndicatorsList').empty();

            if (safeIndicators && safeIndicators.length > 0) {
                safeIndicators.forEach(indicator => {
                    $safeList.append(`<li class="safe-item">${indicator}</li>`);
                });
            } else {
                $safeList.append('<li class="neutral-item">Limited data available for safety assessment</li>');
            }
        }

        /**
         * ENHANCED: Main population function with all 3 phases
         */
        function populateResults(data) {
            console.log('SolanaWP: Populating enhanced results with all phases:', data);

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

            // 2. TOKEN ANALYTICS CARD
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

            // 5. ENHANCED ACCOUNT DETAILS & SECURITY ANALYSIS
            let accountSecurityVisible = false;

            // PHASE 1: Enhanced Account Details
            if (data.account) {
                updateEnhancedAccountDetailsUI(data.account);
                accountSecurityVisible = true;
            }

            // Security Analysis (existing)
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

            // 6. ENHANCED RUG PULL RISK CARD (All 3 Phases)
            if (data.rugpull) {
                const rp = data.rugpull;

                // Update risk level with proper styling
                $('#rugRiskLevel').text(rp.risk_level || 'Unknown');

                // Apply color coding based on risk level
                if (rp.risk_level === 'High') {
                    $('#rugRiskLevel').css({
                        'background': '#fee2e2',
                        'color': '#dc2626'
                    });
                } else if (rp.risk_level === 'Medium') {
                    $('#rugRiskLevel').css({
                        'background': '#fef3c7',
                        'color': '#d97706'
                    });
                } else if (rp.risk_level === 'Low') {
                    $('#rugRiskLevel').css({
                        'background': '#dcfce7',
                        'color': '#16a34a'
                    });
                }

                $('#rugOverallScore').text(rp.overall_score || '0');
                $('#rugVolume24h').text(rp.volume_24h || 'Unknown');

                // PHASE 2: Update authority-specific UI elements
                if (rp.mint_authority) {
                    $('#rugMintAuthority')
                        .text(rp.mint_authority.text || 'Unknown')
                        .css('color', rp.mint_authority.color || '#6b7280');
                }

                if (rp.freeze_authority) {
                    $('#rugFreezeAuthority')
                        .text(rp.freeze_authority.text || 'Unknown')
                        .css('color', rp.freeze_authority.color || '#6b7280');
                }

                // Update other existing fields
                if (rp.liquidity_locked) {
                    $('#rugLiquidityLocked')
                        .text(rp.liquidity_locked.text || 'Unknown')
                        .css('color', rp.liquidity_locked.color || '#6b7280');
                }

                if (rp.ownership_renounced) {
                    $('#rugOwnershipRenounced')
                        .text(rp.ownership_renounced.text || 'Unknown')
                        .css('color', rp.ownership_renounced.color || '#6b7280');
                }

                // Update warning signs and safe indicators
                updateWarningSignsList(rp.warning_signs || []);
                updateSafeIndicatorsList(rp.safe_indicators || []);

                // PHASE 3: Update token distribution chart with real data
                if (rp.token_distribution && Array.isArray(rp.token_distribution)) {
                    updateTokenDistributionChart(rp.token_distribution);

                    // Also update the text list
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

                // PHASE 3: Update concentration metrics if available
                if (rp.concentration_metrics) {
                    $('#top1HolderPercentage').text(rp.concentration_metrics.top_1_percentage + '%');
                    $('#top5HoldersPercentage').text(rp.concentration_metrics.top_5_percentage + '%');
                    $('#top20HoldersPercentage').text(rp.concentration_metrics.top_20_percentage + '%');
                }

                $('#rugPullRiskCard').show();
            }

            // PHASE 2: Update authority analysis UI (if available)
            if (data.authority_analysis) {
                updateAuthorityAnalysisUI(data.authority_analysis);
            }

            // PHASE 3: Update distribution analysis UI (if available)
            if (data.distribution_analysis) {
                updateTokenDistributionUI(data.distribution_analysis);
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

                // Twitter info with enhanced fields
                if (ws.twitterInfo) {
                    const twitter = ws.twitterInfo;
                    $('#twitterHandle').text(twitter.handle || 'Not found');
                    $('#twitterVerified')
                        .text(twitter.verified ? 'Yes' : 'No')
                        .css('color', twitter.verified ? '#10b981' : '#ef4444');

                    // Enhanced Twitter fields
                    $('#twitterVerificationType').text(twitter.verificationType || 'Unavailable');
                    $('#twitterVerifiedFollowers').text(twitter.verifiedFollowers || 'Unavailable');
                    $('#twitterSubscriptionType').text(twitter.subscriptionType || 'Unavailable');
                    $('#twitterFollowers').text(twitter.followers || 'Unavailable');
                    $('#twitterIdentityVerification').text(twitter.identityVerification || 'Unavailable');
                    $('#twitterCreationDate').text(twitter.creationDate || 'Unavailable');
                }

                // Other social platforms
                if (ws.telegramInfo) {
                    $('#telegramChannel').text(ws.telegramInfo.channel || 'Not found');
                }

                if (ws.discordInfo) {
                    $('#discordServer').text(ws.discordInfo.invite || 'Not found');
                    $('#discordName').text(ws.discordInfo.serverName || 'Unknown');
                } else {
                    $('#discordServer').text('Not found');
                    $('#discordName').text('Unknown');
                }

                if (ws.githubInfo) {
                    $('#githubRepo').text(ws.githubInfo.repository || 'Not found');
                    $('#githubOrg').text(ws.githubInfo.organization || 'Unknown');
                } else {
                    $('#githubRepo').text('Not found');
                    $('#githubOrg').text('Unknown');
                }

                $('#websiteSocialCard').show();
            }

            // 8. RECOMMENDED SECURITY TOOLS SECTION
            if ($('#affiliateSection').children().length > 0) {
                $('#affiliateSection').show();
            }

            // 9. FINAL RESULTS CARD
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

            // Smooth scroll to results
            $('html, body').animate({
                scrollTop: $('#resultsSection').offset().top - 100
            }, 500);
        }

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

        // ===================================================================
        // EVENT LISTENER FOR THE CHECK BUTTON - ENHANCED API IMPLEMENTATION
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

                console.log('SolanaWP: Making ENHANCED API call for address:', address);

                if (typeof solanaWP_ajax_object === 'undefined') {
                    console.error('AJAX Error: solanaWP_ajax_object not found.');
                    updateValidationUI({
                        valid: false,
                        message: 'Configuration error. Please refresh the page and try again.'
                    });
                    setButtonLoading(false);
                    return;
                }

                // ENHANCED WORDPRESS AJAX CALL WITH ALL 3 PHASES
                $.ajax({
                    url: solanaWP_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'solanawp_check_address',
                        address: address,
                        nonce: solanaWP_ajax_object.nonce
                    },
                    dataType: 'json',
                    timeout: 45000,
                    beforeSend: function() {
                        console.log('SolanaWP: Sending enhanced AJAX request to backend...');
                    },
                    success: function(response) {
                        console.log('SolanaWP: Enhanced backend response received:', response);

                        if (response.success && response.data) {
                            // Use the enhanced data with all 3 phases
                            populateResults(response.data);
                            console.log('SolanaWP: Enhanced blockchain data populated successfully');
                        } else {
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
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $checkAddressBtn.trigger('click');
            }
        });

        // Auto-trim whitespace
        $solanaAddressInput.on('blur', function() {
            $(this).val($(this).val().trim());
        });

        // Debug info
        console.log('SolanaWP: Enhanced Main JavaScript initialized with ALL 3 PHASES');
        console.log('SolanaWP: AJAX object available:', typeof solanaWP_ajax_object !== 'undefined');

        if (typeof solanaWP_ajax_object !== 'undefined') {
            console.log('SolanaWP: AJAX URL:', solanaWP_ajax_object.ajax_url);
            console.log('SolanaWP: Nonce present:', !!solanaWP_ajax_object.nonce);
        }
    });

})(jQuery);
