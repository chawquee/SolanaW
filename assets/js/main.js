/**
 * SolanaWP Main JavaScript File - FIXED VERSION
 * File location: assets/js/main.js
 */

(function($) {

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
                    } else if (id === 'recentTransactionsList' || id === 'rugTokenDistribution' || id === 'keyRiskIndicators' || id === 'creatorTokensContainer' || id === 'tokenDistributionChart') {
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

        // Update Token Analytics UI
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

        // Enhanced Account Details Update
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

        function updateAccountLabelsForToken() {
            const labels = $('#accountDetailsCard .metric-label');
            if (labels.length >= 4) {
                labels.eq(0).text('Program Owner:');
                labels.eq(1).text('Executable:');
                labels.eq(2).text('Data Size:');
                labels.eq(3).text('Rent Epoch:');
            }
        }

        function updateAccountLabelsForWallet() {
            const labels = $('#accountDetailsCard .metric-label');
            if (labels.length >= 4) {
                labels.eq(0).text('Account Owner:');
                labels.eq(1).text('Executable:');
                labels.eq(2).text('Data Size:');
                labels.eq(3).text('Rent Epoch:');
            }
        }

        // Standalone Token Distribution Analysis
        function updateTokenDistributionStandalone(distributionData, rugCheckData, moralisData) {
            console.log('Updating standalone token distribution with:', {distributionData, rugCheckData, moralisData});

            try {
                // 1. Holders Distribution Section
                updateHoldersDistribution(distributionData, rugCheckData, moralisData);

                // 2. Top Holders Distribution Section
                updateTopHoldersDistribution(distributionData);

                // 3. Holders Growth Analysis Section
                updateHoldersGrowthAnalysis(moralisData);

                // 4. Holders Categories Section
                updateHoldersCategories(moralisData);

                $('#tokenDistributionCard').show();
                console.log('Standalone token distribution updated successfully');

            } catch (error) {
                console.error('Error updating standalone token distribution:', error);
            }
        }

        function updateHoldersDistribution(distributionData, rugCheckData, moralisData) {
            // Extract Total Holders from Moralis API as requested
            if (moralisData && typeof moralisData.totalHolders !== 'undefined') {
                $('#totalHoldersCount').text(moralisData.totalHolders || '0');
            } else if (rugCheckData && typeof rugCheckData.totalHolders !== 'undefined') {
                // Fallback to RugCheck API if Moralis not available
                $('#totalHoldersCount').text(rugCheckData.totalHolders || '0');
            } else {
                $('#totalHoldersCount').text('-');
            }

            // Concentration metrics from Alchemy API (existing)
            if (distributionData) {
                $('#concentrationTop1').text(distributionData.top_1_percentage ? distributionData.top_1_percentage + '%' : '-');
                $('#concentrationTop5').text(distributionData.top_5_percentage ? distributionData.top_5_percentage + '%' : '-');
                $('#concentrationTop20').text(distributionData.top_20_percentage ? distributionData.top_20_percentage + '%' : '-');
            }

            // Enhanced concentration metrics from Moralis API
            if (moralisData && moralisData.concentration) {
                $('#concentrationTop50').text(moralisData.concentration.top_50_percentage ? moralisData.concentration.top_50_percentage + '%' : '-');
                $('#concentrationTop100').text(moralisData.concentration.top_100_percentage ? moralisData.concentration.top_100_percentage + '%' : '-');
                $('#concentrationTop250').text(moralisData.concentration.top_250_percentage ? moralisData.concentration.top_250_percentage + '%' : '-');
                $('#concentrationTop500').text(moralisData.concentration.top_500_percentage ? moralisData.concentration.top_500_percentage + '%' : '-');
            } else {
                $('#concentrationTop50').text('-');
                $('#concentrationTop100').text('-');
                $('#concentrationTop250').text('-');
                $('#concentrationTop500').text('-');
            }

            // Risk Assessment
            if (distributionData && distributionData.risk_assessment) {
                const riskAssessment = distributionData.risk_assessment;

                $('#distributionRiskLevel')
                    .text(riskAssessment.level || 'Unknown')
                    .css('color', riskAssessment.color || '#6b7280');

                $('#distributionRiskExplanation').text(riskAssessment.explanation || 'No risk assessment available');

                // Apply risk styling
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
        }

        function updateTopHoldersDistribution(distributionData) {
            const $container = $('#tokenDistributionChart');
            $container.empty();

            if (distributionData && distributionData.largest_holders && distributionData.largest_holders.length > 0) {
                distributionData.largest_holders.forEach((holder, index) => {
                    if (index < 10) { // Show top 10 holders
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
                        $container.append($holderItem);
                    }
                });
            } else {
                $container.html('<div class="loading-placeholder">No holder distribution data available</div>');
            }
        }

        function updateHoldersGrowthAnalysis(moralisData) {
            if (moralisData && moralisData.holdersGrowth) {
                const growth = moralisData.holdersGrowth;

                // Update growth changes with color coding and status text
                updateGrowthChangeWithStatus('5m', growth.change_5m, growth.percent_5m);
                updateGrowthChangeWithStatus('1h', growth.change_1h, growth.percent_1h);
                updateGrowthChangeWithStatus('6h', growth.change_6h, growth.percent_6h);
                updateGrowthChangeWithStatus('24h', growth.change_24h, growth.percent_24h);
                updateGrowthChangeWithStatus('3d', growth.change_3d, growth.percent_3d);
                updateGrowthChangeWithStatus('7d', growth.change_7d, growth.percent_7d);
                updateGrowthChangeWithStatus('30d', growth.change_30d, growth.percent_30d);
            } else {
                // Set default values
                const periods = ['5m', '1h', '6h', '24h', '3d', '7d', '30d'];
                periods.forEach(period => {
                    $(`#holdersChange${period}`).text('-');
                    $(`#holdersChangePercent${period}`).text('-');
                    $(`#holdersStatus${period}`).text('').removeClass('holders-joined holders-left');
                });
            }
        }

        function updateHoldersCategories(moralisData) {
            if (moralisData && moralisData.holdersCategories) {
                const categories = moralisData.holdersCategories;

                $('#holdersWhales').text(categories.whales || '0');
                $('#holdersSharks').text(categories.sharks || '0');
                $('#holdersDolphins').text(categories.dolphins || '0');
                $('#holdersFish').text(categories.fish || '0');
                $('#holdersOctopus').text(categories.octopus || '0');
                $('#holdersCrabs').text(categories.crabs || '0');
                $('#holdersShrimps').text(categories.shrimps || '0');
            } else {
                // Set default values
                const categoryTypes = ['Whales', 'Sharks', 'Dolphins', 'Fish', 'Octopus', 'Crabs', 'Shrimps'];
                categoryTypes.forEach(type => {
                    $(`#holders${type}`).text('0');
                });
            }
        }

        function updateGrowthChangeWithStatus(period, changeValue, percentValue) {
            const $changeElement = $(`#holdersChange${period}`);
            const $percentElement = $(`#holdersChangePercent${period}`);
            const $statusElement = $(`#holdersStatus${period}`);

            if (changeValue !== undefined && changeValue !== null) {
                const change = parseInt(changeValue);
                $changeElement.text((change >= 0 ? '+' : '') + change);

                if (change > 0) {
                    $changeElement.css('color', '#10b981'); // Green for positive
                    $statusElement.text('Holders Joined').addClass('holders-joined').removeClass('holders-left');
                } else if (change < 0) {
                    $changeElement.css('color', '#ef4444'); // Red for negative
                    $statusElement.text('Holders Left').addClass('holders-left').removeClass('holders-joined');
                } else {
                    $changeElement.css('color', '#6b7280'); // Gray for neutral
                    $statusElement.text('').removeClass('holders-joined holders-left');
                }
            } else {
                $changeElement.text('-').css('color', '#6b7280');
                $statusElement.text('').removeClass('holders-joined holders-left');
            }

            if (percentValue !== undefined && percentValue !== null) {
                const percent = parseFloat(percentValue).toFixed(1);
                $percentElement.text((percent >= 0 ? '+' : '') + percent + '%');

                if (percent > 0) {
                    $percentElement.css('color', '#10b981'); // Green for positive
                } else if (percent < 0) {
                    $percentElement.css('color', '#ef4444'); // Red for negative
                } else {
                    $percentElement.css('color', '#6b7280'); // Gray for neutral
                }
            } else {
                $percentElement.text('-').css('color', '#6b7280');
            }
        }

        // Enhanced RugCheck Analysis
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

        function updateRiskOverviewSection(rugCheckData) {
            // Main Risk Score
            const score = rugCheckData.score || rugCheckData.score_normalised || 0;
            $('#rugOverallScore').text(score);

            // Risks Score Logic - Check if risks array is empty
            const risks = rugCheckData.risks || [];
            if (risks.length === 0) {
                $('#risksScore').text('No Known Risks').css('color', '#10b981'); // Green
            } else {
                // Calculate average risk score if risks exist
                const avgRiskScore = risks.reduce((sum, risk) => sum + (risk.score || 0), 0) / risks.length;
                $('#risksScore').text(Math.round(avgRiskScore)).css('color', '#dc2626'); // Red
            }

            // Risk Level Logic - Check if risks array is empty
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

            // Rugged Status Logic - false = "NO" (green), true = "Yes" (red)
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

            // Mutable Status Logic - true = "Yes" (red), false = "No" (green)
            if (rugCheckData.tokenMeta?.mutable === true) {
                $('#mutableStatus').text('Yes').css('color', '#dc2626'); // Red
            } else if (rugCheckData.tokenMeta?.mutable === false) {
                $('#mutableStatus').text('No').css('color', '#10b981'); // Green
            } else {
                $('#mutableStatus').text('-').css('color', '#6b7280'); // Gray for unknown
            }
        }

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

                // Determine level display and styling
                let levelDisplay = risk.level || 'Unknown';
                let levelColor = '#374151';
                let levelIcon = 'ℹ️';
                let bgColor = '#f9fafb';
                let borderColor = '#e5e7eb';

                // Special handling for 'warn' level
                if (risk.level === 'warn') {
                    levelDisplay = 'Warning';
                    levelColor = '#dc2626'; // Red color
                    levelIcon = '⚠️'; // Warning sign
                    bgColor = '#fef2f2';
                    borderColor = '#fecaca';
                } else if (risk.level === 'high' || risk.level === 'High' || risk.level === 'CRITICAL') {
                    bgColor = '#fef2f2';
                    borderColor = '#fecaca';
                    levelIcon = '🚨';
                    levelColor = '#dc2626';
                } else if (risk.level === 'medium' || risk.level === 'Medium' || risk.level === 'WARNING') {
                    bgColor = '#fefce8';
                    borderColor = '#fed7aa';
                    levelIcon = '⚠️';
                    levelColor = '#d97706';
                } else if (risk.level === 'low' || risk.level === 'Low' || risk.level === 'INFO') {
                    bgColor = '#f0fdf4';
                    borderColor = '#bbf7d0';
                    levelIcon = '✅';
                    levelColor = '#059669';
                }

                riskDiv.css({
                    'background': bgColor,
                    'border': '2px solid ' + borderColor,
                    'border-radius': '12px',
                    'padding': '16px',
                    'margin-bottom': '12px',
                    'transition': 'all 0.3s ease'
                });

                riskDiv.html(`
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <span style="font-size: 18px; margin-top: 2px;">${levelIcon}</span>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 15px; margin-bottom: 8px; color: #1f2937;">
                                ${risk.name || 'Unknown Risk'}
                            </div>
                            <div style="font-size: 14px; line-height: 1.4; color: #374151; margin-bottom: 8px;">
                                ${risk.description || 'No description available'}
                            </div>
                            <div style="font-size: 13px; font-weight: 600;">
                                <span style="color: #6b7280;">Level: </span>
                                <span style="color: ${levelColor}; font-weight: 700;">${levelDisplay}</span>
                            </div>
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

        // Helper functions
        function getDistributionColor(percentage) {
            if (percentage > 30) return '#ef4444'; // Red for high concentration
            if (percentage > 15) return '#f59e0b'; // Orange for medium
            if (percentage > 5) return '#3b82f6';  // Blue for moderate
            return '#10b981'; // Green for low/healthy
        }

        function formatNumber(num) {
            if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
            if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
            return num.toFixed(2);
        }

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

        // Main population function
        function populateResults(data) {
            console.log('SolanaWP: Populating enhanced results with standalone token distribution:', data);

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

            // 5. ENHANCED ACCOUNT DETAILS
            let accountVisible = false;

            if (data.account) {
                updateEnhancedAccountDetailsUI(data.account);
                accountVisible = true;
            }

            // Show account grid if has data
            if (accountVisible) {
                $('#accountAndSecurityOuterGrid').css('display', 'grid');
            }

            // 6. ENHANCED RUG PULL RISK CARD
            if (data.rugpull || data.rugcheck_data) {
                if (data.rugcheck_data) {
                    updateRugCheckAnalysisUI(data.rugcheck_data);
                }
                $('#rugPullRiskCard').show();
            }

            // 7. STANDALONE TOKEN DISTRIBUTION ANALYSIS CARD
            if (data.distribution_analysis || data.rugcheck_data || data.moralis_data) {
                updateTokenDistributionStandalone(data.distribution_analysis, data.rugcheck_data, data.moralis_data);
            }

            // 8. WEBSITE & SOCIAL ACCOUNTS CARD
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

            // 9. RECOMMENDED SECURITY TOOLS SECTION
            if ($('#affiliateSection').children().length > 0) {
                $('#affiliateSection').show();
            }

            // 10. FINAL RESULTS CARD
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
        // EVENT LISTENER FOR THE CHECK BUTTON - MAIN BUTTON CLICK HANDLER
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

                console.log('SolanaWP: Making AJAX call for address:', address);

                if (typeof solanaWP_ajax_object === 'undefined') {
                    console.error('AJAX Error: solanaWP_ajax_object not found.');
                    updateValidationUI({
                        valid: false,
                        message: 'Configuration error. Please refresh the page and try again.'
                    });
                    setButtonLoading(false);
                    return;
                }

                // WORDPRESS AJAX CALL
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
                        console.log('SolanaWP: Sending AJAX request...');
                    },
                    success: function(response) {
                        console.log('SolanaWP: Backend response received:', response);

                        if (response.success && response.data) {
                            populateResults(response.data);
                            console.log('SolanaWP: Data populated successfully');
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
        console.log('SolanaWP: Main JavaScript initialized');
        console.log('SolanaWP: AJAX object available:', typeof solanaWP_ajax_object !== 'undefined');

        if (typeof solanaWP_ajax_object !== 'undefined') {
            console.log('SolanaWP: AJAX URL:', solanaWP_ajax_object.ajax_url);
            console.log('SolanaWP: Nonce present:', !!solanaWP_ajax_object.nonce);
        }
    });

})(jQuery);
