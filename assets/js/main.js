/**
 * SolanaWP Main JavaScript File - Updated with Website & Social Accounts Section
 * File location: assets/js/main.js
 *
 * Contains functionality for the Solana Address Checker and other theme interactions.
 * Based on the interactions and data display shown in hannisolsvelte.html
 * and the core features outlined in solanacheckerplan.txt.
 * Uses solanaWP_ajax_object localized from inc/enqueue.php for AJAX calls.
 * Version: Updated with Website & Social Accounts functionality - ALWAYS VISIBLE
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
                        $(this).empty().append('<p class="loading-initial-data">' + (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.checking_text : 'Loading data...') + '</p>');
                    } else if (id === 'secKnownScam' || id === 'secSuspiciousActivity') {
                        $(this).empty().append('<span class="value-placeholder">-</span>');
                    }
                }
            });
            $('#addressValidationCard').show();
            $('#validationNoteBanner').hide().removeClass('error-banner success-banner');
            updateValidationUI({ status: 'Pending', format: '-', length: '-', type: '-' });
        }

        // Main function to update the Address Validation UI card
        function updateValidationUI(data) {
            $('#validationStatus').text(data.status || '-');
            $('#validationFormat').text(data.format || '-');
            $('#validationLength').text(data.length || '-');
            $('#validationType').text(data.type || '-');

            const $banner = $('#validationNoteBanner');
            const $bannerText = $('#validationNoteText');
            const $icon = $banner.find('svg');

            if (data.isValid === true) {
                $bannerText.html(data.message || '<strong>Note:</strong> Valid Solana address');
                $icon.attr('class', 'icon text-green'); // From success-banner in hannisolsvelte.html
                $banner.removeClass('error-banner').addClass('success-banner').show();
            } else if (data.isValid === false) {
                $bannerText.html(data.message || '<strong>Note:</strong> Invalid or problematic address');
                $icon.attr('class', 'icon text-yellow'); // Example error icon color
                $banner.removeClass('success-banner').addClass('error-banner').show();
            } else { // Pending or other states
                $banner.hide();
            }
        }

        // Main function to populate all results cards
        /**
         * SolanaWP Main JavaScript File - Fixed Website & Social Accounts Section
         *
         * ONLY the populateResults function is updated to ensure Website & Social works
         */

// Find this section in your assets/js/main.js file and replace the populateResults function:

        function populateResults(data) {
            resetResultAreas();

            if (data.validation) {
                updateValidationUI({
                    status: data.validation.isValid ? 'Valid' : 'Invalid',
                    format: data.validation.format,
                    length: data.validation.length,
                    type: data.validation.type,
                    isValid: data.validation.isValid,
                    message: data.validation.message
                });
            } else {
                updateValidationUI({ isValid: false, message: 'Validation data missing.' });
            }

            if (!data.validation || !data.validation.isValid) {
                return; // Stop if address is not valid
            }

            // Balance & Holdings Card
            if (data.balanceHoldings) {
                const bh = data.balanceHoldings;
                $('#solBalanceValue').text((bh.solBalance !== undefined ? bh.solBalance : '-') + ' SOL');
                $('#solBalanceUsdValue').text((bh.solBalanceUsd !== undefined ? '$' + bh.solBalanceUsd : '$ -') + ' USD');
                $('#tokenCount').text(bh.tokenCount !== undefined ? bh.tokenCount : '-');
                $('#nftCount').text(bh.nftCount !== undefined ? bh.nftCount : '-');
                $('#balanceHoldingsCard').show();
            }

            // Transaction Analysis Card
            if (data.transactionAnalysis) {
                const ta = data.transactionAnalysis;
                $('#totalTransactions').text(ta.totalTransactions !== undefined ? ta.totalTransactions : '-');
                $('#firstActivity').text(ta.firstActivity || '-');
                $('#lastActivity').text(ta.lastActivity || '-');

                const $txList = $('#recentTransactionsList').empty();
                const $txTemplate = $('.recent-transaction-item-template');
                if (ta.recentTransactions && ta.recentTransactions.length > 0) {
                    ta.recentTransactions.forEach(tx => {
                        const $newItem = $txTemplate.clone().removeClass('recent-transaction-item-template').removeAttr('style').addClass('recent-transaction-item');
                        $newItem.find('.tx-type').text('Type: ' + (tx.type || 'N/A'));
                        $newItem.find('.tx-signature').text('Signature: ' + (tx.signature || 'N/A'));
                        $newItem.find('.tx-amount').text(tx.amount || '-');
                        $newItem.find('.tx-time').text(tx.time || '-');
                        $txList.append($newItem);
                    });
                } else {
                    $txList.append($('<p class="no-transactions-message"></p>').text(typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.no_transactions_text || 'No recent transactions found.' : 'No recent transactions found.'));
                }
                $('#transactionAnalysisCard').show();
            }

            // Account Details & Security Analysis Grid
            let accountSecurityGridVisible = false;
            if (data.accountDetails) {
                const ad = data.accountDetails;
                $('#accOwner').text(ad.owner || '-');
                $('#accExecutable').text(ad.executable || '-');
                $('#accDataSize').text(ad.dataSize || '-');
                $('#accRentEpoch').text(ad.rentEpoch || '-');
                $('#accountDetailsCard').show();
                accountSecurityGridVisible = true;
            }
            if (data.securityAnalysis) {
                const sa = data.securityAnalysis;
                $('#secRiskLevel').text(sa.riskLevel || '-').css('color', sa.riskLevel === 'Low' ? '#059669' : (sa.riskLevel === 'High' ? '#dc2626' : '#d97706'));

                const $knownScamEl = $('#secKnownScam').empty();
                const scamIconHtml = sa.knownScam && sa.knownScam.isScam ?
                    '<svg class="icon text-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>' :
                    '<svg class="icon text-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                $knownScamEl.append(scamIconHtml + `<span style="font-weight: 600; color: ${sa.knownScam && sa.knownScam.isScam ? '#d97706' : '#059669'};">${(sa.knownScam ? sa.knownScam.text : '-')}</span>`);

                const $suspiciousActivityEl = $('#secSuspiciousActivity').empty();
                const suspiciousIconHtml = sa.suspiciousActivity && sa.suspiciousActivity.found ?
                    '<svg class="icon text-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>' :
                    '<svg class="icon text-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                $suspiciousActivityEl.append(suspiciousIconHtml + `<span style="font-weight: 600; color: ${sa.suspiciousActivity && sa.suspiciousActivity.found ? '#d97706' : '#059669'};">${(sa.suspiciousActivity ? sa.suspiciousActivity.text : '-')}</span>`);

                $('#securityAnalysisCard').show();
                accountSecurityGridVisible = true;
            }
            if (accountSecurityGridVisible) {
                $('#accountAndSecurityOuterGrid').css('display', 'grid');
            }

            // UPDATE: Find this comment block in assets/js/main.js and replace with:

            // Rug Pull Risk Card
            if (data.rugPullRisk) {
                const rpr = data.rugPullRisk;
                $('#rugOverallScore').text(rpr.overallScore || '-');
                $('#rugRiskLevel').text(rpr.riskLevel || '-'); // Assuming .risk-level-indicator class handles styling
                $('#rugVolume24h').text(rpr.volume24h || '-');
                $('#rugLiquidityLocked').text(rpr.liquidityLocked ? rpr.liquidityLocked.text : '-').css('color', rpr.liquidityLocked ? rpr.liquidityLocked.color : 'inherit');
                $('#rugOwnershipRenounced').text(rpr.ownershipRenounced ? rpr.ownershipRenounced.text : '-').css('color', rpr.ownershipRenounced ? rpr.ownershipRenounced.color : 'inherit');
                $('#rugMintAuthority').text(rpr.mintAuthority ? rpr.mintAuthority.text : '-').css('color', rpr.mintAuthority ? rpr.mintAuthority.color : 'inherit');
                $('#rugFreezeAuthority').text(rpr.freezeAuthority ? rpr.freezeAuthority.text : '-').css('color', rpr.freezeAuthority ? rpr.freezeAuthority.color : 'inherit');

                const $tokenDistEl = $('#rugTokenDistribution').empty();
                const $distTemplate = $('.token-distribution-item-template');
                if (rpr.tokenDistribution && rpr.tokenDistribution.length > 0) {
                    rpr.tokenDistribution.forEach(dist => {
                        const $newItem = $distTemplate.clone().removeClass('token-distribution-item-template').removeAttr('style').addClass('token-distribution-item');
                        $newItem.find('.dist-label').text((dist.label || 'N/A') + ':');
                        $newItem.find('.dist-percentage').text((dist.percentage !== undefined ? dist.percentage : '-') + '%');
                        $newItem.find('.dist-bar').css({ 'background-color': dist.color || '#e5e7eb', 'width': (dist.percentage || 0) + '%' });
                        $tokenDistEl.append($newItem);
                    });
                } else {
                    $tokenDistEl.append($('<p class="no-distribution-message"></p>').text('Token distribution data unavailable.'));
                }
                $('#rugPullRiskCard').show();
            }

            // Website & Social Accounts Card (NOW POSITIONED AFTER RUG PULL RISK)
            if (data.websiteSocial) {
                const ws = data.websiteSocial;

                // Populate web information
                if (ws.webInfo) {
                    $('#webInfoAddress').text(ws.webInfo.website || '-');
                    $('#webInfoRegDate').text(ws.webInfo.registrationDate || '-');
                    $('#webInfoRegCountry').text(ws.webInfo.registrationCountry || '-');
                }

                // Populate telegram information
                if (ws.telegramInfo) {
                    $('#telegramChannel').text(ws.telegramInfo.channel || '-');
                    $('#telegramMembers').text(ws.telegramInfo.members || '-');
                }

                // Populate twitter information
                if (ws.twitterInfo) {
                    $('#twitterHandle').text(ws.twitterInfo.handle || '-');
                    $('#twitterFollowers').text(ws.twitterInfo.followers || '-');
                    const $verifiedEl = $('#twitterVerified');
                    if (ws.twitterInfo.verified === true) {
                        $verifiedEl.text('Yes').css('color', '#059669'); // Green for verified
                    } else if (ws.twitterInfo.verified === false) {
                        $verifiedEl.text('No').css('color', '#dc2626'); // Red for not verified
                    } else {
                        $verifiedEl.text('-').css('color', 'inherit');
                    }
                }

                $('#websiteSocialCard').show();
            }

            // Community Interaction Card (NOW POSITIONED AFTER WEBSITE & SOCIAL)
            if (data.communityInteraction) {
                const ci = data.communityInteraction;
                const $contentEl = $('#communityCardContent').empty();
                $contentEl.append(`
                    <div class="community-stats-grid">
                        <div class="community-stat-item size"><div>${ci.size || '-'}</div><div>Community Size</div><div>${ci.sizeLabel || '-'}</div></div>
                        <div class="community-stat-item engagement"><div>${ci.engagement || '-'}</div><div>Engagement Rate</div><div>${ci.engagementLabel || '-'}</div></div>
                        <div class="community-stat-item growth"><div>${ci.growth || '-'}</div><div>Growth</div><div>${ci.growthLabel || '-'}</div></div>
                        <div class="community-stat-item sentiment"><div>${ci.sentiment || '-'}</div><div>Overall Sentiment</div><div>${ci.sentimentLabel || '-'}</div></div>
                    </div>
                    <div class="community-engagement-grid">
                        <div class="engagement-item likes"><div class="engagement-icon-wrapper"><svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg></div><div><div>${ci.likes || '-'}</div><div>Likes</div></div></div>
                        <div class="engagement-item comments"><div class="engagement-icon-wrapper"><svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></div><div><div>${ci.comments || '-'}</div><div>Comments</div></div></div>
                        <div class="engagement-item shares"><div class="engagement-icon-wrapper"><svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path></svg></div><div><div>${ci.shares || '-'}</div><div>Shares</div></div></div>
                    </div>`);
                let sentimentBarHTML = '';
                if (ci.sentimentBreakdown && ci.sentimentBreakdown.length > 0) {
                    ci.sentimentBreakdown.forEach(s => { sentimentBarHTML += `<div style="background-color: ${s.color || '#ccc'}; width: ${s.percentage || 0}%;" title="${s.label || ''}: ${s.percentage || 0}%"></div>`; });
                    $contentEl.append(`
                        <h4>Community Sentiment Breakdown</h4>
                        <div class="community-sentiment-breakdown">
                            ${ci.sentimentBreakdown.map(s => `<div class="sentiment-item ${s.label ? s.label.toLowerCase() : ''}"><span style="color: ${s.color || 'inherit'};">${s.label || '-'}</span><span>${s.percentage !== undefined ? s.percentage : '-'}%</span></div>`).join('')}
                            <div class="sentiment-bar-container"><div class="sentiment-bar-inner">${sentimentBarHTML}</div></div>
                        </div>`);
                }
                $contentEl.append(`
                    <div class="community-text-grid">
                        <div class="community-text-item"><h5>Recent Mentions</h5>${(ci.recentMentions && ci.recentMentions.length > 0) ? ci.recentMentions.map(m => `<div class="mention-item">"${$('<div/>').text(m).html()}"</div>`).join('') : '<p>No recent mentions.</p>'}</div>
                        <div class="community-text-item"><h5>Trending Keywords</h5><div class="keyword-tags-wrapper">${(ci.trendingKeywords && ci.trendingKeywords.length > 0) ? ci.trendingKeywords.map(k => `<span class="keyword-tag">${$('<div/>').text(k).html()}</span>`).join('') : '<p>No trending keywords.</p>'}</div></div>
                    </div>`);
                $('#communityInteractionCard').show();
            }
            // Community Interaction Card
            if (data.communityInteraction) {
                const ci = data.communityInteraction;
                const $contentEl = $('#communityCardContent').empty();
                $contentEl.append(`
            <div class="community-stats-grid">
                <div class="community-stat-item size"><div>${ci.size || '-'}</div><div>Community Size</div><div>${ci.sizeLabel || '-'}</div></div>
                <div class="community-stat-item engagement"><div>${ci.engagement || '-'}</div><div>Engagement Rate</div><div>${ci.engagementLabel || '-'}</div></div>
                <div class="community-stat-item growth"><div>${ci.growth || '-'}</div><div>Growth</div><div>${ci.growthLabel || '-'}</div></div>
                <div class="community-stat-item sentiment"><div>${ci.sentiment || '-'}</div><div>Overall Sentiment</div><div>${ci.sentimentLabel || '-'}</div></div>
            </div>
            <div class="community-engagement-grid">
                <div class="engagement-item likes"><div class="engagement-icon-wrapper"><svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg></div><div><div>${ci.likes || '-'}</div><div>Likes</div></div></div>
                <div class="engagement-item comments"><div class="engagement-icon-wrapper"><svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></div><div><div>${ci.comments || '-'}</div><div>Comments</div></div></div>
                <div class="engagement-item shares"><div class="engagement-icon-wrapper"><svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path></svg></div><div><div>${ci.shares || '-'}</div><div>Shares</div></div></div>
            </div>`);
                let sentimentBarHTML = '';
                if (ci.sentimentBreakdown && ci.sentimentBreakdown.length > 0) {
                    ci.sentimentBreakdown.forEach(s => { sentimentBarHTML += `<div style="background-color: ${s.color || '#ccc'}; width: ${s.percentage || 0}%;" title="${s.label || ''}: ${s.percentage || 0}%"></div>`; });
                    $contentEl.append(`
                <h4>Community Sentiment Breakdown</h4>
                <div class="community-sentiment-breakdown">
                    ${ci.sentimentBreakdown.map(s => `<div class="sentiment-item ${s.label ? s.label.toLowerCase() : ''}"><span style="color: ${s.color || 'inherit'};">${s.label || '-'}</span><span>${s.percentage !== undefined ? s.percentage : '-'}%</span></div>`).join('')}
                    <div class="sentiment-bar-container"><div class="sentiment-bar-inner">${sentimentBarHTML}</div></div>
                </div>`);
                }
                $contentEl.append(`
            <div class="community-text-grid">
                <div class="community-text-item"><h5>Recent Mentions</h5>${(ci.recentMentions && ci.recentMentions.length > 0) ? ci.recentMentions.map(m => `<div class="mention-item">"${$('<div/>').text(m).html()}"</div>`).join('') : '<p>No recent mentions.</p>'}</div>
                <div class="community-text-item"><h5>Trending Keywords</h5><div class="keyword-tags-wrapper">${(ci.trendingKeywords && ci.trendingKeywords.length > 0) ? ci.trendingKeywords.map(k => `<span class="keyword-tag">${$('<div/>').text(k).html()}</span>`).join('') : '<p>No trending keywords.</p>'}</div></div>
            </div>`);
                $('#communityInteractionCard').show();
            }

            // *** WEBSITE & SOCIAL ACCOUNTS SECTION - FIXED VERSION ***


            // Affiliate Section
            if ($('#affiliateSection').children().length > 0) {
                $('#affiliateSection').show();
            }

            // Final Results Card
            if (data.finalResults) {
                const fr = data.finalResults;
                $('#finalTrustScore').text(fr.trustScore !== undefined ? fr.trustScore : '-');
                $('#finalReliabilityScore').text(fr.reliabilityScore !== undefined ? fr.reliabilityScore : '-');
                $('#finalOverallRating').text(fr.overallRating || '-');
                $('#finalSummaryText').text(fr.summary || 'No summary available.');
                $('#finalResultsCard').show();
            }
        }
        // Event Listener for the Check Button
        if ($checkAddressBtn.length && $solanaAddressInput.length) {
            $checkAddressBtn.on('click', function() {
                const address = $solanaAddressInput.val().trim();

                if (address === '') {
                    resetResultAreas();
                    updateValidationUI({ isValid: false, message: (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.error_enter_address : 'Please enter a Solana address.') });
                    return;
                }

                setButtonLoading(true);
                resetResultAreas();

                // --- ACTUAL API CALL SECTION - DEVELOPER IMPLEMENTATION REQUIRED ---
                console.log('SolanaWP: Checking address:', address);
                console.log('SolanaWP: Developer needs to implement actual API call here using solanaWP_ajax_object.ajax_url and solanaWP_ajax_object.nonce.');

                // --- START OF SIMULATED API RESPONSE (FOR DEMONSTRATION - REPLACE THIS) ---
                setTimeout(function() {
                    console.log('SolanaWP: Simulating API response for:', address);
                    const mockIsValid = (address.length > 30 && (address.startsWith("SoL") || address.length === 44)); // More plausible mock validation
                    const mockData = { //
                        validation: {
                            isValid: mockIsValid,
                            address: address,
                            format: 'Base58 (Simulated)',
                            length: address.length + ' characters',
                            type: mockIsValid ? 'Wallet Account (Simulated)' : 'Unknown (Simulated)',
                            message: mockIsValid ? 'Address format appears valid (Simulated)' : 'Address format seems incorrect or too short (Simulated)'
                        },
                        // MOVED: Website & Social Accounts Mock Data - NOW ALWAYS INCLUDED
                        websiteSocial: {
                            webInfo: {
                                website: 'solanaproject.com',
                                registrationDate: 'March 15, 2021',
                                registrationCountry: 'United States'
                            },
                            telegramInfo: {
                                channel: '@solana_official',
                                members: '45,280'
                            },
                            twitterInfo: {
                                handle: '@solana',
                                followers: '1.2M',
                                verified: true
                            }
                        }
                    };

                    // Only include other sections if mockIsValid is true for this simulation
                    if (mockIsValid) {
                        mockData.balanceHoldings = { solBalance: (Math.random() * 10).toFixed(2), solBalanceUsd: (Math.random() * 200).toFixed(2), tokenCount: Math.floor(Math.random() * 10), nftCount: Math.floor(Math.random() * 5) };
                        mockData.transactionAnalysis = {
                            totalTransactions: Math.floor(Math.random() * 500), firstActivity: '2022/01/15 (Simulated)', lastActivity: '2025/05/28 (Simulated)',
                            recentTransactions: [
                                { type: 'Transfer', signature: 'SIM_SIG_1...' + address.substring(0,3), amount: (Math.random()).toFixed(2) +' SOL', time: '1 hour ago' },
                                { type: 'SPL Token Transfer', signature: 'SIM_SIG_2...' + address.substring(0,3), amount: '100 MOCK', time: '3 hours ago' }
                            ]
                        };
                        mockData.accountDetails = { owner: address.substring(0,10) + '... (Owner)', executable: 'No', dataSize: Math.floor(Math.random()*100+50) + ' bytes', rentEpoch: Math.floor(Math.random()*500+100) };
                        mockData.securityAnalysis = {
                            riskLevel: ['Low', 'Medium', 'High'][Math.floor(Math.random()*3)],
                            knownScam: { isScam: Math.random() > 0.8, text: (Math.random() > 0.8 ? 'Potential Scam (Sim.)' : 'Not a known scam (Sim.)') },
                            suspiciousActivity: { found: Math.random() > 0.7, text: (Math.random() > 0.7 ? 'Some flags (Sim.)' : 'No suspicious activity (Sim.)') }
                        };
                        mockData.rugPullRisk = {
                            overallScore: Math.floor(Math.random()*100) + '/100', riskLevel: ['Low', 'Medium', 'High'][Math.floor(Math.random()*3)], volume24h: '$' + (Math.random()*2).toFixed(1) + 'M',
                            liquidityLocked: { value: Math.random() > 0.3, text: Math.random() > 0.3 ? 'Yes' : 'No', color: Math.random() > 0.3 ? '#059669' : '#dc2626' },
                            ownershipRenounced: { value: Math.random() > 0.6, text: Math.random() > 0.6 ? 'Yes' : 'No', color: Math.random() > 0.6 ? '#059669' : '#dc2626' },
                            mintAuthority: { value: Math.random() > 0.4, text: Math.random() > 0.4 ? 'Active' : 'Disabled', color: Math.random() > 0.4 ? '#d97706' : '#059669' },
                            freezeAuthority: { value: Math.random() < 0.2, text: Math.random() < 0.2 ? 'Active' : 'Disabled', color: Math.random() < 0.2 ? '#dc2626' : '#059669' },
                            tokenDistribution: [
                                { label: 'Top 10 Holders', percentage: Math.floor(Math.random()*30+20), color: '#7c3aed' },
                                { label: 'Liquidity Pool', percentage: Math.floor(Math.random()*30+20), color: '#2563eb' }
                            ]
                        };
                        mockData.communityInteraction = {
                            size: (Math.random()*20).toFixed(1) + 'K', sizeLabel: 'Sim. Members', engagement: ['Low', 'Medium', 'High'][Math.floor(Math.random()*3)], engagementLabel: 'Sim. Activity',
                            growth: (Math.random()*20-10).toFixed(0) + '%', growthLabel: 'Sim. Increase', sentiment: ['Negative', 'Neutral', 'Positive'][Math.floor(Math.random()*3)], sentimentLabel: 'Sim. Mood',
                            likes: (Math.random()*5).toFixed(1) + 'K', comments: (Math.random()*3).toFixed(1) + 'K', shares: (Math.random()*2).toFixed(1) + 'K',
                            sentimentBreakdown: [
                                { label: 'Positive', percentage: Math.floor(Math.random()*60+20), color: '#10b981' }, { label: 'Neutral', percentage: Math.floor(Math.random()*30+10), color: '#f59e0b' }, { label: 'Negative', percentage: Math.floor(Math.random()*20+5), color: '#ef4444' }
                            ],
                            recentMentions: ["Simulated mention one.", "Another simulated comment."],
                            trendingKeywords: ["simulated", "test", "data"]
                        };
                        mockData.finalResults = {
                            trustScore: Math.floor(Math.random()*100), reliabilityScore: Math.floor(Math.random()*100), overallRating: ['A+','A','A-','B+','B','B-','C'][Math.floor(Math.random()*7)],
                            summary: "This is a simulated summary based on random data. The address appears to be processed with website and social account information."
                        };
                    }
                    // End of additional mock data for valid address

                    populateResults(mockData);
                    setButtonLoading(false);
                }, 1000 + Math.random() * 1500); // Simulate 1-2.5 second network delay
                // --- END OF SIMULATED API RESPONSE ---

                /*
                // --- START OF ACTUAL WORDPRESS AJAX CALL (EXAMPLE) ---
                if (typeof solanaWP_ajax_object === 'undefined') {
                    alert('AJAX Error: AJAX object not found. Please ensure scripts are loaded correctly.');
                    setButtonLoading(false);
                    return;
                }
                $.ajax({
                    url: solanaWP_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'solanawp_check_solana_address', // Defined in PHP (functions.php or an include)
                        address: address,
                        nonce: solanaWP_ajax_object.nonce      // Security nonce from wp_localize_script
                    },
                    dataType: 'json', // Expect JSON response from server
                    success: function(response) {
                        if(response.success && response.data) {
                            populateResults(response.data);
                        } else {
                            let errorMessage = (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.error_general_ajax : 'Error processing address.');
                            if (response.data && response.data.message) {
                                errorMessage = response.data.message;
                            }
                            updateValidationUI({ isValid: false, message: errorMessage });
                            console.error('Backend Error:', response.data);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX Error:', textStatus, jqXHR.responseText, errorThrown);
                        updateValidationUI({ isValid: false, message: (typeof solanaWP_ajax_object !== 'undefined' ? solanaWP_ajax_object.error_general_ajax : 'Network error or server issue checking address.') });
                    },
                    complete: function() {
                        setButtonLoading(false);
                    }
                });
                // --- END OF ACTUAL WORDPRESS AJAX CALL ---
                */
            });
        }

        // Initialize results area (optional, to show initial state before any search)
        // resetResultAreas();
        // updateValidationUI({status: 'Enter an address to begin.'}); // Or similar initial message

    }); // End Document Ready

})(jQuery);
