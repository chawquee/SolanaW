/**
 * SolanaWP Main JavaScript File - ENHANCED WITH NEW RUG PULL LAYOUT & RUGCHECK API
 * File location: assets/js/main.js
 *
 * PHASE 1: Enhanced Account Details
 * PHASE 2: Authority Risk Analysis (RugCheck API)
 * PHASE 3: Token Distribution Analysis (Alchemy API - existing)
 * NEW LAYOUT: Updated Rug Pull Risk Layout Structure with RugCheck integration
 * REMOVED: Security Analysis section (deleted)
 * UPDATED: Added Top Holders support from RugCheck API topHolders count
 * Version: ENHANCED LAYOUT IMPLEMENTATION WITH RUGCHECK API + ALL NEW UPDATES
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
                    } else if (id === 'recentTransactionsList' || id === 'rugTokenDistribution' || id === 'keyRiskIndicators' || id === 'creatorTokensContainer') {
                        $(this).empty().append('<div class="loading-placeholder">' + (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.loading_text || 'Loading...' : 'Loading...') + '</div>');
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
                $('#accOwner').text(accountData.owner || 'Unknown');
                $('#accExecutable').text(accountData.executable || 'Unknown');
                $('#accDataSize').text(accountData.data_size ? accountData.data_size + ' bytes' : 'Unknown');
                $('#accRentEpoch').text(accountData.rent_epoch || 'Unknown');

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
         * UPDATED: Token Distribution Analysis with RugCheck API totalHolders and topHoldersCount
         * Keep existing Alchemy logic for Top 1, Top 5, Top 20 holders and Holder Distribution
         */
        function updateTokenDistributionUI(distributionData, rugCheckData) {
            console.log('Updating token distribution with distribution data:', distributionData);
            console.log('Updating token distribution with RugCheck data:', rugCheckData);

            try {
                // NEW: Update Total Holders from RugCheck API
                if (rugCheckData && typeof rugCheckData.totalHolders !== 'undefined') {
                    $('#totalHoldersCount').text(rugCheckData.totalHolders || '0');
                    console.log('Updated Total Holders from RugCheck:', rugCheckData.totalHolders);
                } else {
                    $('#totalHoldersCount').text('-');
                }

                // NEW: Update Top Holders from RugCheck API topHolders array count
                if (rugCheckData && typeof rugCheckData.topHoldersCount !== 'undefined') {
                    $('#topHoldersCount').text(rugCheckData.topHoldersCount || '0');
                    console.log('Updated Top Holders count from RugCheck:', rugCheckData.topHoldersCount);
                } else {
                    $('#topHoldersCount').text('-');
                }

                // EXISTING: Update concentration metrics from Alchemy API (Keep existing IDs)
                if (distributionData) {
                    $('#concentrationTop1').text(distributionData.top_1_percentage ? distributionData.top_1_percentage + '%' : '-');
                    $('#concentrationTop5').text(distributionData.top_5_percentage ? distributionData.top_5_percentage + '%' : '-');
                    $('#concentrationTop20').text(distributionData.top_20_percentage ? distributionData.top_20_percentage + '%' : '-');

                    // Update risk assessment
                    if (distributionData.risk_assessment) {
                        const riskAssessment = distributionData.risk_assessment;

                        // Update risk level text and color
                        $('#distributionRiskLevel')
                            .text(riskAssessment.level || 'Unknown')
                            .css('color', riskAssessment.color || '#6b7280');

                        // Update explanation
                        $('#distributionRiskExplanation').text(riskAssessment.explanation || 'No risk assessment available');

                        // Apply risk styling to container
                        const $riskContainer = $('#distributionRiskContainer');
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

                    // Update largest holders list if available (Keep existing logic)
                    if (distributionData.largest_holders && distributionData.largest_holders.length > 0) {
                        updateLargestHoldersList(distributionData.largest_holders);
                    }
                }

                console.log('Token distribution updated successfully');

            } catch (error) {
                console.error('Error updating token distribution:', error);
            }
        }

        /**
         * Helper function to update largest holders list (Keep existing logic)
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
         * ENHANCED: Update RugCheck Data with new layout and all new logic
         */
        function updateRugCheckAnalysisUI(rugCheckData) {
            if (!rugCheckData) {
                console.log('No RugCheck data available');
                return;
            }

            console.log('Updating RugCheck analysis with enhanced layout:', rugCheckData);

            try {
                // Enhanced Risk Overview Section with all new logic
                updateRiskOverviewSection(rugCheckData);

                // Security & Liquidity Analysis
                updateSecurityLiquiditySection(rugCheckData);

                // Risk Indicators with "No Known Risks" logic
                updateRiskIndicatorsSection(rugCheckData);

                // Creator History
                updateCreatorHistorySection(rugCheckData.creatorTokens || []);

                // Enhanced Insider Networks Logic
                updateInsiderNetworksSection(rugCheckData);

                // Enhanced Lockers Logic
                updateLockersSection(rugCheckData);

                console.log('Enhanced RugCheck analysis updated successfully');

            } catch (error) {
                console.error('Error updating enhanced RugCheck analysis:', error);
            }
        }

        /**
         * UPDATED: Update Risk Overview Section with all new color and text logic
         */
        function updateRiskOverviewSection(rugCheckData) {
            // Main Risk Score
            const score = rugCheckData.score || rugCheckData.score_normalised || 0;
            $('#rugOverallScore').text(score);

            // NEW: Risks Score Logic - Check if risks array is empty
            const risks = rugCheckData.risks || [];
            if (risks.length === 0) {
                $('#risksScore').text('No Known Risks').css('color', '#10b981'); // Green
            } else {
                // Calculate average risk score if risks exist
                const avgRiskScore = risks.reduce((sum, risk) => sum + (risk.score || 0), 0) / risks.length;
                $('#risksScore').text(Math.round(avgRiskScore)).css('color', '#dc2626'); // Red
            }

            // NEW: Risk Level Logic - Check if risks array is empty
            let riskLevel = 'Unknown';
            let riskClass = '';

            if (risks.length === 0) {
                riskLevel = 'No Known Risks';
                riskClass = 'risk-low';
                $('#rugRiskLevel').css('color', '#10b981'); // Green
            } else {
                // Calculate risk level based on score when risks exist
                if (score <= 30) {
                    riskLevel = 'Low Risk';
                    riskClass = 'risk-low';
                    $('#rugRiskLevel').css('color', '#10b981'); // Green
                } else if (score <= 70) {
                    riskLevel = 'Medium Risk';
                    riskClass = 'risk-medium';
                    $('#rugRiskLevel').css('color', '#f59e0b'); // Orange
                } else {
                    riskLevel = 'High Risk';
                    riskClass = 'risk-high';
                    $('#rugRiskLevel').css('color', '#dc2626'); // Red
                }
            }

            const $riskLevelBadge = $('#rugRiskLevel');
            $riskLevelBadge.text(riskLevel)
                .removeClass('risk-low risk-medium risk-high')
                .addClass(riskClass);

            // NEW: Rugged Status Logic - false = "NO" (green), true = "Yes" (red)
            if (rugCheckData.rugged === true) {
                $('#ruggedStatus').text('Yes').css('color', '#dc2626'); // Red
                if (rugCheckData.detectedAt) {
                    const detectedDate = new Date(rugCheckData.detectedAt);
                    $('#ruggedDate').text('Detected: ' + detectedDate.toLocaleDateString())
                        .css('color', '#dc2626');
                }
            } else if (rugCheckData.rugged === false) {
                $('#ruggedStatus').text('NO').css('color', '#10b981'); // Green
                $('#ruggedDate').text('');
            } else {
                $('#ruggedStatus').text('-').css('color', '#6b7280'); // Gray for unknown
                $('#ruggedDate').text('');
            }

            // NEW: Mutable Status Logic - true = "Yes" (red), false = "No" (green)
            if (rugCheckData.tokenMeta?.mutable === true) {
                $('#mutableStatus').text('Yes').css('color', '#dc2626'); // Red
            } else if (rugCheckData.tokenMeta?.mutable === false) {
                $('#mutableStatus').text('No').css('color', '#10b981'); // Green
            } else {
                $('#mutableStatus').text('-').css('color', '#6b7280'); // Gray for unknown
            }
        }

        /**
         * NEW: Update Insider Networks Section with enhanced logic
         */
        function updateInsiderNetworksSection(rugCheckData) {
            const $container = $('#insiderNetworksContainer');
            const $statusSpan = $('#insidersDetectedStatus');

            // Check if there are insider networks
            const insiderNetworks = rugCheckData.insiderNetworks || [];
            const graphInsidersDetected = rugCheckData.graphInsidersDetected;

            if (!insiderNetworks.length && (!graphInsidersDetected || graphInsidersDetected === 0)) {
                // No insider networks found
                $statusSpan.text('No Insider Networks').css('color', '#10b981'); // Green

                $container.html(`
                    <div class="no-risk-indicator">
                        <span class="icon">✅</span>
                        <span>No insider networks detected</span>
                    </div>
                `);
            } else {
                // Insider networks found
                $statusSpan.text('Yes').css('color', '#dc2626'); // Red

                $container.empty();

                insiderNetworks.forEach((network, index) => {
                    if (index < 3) { // Show max 3 networks
                        const networkDiv = $('<div class="rug-network-item"></div>');

                        networkDiv.html(`
                            <p><strong>Network ID:</strong> ${network.id || 'Unknown'}</p>
                            <p><strong>Network Type:</strong> ${network.type || 'Unknown'}</p>
                            <p><strong>Number of Accounts:</strong> ${network.size || 0}</p>
                            <p><strong>Active Accounts:</strong> ${network.activeAccounts || 0}</p>
                            <p><strong>Amount of Held Tokens:</strong> ${network.tokenAmount ? formatNumber(network.tokenAmount) : '0'}</p>
                        `);

                        $container.append(networkDiv);
                    }
                });

                if (insiderNetworks.length > 3) {
                    $container.append(`
                        <div style="color: #6b7280; font-size: 13px; padding: 8px; text-align: center; font-style: italic; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                            ... and ${insiderNetworks.length - 3} more networks
                        </div>
                    `);
                }
            }
        }

        /**
         * NEW: Update Lockers Section with enhanced logic
         */
        function updateLockersSection(rugCheckData) {
            const $container = $('#lockersContainer');

            // Check if there are lockers
            const lockers = rugCheckData.lockers || {};
            const lockerKeys = Object.keys(lockers);

            if (lockerKeys.length === 0) {
                // No lockers found
                $container.html(`
                    <div class="no-lockers-indicator">
                        <span class="icon">🚨</span>
                        <span>No Lockers</span>
                    </div>
                `);
            } else {
                // Lockers found
                $container.empty();

                lockerKeys.forEach((key, index) => {
                    if (index < 2) { // Show max 2 lockers
                        const locker = lockers[key];
                        const lockerDiv = $('<div class="rug-locker-item"></div>');

                        const unlockDate = locker.unlockDate ?
                            (locker.unlockDate === 0 ? 'Never' : new Date(locker.unlockDate * 1000).toLocaleDateString()) :
                            'Unknown';

                        lockerDiv.html(`
                            <p><strong>Owner:</strong> ${locker.owner || 'Unknown'}</p>
                            <p><strong>Program ID:</strong> ${locker.programID || 'Unknown'}</p>
                            <p><strong>Account of Locked Tokens:</strong> ${locker.tokenAccount || 'Unknown'}</p>
                            <p><strong>Lock Type:</strong> ${locker.type || 'Unknown'}</p>
                            <p><strong>Unlocking Date:</strong> ${unlockDate}</p>
                            <p><strong>Locked Tokens in USDC:</strong> $${locker.usdcLocked ? formatNumber(locker.usdcLocked) : '0'}</p>
                            ${locker.uri ? `<p><strong>More Info:</strong> <a href="${locker.uri}" target="_blank" style="color: #2563eb;">Link</a></p>` : ''}
                        `);

                        $container.append(lockerDiv);
                    }
                });

                if (lockerKeys.length > 2) {
                    $container.append(`
                        <div style="color: #6b7280; font-size: 13px; padding: 8px; text-align: center; font-style: italic; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                            ... and ${lockerKeys.length - 2} more lockers
                        </div>
                    `);
                }
            }
        }

        /**
         * Update Security & Liquidity Section
         */
        function updateSecurityLiquiditySection(rugCheckData) {
            // Liquidity Analysis
            if (rugCheckData.markets && rugCheckData.markets.length > 0) {
                const market = rugCheckData.markets[0];
                const liquidityPct = market.lp?.lpLockedPct || 0;

                updateSecurityMetric('liquidity', {
                    percentage: liquidityPct,
                    status: liquidityPct >= 80 ? 'High Locked' :
                        liquidityPct >= 50 ? 'Partially Locked' : 'Low/No Lock',
                    icon: liquidityPct >= 80 ? '🔒' :
                        liquidityPct >= 50 ? '⚠️' : '🚨',
                    color: liquidityPct >= 80 ? '#10b981' :
                        liquidityPct >= 50 ? '#f59e0b' : '#ef4444',
                    explanation: liquidityPct >= 80 ? 'High percentage of liquidity is locked, reducing rug pull risk.' :
                        liquidityPct >= 50 ? 'Some liquidity is locked, but still poses moderate risk.' :
                            'Most liquidity can be removed at any time - high risk!',
                    percentageText: `${liquidityPct.toFixed(2)}% locked`
                });
            }

            // Mint Authority
            updateAuthorityMetric('mintAuthority', rugCheckData.mintAuthority, {
                nullValue: 'Renounced',
                nullIcon: '✅',
                nullColor: '#10b981',
                nullExplanation: 'Mint authority is renounced - no new tokens can be created.',
                activeValue: 'Active',
                activeIcon: '🚨',
                activeColor: '#ef4444',
                activeExplanation: 'Mint authority is active - creator can mint unlimited tokens!'
            });

            // Freeze Authority
            updateAuthorityMetric('freezeAuthority', rugCheckData.freezeAuthority, {
                nullValue: 'Renounced',
                nullIcon: '✅',
                nullColor: '#10b981',
                nullExplanation: 'Freeze authority is renounced - tokens cannot be frozen.',
                activeValue: 'Active',
                activeIcon: '🚨',
                activeColor: '#ef4444',
                activeExplanation: 'Freeze authority is active - creator can freeze your tokens!'
            });
        }

        /**
         * Helper function to update security metrics
         */
        function updateSecurityMetric(type, data) {
            const containerId = type + 'Container';
            const statusId = type + 'Status';
            const iconId = type + 'Icon';
            const explanationId = type + 'Explanation';
            const percentageId = type + 'Percentage';

            $('#' + statusId).text(data.status).css('color', data.color);
            $('#' + iconId).text(data.icon);
            $('#' + explanationId).text(data.explanation);

            if (data.percentageText && $('#' + percentageId).length) {
                $('#' + percentageId).text(data.percentageText);
            }
        }

        /**
         * Helper function to update authority metrics
         */
        function updateAuthorityMetric(type, authority, config) {
            const statusId = type + 'Status';
            const iconId = type + 'Icon';
            const explanationId = type + 'Explanation';

            if (authority === null || authority === 'null') {
                $('#' + statusId).text(config.nullValue).css('color', config.nullColor);
                $('#' + iconId).text(config.nullIcon);
                $('#' + explanationId).text(config.nullExplanation);
            } else if (authority) {
                $('#' + statusId).text(config.activeValue).css('color', config.activeColor);
                $('#' + iconId).text(config.activeIcon);
                $('#' + explanationId).text(config.activeExplanation);
            } else {
                $('#' + statusId).text('Unknown').css('color', '#6b7280');
                $('#' + iconId).text('❓');
                $('#' + explanationId).text('Unable to determine ' + type.replace(/([A-Z])/g, ' $1').toLowerCase() + ' status.');
            }
        }

        /**
         * UPDATED: Risk Indicators Section - Show "No Known Risks" when risks array is empty
         */
        function updateRiskIndicatorsSection(rugCheckData) {
            const $container = $('#keyRiskIndicators');
            $container.empty();

            const risks = rugCheckData.risks || [];

            if (risks.length === 0) {
                $container.append(`
                    <div class="no-risk-indicator">
                        <span class="icon">✅</span>
                        <span>No Known Risks</span>
                    </div>
                `);
                return;
            }

            risks.forEach(risk => {
                const riskDiv = $('<div class="risk-indicator-item"></div>');

                let bgColor = '#f9fafb';
                let textColor = '#374151';
                let borderColor = '#e5e7eb';
                let icon = 'ℹ️';

                if (risk.level === 'High' || risk.level === 'CRITICAL') {
                    bgColor = '#fef2f2';
                    textColor = '#dc2626';
                    borderColor = '#fecaca';
                    icon = '🚨';
                } else if (risk.level === 'Medium' || risk.level === 'WARNING') {
                    bgColor = '#fefce8';
                    textColor = '#d97706';
                    borderColor = '#fed7aa';
                    icon = '⚠️';
                } else if (risk.level === 'Low' || risk.level === 'INFO') {
                    bgColor = '#f0fdf4';
                    textColor = '#059669';
                    borderColor = '#bbf7d0';
                    icon = '✅';
                }

                riskDiv.css({
                    'background': bgColor,
                    'color': textColor,
                    'border': '2px solid ' + borderColor,
                    'border-radius': '12px',
                    'padding': '16px',
                    'margin-bottom': '12px',
                    'transition': 'all 0.3s ease'
                });

                riskDiv.html(`
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <span style="font-size: 18px;">${icon}</span>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">
                                ${risk.name || 'Risk Factor'}
                                ${risk.score ? `<span style="float: right; font-size: 13px; opacity: 0.8;">(${risk.score})</span>` : ''}
                            </div>
                            <div style="font-size: 14px; line-height: 1.4; opacity: 0.9;">
                                ${risk.description || 'No description available'}
                            </div>
                            ${risk.value ? `<div style="font-size: 13px; margin-top: 6px; font-weight: 600; opacity: 0.8;">Value: ${risk.value}</div>` : ''}
                        </div>
                    </div>
                `);

                // Add hover effect
                riskDiv.hover(
                    function() {
                        $(this).css('transform', 'translateY(-2px)').css('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.15)');
                    },
                    function() {
                        $(this).css('transform', 'translateY(0)').css('box-shadow', 'none');
                    }
                );

                $container.append(riskDiv);
            });
        }

        /**
         * Update Creator History Section with enhanced styling
         */
        function updateCreatorHistorySection(creatorTokens) {
            const $container = $('#creatorTokensContainer');
            $container.empty();

            if (!creatorTokens || creatorTokens.length === 0) {
                $container.append(`
                    <div style="display: flex; align-items: center; gap: 12px; padding: 16px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb;">
                        <span style="font-size: 18px;">👤</span>
                        <span style="color: #6b7280; font-style: italic;">No other tokens found from this creator</span>
                    </div>
                `);
                return;
            }

            creatorTokens.forEach((token, index) => {
                if (index < 3) { // Show max 3 tokens
                    const tokenDiv = $('<div class="creator-token-item"></div>');

                    const createdDate = token.createdAt ? new Date(token.createdAt).toLocaleDateString() : 'Unknown';
                    const marketCap = token.marketCap ? formatNumber(token.marketCap) : 'Unknown';

                    tokenDiv.css({
                        'margin-bottom': '12px',
                        'padding': '16px',
                        'background': 'white',
                        'border': '2px solid #e5e7eb',
                        'border-radius': '12px',
                        'transition': 'all 0.3s ease'
                    });

                    tokenDiv.html(`
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <span style="font-size: 16px;">🪙</span>
                            <span style="font-weight: 700; font-size: 15px; color: #374151;">Token #${index + 1}</span>
                        </div>
                        <div style="font-size: 13px; color: #6b7280; margin-bottom: 6px; line-height: 1.4;">
                            <strong>Created:</strong> ${createdDate} | <strong>Market Cap:</strong> $${marketCap}
                        </div>
                        <div style="font-size: 11px; color: #9ca3af; word-break: break-all; background: #f9fafb; padding: 6px 8px; border-radius: 6px;">
                            ${token.mint || 'Unknown Address'}
                        </div>
                    `);

                    // Add hover effect
                    tokenDiv.hover(
                        function() {
                            $(this).css({
                                'border-color': '#3b82f6',
                                'transform': 'translateY(-1px)',
                                'box-shadow': '0 4px 12px rgba(59, 130, 246, 0.15)'
                            });
                        },
                        function() {
                            $(this).css({
                                'border-color': '#e5e7eb',
                                'transform': 'translateY(0)',
                                'box-shadow': 'none'
                            });
                        }
                    );

                    $container.append(tokenDiv);
                }
            });

            if (creatorTokens.length > 3) {
                $container.append(`
                    <div style="color: #6b7280; font-size: 13px; padding: 8px; text-align: center; font-style: italic; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                        ... and ${creatorTokens.length - 3} more tokens
                    </div>
                `);
            }
        }

        /**
         * Initialize animated loading states when starting analysis
         */
        function initializeLoadingStates() {
            // Show animated loading for insider networks
            showAnimatedLoading('insiderNetworksContainer', 'Analyzing insider networks...');

            // Show animated loading for lockers
            showAnimatedLoading('lockersContainer', 'Loading locker information...');

            // Show animated loading for key risk indicators
            showAnimatedLoading('keyRiskIndicators', 'Analyzing risk factors...');

            // Show animated loading for creator tokens
            showAnimatedLoading('creatorTokensContainer', 'Analyzing creator history...');
        }

        /**
         * Helper function to show animated loading for specific containers
         */
        function showAnimatedLoading(containerId, message) {
            const $container = $('#' + containerId);
            $container.html(`<div class="loading-placeholder animated-loading">${message}</div>`);
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
         * UPDATED: Main population function with enhanced layout and all new updates
         */
        function populateResults(data) {
            console.log('SolanaWP: Populating enhanced results with enhanced rug pull layout:', data);

            // Clear previous results
            $('.card').hide();
            $('#accountAndSecurityOuterGrid').css('display', 'none');

            // Show results section
            $('#resultsSection').show();

            // Initialize animated loading states immediately
            initializeLoadingStates();

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

            // 5. ENHANCED ACCOUNT DETAILS (Keep existing)
            let accountVisible = false;

            // PHASE 1: Enhanced Account Details
            if (data.account) {
                updateEnhancedAccountDetailsUI(data.account);
                accountVisible = true;
            }

            // Show account grid if has data (REMOVED SECURITY ANALYSIS)
            if (accountVisible) {
                $('#accountAndSecurityOuterGrid').css('display', 'grid');
            }

            // 6. ENHANCED RUG PULL RISK CARD with new layout
            if (data.rugpull || data.rugcheck_data) {
                // UPDATED: Pass RugCheck data to distribution update for totalHolders and topHoldersCount
                if (data.distribution_analysis || data.rugcheck_data) {
                    updateTokenDistributionUI(data.distribution_analysis, data.rugcheck_data);
                }

                // Update enhanced RugCheck data
                if (data.rugcheck_data) {
                    updateRugCheckAnalysisUI(data.rugcheck_data);
                }

                // Show the card
                $('#rugPullRiskCard').show();
            }

            // 7. WEBSITE & SOCIAL ACCOUNTS CARD (Keep existing)
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

                console.log('SolanaWP: Making ENHANCED API call with RugCheck integration for address:', address);

                if (typeof solanaWP_ajax_object === 'undefined') {
                    console.error('AJAX Error: solanaWP_ajax_object not found.');
                    updateValidationUI({
                        valid: false,
                        message: 'Configuration error. Please refresh the page and try again.'
                    });
                    setButtonLoading(false);
                    return;
                }

                // ENHANCED WORDPRESS AJAX CALL WITH RUGCHECK API
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
                        console.log('SolanaWP: Sending enhanced AJAX request with RugCheck integration...');
                    },
                    success: function(response) {
                        console.log('SolanaWP: Enhanced backend response with RugCheck received:', response);

                        if (response.success && response.data) {
                            // Use the enhanced data with RugCheck integration
                            populateResults(response.data);
                            console.log('SolanaWP: Enhanced blockchain data with RugCheck populated successfully');
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
        console.log('SolanaWP: Enhanced Main JavaScript initialized with ENHANCED RUG PULL LAYOUT + RUGCHECK API INTEGRATION + TOP HOLDERS + ALL NEW UPDATES');
        console.log('SolanaWP: AJAX object available:', typeof solanaWP_ajax_object !== 'undefined');

        if (typeof solanaWP_ajax_object !== 'undefined') {
            console.log('SolanaWP: AJAX URL:', solanaWP_ajax_object.ajax_url);
            console.log('SolanaWP: Nonce present:', !!solanaWP_ajax_object.nonce);
        }
    });

})(jQuery);
