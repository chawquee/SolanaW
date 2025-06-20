/**
 * SolanaWP Main JavaScript File - ENHANCED VERSION
 * File location: assets/js/main.js
 *
 * UPDATES:
 * - Made social links clickable (Website, X/Twitter, Telegram, Discord, GitHub)
 * - Changed "Not Found"/"Unknown" to "Unavailable"
 * - Added dedicated "Risks" subsection from RugCheck API
 * - Enhanced Risk Indicators with Security & Liquidity issues
 * - Added dynamic time periods for Holders Growth Analysis
 * - FIXED: Token Distribution section visibility and dynamic periods
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
                            $(this).text('Unavailable'); // UPDATED: Changed from '-' to 'Unavailable'
                        }
                    } else if (id === 'recentTransactionsList' || id === 'rugTokenDistribution' || id === 'keyRiskIndicators' || id === 'creatorTokensContainer' || id === 'tokenDistributionChart' || id === 'rugCheckRisksContainer') { // UPDATED: Added rugCheckRisksContainer
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
            $('#validationFormat').text(validation.format || 'Unavailable'); // UPDATED
            $('#validationLength').text(validation.length || 'Unavailable'); // UPDATED
            $('#validationType').text(validation.type || 'Unavailable'); // UPDATED

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

        // UPDATED: Helper function to create clickable social links
        function createClickableLink(text, rawUrl, linkType) {
            // If text is "Unavailable" or similar, return as plain text
            if (!text || text === 'Unavailable' || text === 'Not found' || text === 'Unknown' || text === '-') {
                return 'Unavailable';
            }

            // If we have the raw URL, use it for the link
            let href = rawUrl || '';

            // Handle different link types
            switch(linkType) {
                case 'website':
                    // Ensure website URL has protocol
                    if (href && !href.startsWith('http')) {
                        href = 'https://' + href;
                    }
                    break;
                case 'twitter':
                    // Convert Twitter handle to full URL if needed
                    if (text.startsWith('@')) {
                        const username = text.substring(1);
                        href = href || `https://x.com/${username}`;
                    } else if (href && (href.includes('twitter.com') || href.includes('x.com'))) {
                        // Use provided URL
                    } else {
                        href = `https://x.com/${text}`;
                    }
                    break;
                case 'telegram':
                    // Convert Telegram handle to full URL if needed
                    if (text.startsWith('@')) {
                        const username = text.substring(1);
                        href = href || `https://t.me/${username}`;
                    } else if (href && href.includes('t.me')) {
                        // Use provided URL
                    } else {
                        href = `https://t.me/${text}`;
                    }
                    break;
                case 'discord':
                    // Discord invite links
                    if (text.startsWith('discord.gg/')) {
                        href = href || `https://${text}`;
                    } else if (href && (href.includes('discord.gg') || href.includes('discord.com'))) {
                        // Use provided URL
                    } else {
                        href = `https://discord.gg/${text}`;
                    }
                    break;
                case 'github':
                    // GitHub repository links
                    if (href && href.includes('github.com')) {
                        // Use provided URL
                    } else {
                        href = `https://github.com/${text}`;
                    }
                    break;
            }

            // Return clickable link if we have a valid href
            if (href) {
                return `<a href="${href}" target="_blank" rel="noopener noreferrer" style="color: #3b82f6; text-decoration: none; font-weight: 600;">${text}</a>`;
            }

            return text;
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
                $('#tokenPriceUsd').text(dexscreenerData.priceUsd ? '$' + parseFloat(dexscreenerData.priceUsd).toFixed(6) : 'Unavailable');
                $('#tokenPriceNative').text(dexscreenerData.priceNative ? parseFloat(dexscreenerData.priceNative).toFixed(6) + ' SOL' : 'Unavailable');

                // Liquidity and Market Cap
                const liquidity = dexscreenerData.liquidity?.usd || 0;
                $('#tokenLiquidity').text(liquidity ? '$' + formatNumber(liquidity) : 'Unavailable');

                const marketCap = dexscreenerData.fdv || dexscreenerData.marketCap || 0;
                $('#tokenMarketCap').text(marketCap ? '$' + formatNumber(marketCap) : 'Unavailable');

                // Volume Information
                const volume24h = dexscreenerData.volume?.h24 || 0;
                const volume6h = dexscreenerData.volume?.h6 || 0;
                const volume1h = dexscreenerData.volume?.h1 || 0;

                $('#tokenVolume24h').text(volume24h ? '$' + formatNumber(volume24h) : 'Unavailable');
                $('#tokenVolume6h').text(volume6h ? '$' + formatNumber(volume6h) : 'Unavailable');
                $('#tokenVolume1h').text(volume1h ? '$' + formatNumber(volume1h) : 'Unavailable');

                // Transaction counts
                const txns24h = (dexscreenerData.txns?.h24?.buys || 0) + (dexscreenerData.txns?.h24?.sells || 0);
                $('#tokenTransactions24h').text(txns24h || 'Unavailable');

                // Price Changes with color coding
                updatePriceChange('#tokenPriceChange5m', dexscreenerData.priceChange?.m5);
                updatePriceChange('#tokenPriceChange1h', dexscreenerData.priceChange?.h1);
                updatePriceChange('#tokenPriceChange6h', dexscreenerData.priceChange?.h6);
                updatePriceChange('#tokenPriceChange24h', dexscreenerData.priceChange?.h24);

                // Trading Activity
                $('#tokenBuys24h').text(dexscreenerData.txns?.h24?.buys || 'Unavailable');
                $('#tokenSells24h').text(dexscreenerData.txns?.h24?.sells || 'Unavailable');
                $('#tokenBuys6h').text(dexscreenerData.txns?.h6?.buys || 'Unavailable');
                $('#tokenSells6h').text(dexscreenerData.txns?.h6?.sells || 'Unavailable');
                $('#tokenBuys1h').text(dexscreenerData.txns?.h1?.buys || 'Unavailable');
                $('#tokenSells1h').text(dexscreenerData.txns?.h1?.sells || 'Unavailable');

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
                $('#accOwner').text(accountData.owner || 'Unavailable');
                $('#accExecutable').text(accountData.executable || 'Unavailable');
                $('#accDataSize').text(accountData.data_size ? accountData.data_size + ' bytes' : 'Unavailable');
                $('#accRentEpoch').text(accountData.rent_epoch || 'Unavailable');

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

        // FIXED: Standalone Token Distribution Analysis
        function updateTokenDistributionStandalone(distributionData, rugCheckData, moralisData, dynamicTimePeriodsData) {
            console.log('Updating standalone token distribution with:', {distributionData, rugCheckData, moralisData, dynamicTimePeriodsData});

            try {
                // 1. Holders Distribution Section
                updateHoldersDistribution(distributionData, rugCheckData, moralisData);

                // 2. Top Holders Distribution Section
                updateTopHoldersDistribution(distributionData);

                // 3. FIXED: Holders Growth Analysis Section with Dynamic Time Periods
                updateHoldersGrowthAnalysis(moralisData, dynamicTimePeriodsData);

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
                $('#totalHoldersCount').text('Unavailable');
            }

            // Concentration metrics from Alchemy API (existing)
            if (distributionData) {
                $('#concentrationTop1').text(distributionData.top_1_percentage ? distributionData.top_1_percentage + '%' : 'Unavailable');
                $('#concentrationTop5').text(distributionData.top_5_percentage ? distributionData.top_5_percentage + '%' : 'Unavailable');
                $('#concentrationTop20').text(distributionData.top_20_percentage ? distributionData.top_20_percentage + '%' : 'Unavailable');
            }

            // Enhanced concentration metrics from Moralis API
            if (moralisData && moralisData.concentration) {
                $('#concentrationTop50').text(moralisData.concentration.top_50_percentage ? moralisData.concentration.top_50_percentage + '%' : 'Unavailable');
                $('#concentrationTop100').text(moralisData.concentration.top_100_percentage ? moralisData.concentration.top_100_percentage + '%' : 'Unavailable');
                $('#concentrationTop250').text(moralisData.concentration.top_250_percentage ? moralisData.concentration.top_250_percentage + '%' : 'Unavailable');
                $('#concentrationTop500').text(moralisData.concentration.top_500_percentage ? moralisData.concentration.top_500_percentage + '%' : 'Unavailable');
            } else {
                $('#concentrationTop50').text('Unavailable');
                $('#concentrationTop100').text('Unavailable');
                $('#concentrationTop250').text('Unavailable');
                $('#concentrationTop500').text('Unavailable');
            }

            // Risk Assessment
            if (distributionData && distributionData.risk_assessment) {
                const riskAssessment = distributionData.risk_assessment;

                $('#distributionRiskLevel')
                    .text(riskAssessment.level || 'Unavailable')
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
                                ${holder.address ? holder.address.substring(0, 6) + '...' : 'Unavailable'}
                            </span>
                        `);
                        $container.append($holderItem);
                    }
                });
            } else {
                $container.html('<div class="loading-placeholder">No holder distribution data available</div>');
            }
        }

        // FIXED: Holders Growth Analysis with proper dynamic time periods handling
        function updateHoldersGrowthAnalysis(moralisData, dynamicTimePeriodsData) {
            console.log('updateHoldersGrowthAnalysis called with:', {moralisData, dynamicTimePeriodsData});

            const $container = $('.holders-growth-grid, #dynamicHoldersGrowthGrid');

            if ($container.length === 0) {
                console.error('ERROR: Holders growth container not found! Expected .holders-growth-grid or #dynamicHoldersGrowthGrid');
                return;
            }

            // Clear existing content
            $container.empty();

            // FIXED: Extract time periods from the dynamic time periods object
            let timePeriodsToShow;
            if (dynamicTimePeriodsData && dynamicTimePeriodsData.periods && Array.isArray(dynamicTimePeriodsData.periods)) {
                timePeriodsToShow = dynamicTimePeriodsData.periods;
                console.log('Using dynamic time periods:', timePeriodsToShow);

                // Update debug info if present
                $('#debugActivityDuration').text(`Dt: ${dynamicTimePeriodsData.dt_hours || 0}h`);
                $('#debugPeriodsCount').text(`${timePeriodsToShow.length} periods`);
                $('#debugCalculationMethod').text(dynamicTimePeriodsData.calculation_method || 'unknown');

                // Set data attribute for CSS styling
                $container.attr('data-periods', timePeriodsToShow.length);
            } else {
                // Fallback to default periods
                timePeriodsToShow = ['5m', '1h', '6h', '24h', '3 days', '7 days', '30 days'];
                console.log('Using default time periods (dynamic periods not available):', timePeriodsToShow);

                $('#debugActivityDuration').text('No activity data');
                $('#debugPeriodsCount').text('7 periods (default)');
                $('#debugCalculationMethod').text('default_all_periods');

                $container.attr('data-periods', 7);
            }

            console.log('Final time periods to show:', timePeriodsToShow);

            // Generate dynamic growth metric cards based on calculated time periods
            timePeriodsToShow.forEach(period => {
                const $card = $('<div class="growth-metric-card"></div>');

                // FIXED: Convert period format for data access (handle "3 days" format)
                let dataKey = period;
                if (period === '3 days') dataKey = '3d';
                if (period === '7 days') dataKey = '7d';
                if (period === '30 days') dataKey = '30d';

                $card.html(`
                    <div class="growth-period">${period}</div>
                    <div class="growth-change" id="holdersChange${dataKey}">-</div>
                    <div class="growth-percentage" id="holdersChangePercent${dataKey}">-</div>
                    <div class="holders-status-text" id="holdersStatus${dataKey}"></div>
                `);

                $container.append($card);
            });

            // Now update the data for each period
            if (moralisData && moralisData.holdersGrowth) {
                const growth = moralisData.holdersGrowth;
                console.log('Updating with Moralis growth data:', growth);

                timePeriodsToShow.forEach(period => {
                    let dataKey = period;
                    if (period === '3 days') dataKey = '3d';
                    if (period === '7 days') dataKey = '7d';
                    if (period === '30 days') dataKey = '30d';

                    // Update growth changes with color coding and status text
                    updateGrowthChangeWithStatus(dataKey, growth[`change_${dataKey}`], growth[`percent_${dataKey}`]);
                });
            } else {
                console.log('No Moralis growth data available, setting default values');

                // Set default values for all shown periods
                timePeriodsToShow.forEach(period => {
                    let dataKey = period;
                    if (period === '3 days') dataKey = '3d';
                    if (period === '7 days') dataKey = '7d';
                    if (period === '30 days') dataKey = '30d';

                    $(`#holdersChange${dataKey}`).text('Unavailable');
                    $(`#holdersChangePercent${dataKey}`).text('Unavailable');
                    $(`#holdersStatus${dataKey}`).text('').removeClass('holders-joined holders-left');
                });
            }

            // Apply center alignment to the container
            $container.css({
                'display': 'grid',
                'justify-content': 'center',
                'align-items': 'center'
            });

            console.log('Holders Growth Analysis updated successfully with periods:', timePeriodsToShow);
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
                $changeElement.text('Unavailable').css('color', '#6b7280');
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
                $percentElement.text('Unavailable').css('color', '#6b7280');
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

                // NEW: Dedicated Risks Section from RugCheck API
                updateRugCheckRisksSection(rugCheckData);

                // Security & Liquidity Analysis
                updateSecurityLiquiditySection(rugCheckData);

                // ENHANCED: Risk Indicators with Security & Liquidity issues
                updateEnhancedRiskIndicatorsSection(rugCheckData);

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

            // UPDATED: Risks display - show count of risks or "No Known Risks"
            const risks = rugCheckData.risks || [];
            if (risks.length === 0) {
                $('#rugRisksLevel').text('No Known Risks').css('color', '#10b981'); // Green
            } else {
                $('#rugRisksLevel').text(`${risks.length} Risk${risks.length !== 1 ? 's' : ''}`).css('color', '#dc2626'); // Red
            }

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
                $('#ruggedStatus').text('Unavailable').css('color', '#6b7280'); // Gray for unknown
                $('#ruggedDate').text('');
            }

            // Mutable Status Logic - true = "Yes" (red), false = "No" (green)
            if (rugCheckData.tokenMeta?.mutable === true) {
                $('#mutableStatus').text('Yes').css('color', '#dc2626'); // Red
            } else if (rugCheckData.tokenMeta?.mutable === false) {
                $('#mutableStatus').text('No').css('color', '#10b981'); // Green
            } else {
                $('#mutableStatus').text('Unavailable').css('color', '#6b7280'); // Gray for unknown
            }
        }

        // NEW: Dedicated Risks Section from RugCheck API
        function updateRugCheckRisksSection(rugCheckData) {
            const $container = $('#rugCheckRisksContainer');
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
                const riskDiv = $('<div class="risk-item"></div>');

                riskDiv.css({
                    'background': '#fef2f2',
                    'border': '2px solid #fecaca',
                    'border-radius': '12px',
                    'padding': '16px',
                    'margin-bottom': '12px',
                    'transition': 'all 0.3s ease'
                });

                riskDiv.html(`
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <span style="font-size: 18px; margin-top: 2px; color: #dc2626;">🚨</span>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 15px; margin-bottom: 8px; color: #dc2626;">
                                ${risk.name || 'Unknown Risk'}
                            </div>
                            <div style="font-size: 14px; line-height: 1.4; color: #7f1d1d; margin-bottom: 8px;">
                                ${risk.description || 'No description available'}
                            </div>
                            ${risk.level ? `<div style="font-size: 13px; font-weight: 600;">
                                <span style="color: #6b7280;">Level: </span>
                                <span style="color: #dc2626; font-weight: 700;">${risk.level}</span>
                            </div>` : ''}
                        </div>
                    </div>
                `);

                // Add hover effect
                riskDiv.hover(
                    function() {
                        $(this).css('transform', 'translateY(-2px)').css('box-shadow', '0 4px 12px rgba(220, 38, 38, 0.2)');
                    },
                    function() {
                        $(this).css('transform', 'translateY(0)').css('box-shadow', 'none');
                    }
                );

                $container.append(riskDiv);
            });
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
                $('#' + statusId).text('Unavailable').css('color', '#6b7280');
                $('#' + iconId).text('❓');
                $('#' + explanationId).text('Unable to determine ' + type.replace(/([A-Z])/g, ' $1').toLowerCase() + ' status.');
            }
        }

        // ENHANCED: Risk Indicators with Security & Liquidity issues
        function updateEnhancedRiskIndicatorsSection(rugCheckData) {
            const $container = $('#keyRiskIndicators');
            $container.empty();

            const existingRisks = rugCheckData.risks || [];
            const securityRisks = [];

            // Add Security & Liquidity risks to the indicators
            // 1. Mint Authority risk
            if (rugCheckData.mintAuthority && rugCheckData.mintAuthority !== null && rugCheckData.mintAuthority !== 'null') {
                securityRisks.push({
                    name: 'Mint Authority Active',
                    description: 'The mint authority is still active, allowing the creator to mint unlimited new tokens which could dilute your holdings.',
                    level: 'high',
                    category: 'security'
                });
            }

            // 2. Freeze Authority risk
            if (rugCheckData.freezeAuthority && rugCheckData.freezeAuthority !== null && rugCheckData.freezeAuthority !== 'null') {
                securityRisks.push({
                    name: 'Freeze Authority Active',
                    description: 'The freeze authority is still active, allowing the creator to freeze token accounts and prevent trading.',
                    level: 'high',
                    category: 'security'
                });
            }

            // 3. Liquidity risk
            if (rugCheckData.markets && rugCheckData.markets.length > 0) {
                const market = rugCheckData.markets[0];
                const liquidityPct = market.lp?.lpLockedPct || 0;

                if (liquidityPct < 50) {
                    securityRisks.push({
                        name: 'Low Liquidity Lock',
                        description: `Only ${liquidityPct.toFixed(2)}% of liquidity is locked. Most liquidity can be removed at any time, creating high rug pull risk.`,
                        level: 'high',
                        category: 'liquidity'
                    });
                } else if (liquidityPct < 80) {
                    securityRisks.push({
                        name: 'Partial Liquidity Lock',
                        description: `${liquidityPct.toFixed(2)}% of liquidity is locked. Some liquidity can still be removed, posing moderate risk.`,
                        level: 'medium',
                        category: 'liquidity'
                    });
                }
            }

            // Combine all risks: existing + security
            const allRisks = [...existingRisks, ...securityRisks];

            if (allRisks.length === 0) {
                $container.append(`
                    <div class="no-risk-indicator">
                        <span class="icon">✅</span>
                        <span>No Known Risks</span>
                    </div>
                `);
                return;
            }

            allRisks.forEach(risk => {
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

                // Add category indicator for security risks
                const categoryIndicator = risk.category === 'security' ? '<span style="background: #3b82f6; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 8px;">SECURITY</span>' :
                    risk.category === 'liquidity' ? '<span style="background: #8b5cf6; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 8px;">LIQUIDITY</span>' : '';

                riskDiv.html(`
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <span style="font-size: 18px; margin-top: 2px;">${levelIcon}</span>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 15px; margin-bottom: 8px; color: #1f2937;">
                                ${risk.name || 'Unknown Risk'}${categoryIndicator}
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
                            <p><strong>Network ID:</strong> ${network.id || 'Unavailable'}</p>
                            <p><strong>Network Type:</strong> ${network.type || 'Unavailable'}</p>
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
                            'Unavailable';

                        lockerDiv.html(`
                            <p><strong>Owner:</strong> ${locker.owner || 'Unavailable'}</p>
                            <p><strong>Program ID:</strong> ${locker.programID || 'Unavailable'}</p>
                            <p><strong>Account of Locked Tokens:</strong> ${locker.tokenAccount || 'Unavailable'}</p>
                            <p><strong>Lock Type:</strong> ${locker.type || 'Unavailable'}</p>
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

                    const createdDate = token.createdAt ? new Date(token.createdAt).toLocaleDateString() : 'Unavailable';
                    const marketCap = token.marketCap ? formatNumber(token.marketCap) : 'Unavailable';

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
                            ${token.mint || 'Unavailable Address'}
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
                $element.text('Unavailable').css('color', '#6b7280');
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

        // FIXED: Main population function with proper debug logging
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
                $('#firstActivity').text(ta.first_transaction || 'Unavailable');
                $('#lastActivity').text(ta.last_transaction || 'Unavailable');

                // Populate recent transactions list
                const $txList = $('#recentTransactionsList').empty();
                if (ta.recent_transactions && ta.recent_transactions.length > 0) {
                    ta.recent_transactions.forEach(tx => {
                        const $item = $('<div class="recent-transaction-item"></div>');
                        $item.html(`
                            <div class="tx-type">Type: ${tx.type || 'Unavailable'}</div>
                            <div class="tx-signature">Signature: ${tx.signature || 'N/A'}</div>
                            <div class="tx-amount">${tx.description || 'Transaction'}</div>
                            <div class="tx-time">${tx.date || 'Unavailable'}</div>
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

            // 7. FIXED: STANDALONE TOKEN DISTRIBUTION ANALYSIS CARD WITH DYNAMIC TIME PERIODS
            console.log('🔍 DEBUG: Checking Token Distribution data:', {
                distribution_analysis: !!data.distribution_analysis,
                rugcheck_data: !!data.rugcheck_data,
                moralis_data: !!data.moralis_data,
                dynamic_time_periods: data.dynamic_time_periods
            });

            if (data.distribution_analysis || data.rugcheck_data || data.moralis_data) {
                console.log('✅ Token Distribution: Updating with data...');
                updateTokenDistributionStandalone(
                    data.distribution_analysis,
                    data.rugcheck_data,
                    data.moralis_data,
                    data.dynamic_time_periods  // This should be the object with 'periods' property
                );
            } else {
                console.log('❌ Token Distribution: No data available to update');
            }

            // 8. ENHANCED WEBSITE & SOCIAL ACCOUNTS CARD WITH CLICKABLE LINKS
            if (data.social) {
                const ws = data.social;

                // Web info
                if (ws.webInfo) {
                    const web = ws.webInfo;
                    $('#webInfoAddress').html(createClickableLink(web.website || 'Unavailable', web.website, 'website'));
                    $('#webInfoRegDate').text(web.registrationDate || 'Unavailable');
                    $('#webInfoRegCountry').text(web.registrationCountry || 'Unavailable');
                }

                // Twitter info with enhanced fields and clickable link
                if (ws.twitterInfo) {
                    const twitter = ws.twitterInfo;
                    $('#twitterHandle').html(createClickableLink(twitter.handle || 'Unavailable', twitter.raw_url, 'twitter'));
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

                // Other social platforms with clickable links
                if (ws.telegramInfo) {
                    $('#telegramChannel').html(createClickableLink(ws.telegramInfo.channel || 'Unavailable', ws.telegramInfo.raw_url, 'telegram'));
                }

                if (ws.discordInfo) {
                    $('#discordServer').html(createClickableLink(ws.discordInfo.invite || 'Unavailable', ws.discordInfo.raw_url, 'discord'));
                    $('#discordName').text(ws.discordInfo.serverName || 'Unavailable');
                } else {
                    $('#discordServer').text('Unavailable');
                    $('#discordName').text('Unavailable');
                }

                if (ws.githubInfo) {
                    $('#githubRepo').html(createClickableLink(ws.githubInfo.repository || 'Unavailable', ws.githubInfo.raw_url, 'github'));
                    $('#githubOrg').text(ws.githubInfo.organization || 'Unavailable');
                } else {
                    $('#githubRepo').text('Unavailable');
                    $('#githubOrg').text('Unavailable');
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
