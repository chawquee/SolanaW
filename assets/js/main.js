/**
 * Main JavaScript file for Solana WordPress Plugin
 */

(function($) {
    'use strict';

    // DOM ready
    $(document).ready(function() {
        initializeChecker();
    });

    /**
     * Initialize the Solana address checker
     */
    function initializeChecker() {
        // Handle form submission
        $('#solanaCheckerForm').on('submit', function(e) {
            e.preventDefault();

            const address = $('#solanaAddress').val().trim();

            if (!address) {
                showError('Please enter a Solana address');
                return;
            }

            // Show loading state
            showLoading();

            // Make AJAX request
            $.ajax({
                url: solana_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'check_solana_address',
                    address: address,
                    nonce: solana_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateResults(response.data);
                    } else {
                        showError(response.data || 'An error occurred while checking the address');
                    }
                },
                error: function(xhr, status, error) {
                    showError('Network error: ' + error);
                },
                complete: function() {
                    hideLoading();
                }
            });
        });

        // Handle example button clicks
        $('.example-btn').on('click', function() {
            const address = $(this).data('address');
            $('#solanaAddress').val(address);
            $('#solanaCheckerForm').submit();
        });

        // Initialize any charts or visualizations
        initializeCharts();
    }

    /**
     * Update the results UI with fetched data
     */
    function updateResults(data) {
        console.log('Received data:', data);

        // Clear previous results
        $('.card').hide();
        $('#accountAndSecurityOuterGrid').css('display', 'none');

        // Show results section
        $('#resultsSection').show();

        // Validation Card
        if (data.validation) {
            updateValidationUI({
                isValid: data.validation.valid,
                exists: data.validation.exists,
                format: data.validation.format,
                length: data.validation.length,
                type: data.validation.type,
                message: data.validation.message
            });
        }

        if (!data.validation || !data.validation.valid) {
            return; // Stop if address is not valid
        }

        // Balance & Holdings Card
        if (data.balance) {
            const bh = data.balance;
            $('#solBalanceValue').text(bh.sol_balance_formatted || '0 SOL');
            $('#solBalanceUsdValue').text('$' + (bh.sol_balance_usd || '0') + ' USD');
            $('#tokenCount').text(bh.token_count || '0');
            $('#nftCount').text(bh.nft_count || '0');
            $('#balanceHoldingsCard').show();
        }

        // Transaction Analysis Card
        if (data.transactions) {
            const ta = data.transactions;
            $('#totalTransactions').text(ta.total_transactions || '0');
            $('#firstActivity').text(ta.first_transaction || 'Unknown');
            $('#lastActivity').text(ta.last_transaction || 'Unknown');

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

        // Account Details & Security Analysis
        let accountSecurityVisible = false;
        if (data.account) {
            const ad = data.account;
            $('#accOwner').text(ad.owner || 'Unknown');
            $('#accExecutable').text(ad.executable || 'Unknown');
            $('#accDataSize').text(ad.data_size || 'Unknown');
            $('#accRentEpoch').text(ad.rent_epoch || 'Unknown');
            $('#accountDetailsCard').show();
            accountSecurityVisible = true;
        }

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

        // Rug Pull Risk Card
        if (data.rugpull) {
            const rp = data.rugpull;
            $('#rugPullRiskLevel').text(rp.risk_level || 'Unknown')
                .removeClass('low medium high')
                .addClass(rp.risk_level ? rp.risk_level.toLowerCase() : '');

            $('#rugPullRiskPercentage').text(rp.risk_percentage + '%' || '0%');

            // Warning signs
            const $warnList = $('#rugPullWarningsList').empty();
            if (rp.warning_signs && rp.warning_signs.length > 0) {
                rp.warning_signs.forEach(sign => {
                    $warnList.append(`<li>${sign}</li>`);
                });
            } else {
                $warnList.append('<li>No warning signs detected</li>');
            }

            // Safe indicators
            const $safeList = $('#rugPullSafeIndicatorsList').empty();
            if (rp.safe_indicators && rp.safe_indicators.length > 0) {
                rp.safe_indicators.forEach(indicator => {
                    $safeList.append(`<li>${indicator}</li>`);
                });
            } else {
                $safeList.append('<li>No safe indicators found</li>');
            }

            // Update metrics
            $('#overallScore').text(rp.overall_score || '0');
            $('#volume24h').text(rp.volume_24h || '$0');
            $('#liquidityLocked').text(rp.liquidity_locked.text || 'Unknown')
                .css('color', rp.liquidity_locked.color || '#6b7280');
            $('#ownershipRenounced').text(rp.ownership_renounced.text || 'Unknown')
                .css('color', rp.ownership_renounced.color || '#6b7280');
            $('#mintAuthority').text(rp.mint_authority.text || 'Unknown')
                .css('color', rp.mint_authority.color || '#6b7280');
            $('#freezeAuthority').text(rp.freeze_authority.text || 'Unknown')
                .css('color', rp.freeze_authority.color || '#6b7280');

            // Update token distribution chart if available
            if (rp.token_distribution && window.tokenDistributionChart) {
                updateTokenDistributionChart(rp.token_distribution);
            }

            $('#rugPullRiskCard').show();
        }

        // Website & Social Accounts Card
        if (data.social) {
            const ws = data.social;

            // Website info
            if (ws.websiteUrl) {
                $('#websiteUrl').attr('href', ws.websiteUrl).text(ws.websiteUrl);
                $('#domainAge').text(ws.domainAge || 'Unknown');
                $('#sslSecured').text(ws.sslSecured ? 'Yes' : 'No')
                    .css('color', ws.sslSecured ? '#10b981' : '#ef4444');
            } else {
                $('#websiteUrl').text('No website found');
            }

            // WHOIS info
            if (ws.whoisInfo) {
                const whois = ws.whoisInfo;
                $('#whoisRegistrar').text(whois.registrar || 'Unknown');
                $('#whoisCreated').text(whois.createdDate || 'Unknown');
                $('#whoisExpiry').text(whois.expiryDate || 'Unknown');
                $('#whoisStatus').text(whois.status || 'Unknown');
            }

            // Twitter info
            if (ws.twitterInfo) {
                const twitter = ws.twitterInfo;
                $('#twitterHandle').text(twitter.handle || 'Not found');
                $('#twitterFollowers').text(twitter.followers || '0');
                $('#twitterVerified').text(twitter.verified ? 'Yes' : 'No')
                    .css('color', twitter.verified ? '#10b981' : '#ef4444');
                $('#twitterCreated').text(twitter.created || 'Unknown');
                $('#twitterEngagement').text(twitter.engagementRate || '0%');
            }

            // Telegram info
            if (ws.telegramInfo) {
                const telegram = ws.telegramInfo;
                $('#telegramHandle').text(telegram.handle || 'Not found');
                $('#telegramMembers').text(telegram.members || '0');
                $('#telegramOnline').text(telegram.onlineMembers || '0');
                $('#telegramCreated').text(telegram.created || 'Unknown');
            }

            $('#websiteSocialCard').show();
        }

        /* DEACTIVATED: Community Interaction Card - Keep code for future updates
        if (data.community) {
            const ci = data.community;
            const $contentEl = $('#communityCardContent').empty();

            $contentEl.append(`
                <div class="community-stats-grid">
                    <div class="community-stat">
                        <div class="stat-value">${ci.size || '0'}</div>
                        <div class="stat-label">${ci.sizeLabel || 'Members'}</div>
                    </div>
                    <div class="community-stat">
                        <div class="stat-value">${ci.engagement || 'Low'}</div>
                        <div class="stat-label">${ci.engagementLabel || 'Engagement'}</div>
                    </div>
                    <div class="community-stat">
                        <div class="stat-value">${ci.growth || '0%'}</div>
                        <div class="stat-label">${ci.growthLabel || 'Growth'}</div>
                    </div>
                    <div class="community-stat">
                        <div class="stat-value">${ci.sentiment || 'Neutral'}</div>
                        <div class="stat-label">${ci.sentimentLabel || 'Sentiment'}</div>
                    </div>
                </div>

                <div class="community-interactions">
                    <h4>Community Interactions</h4>
                    <div class="interaction-stats">
                        <span><i class="fas fa-heart"></i> ${ci.likes || '0'} Likes</span>
                        <span><i class="fas fa-comment"></i> ${ci.comments || '0'} Comments</span>
                        <span><i class="fas fa-share"></i> ${ci.shares || '0'} Shares</span>
                    </div>
                </div>

                <div class="sentiment-breakdown">
                    <h4>Sentiment Analysis</h4>
                    <canvas id="sentimentChart" width="200" height="200"></canvas>
                </div>

                <div class="recent-mentions">
                    <h4>Recent Mentions</h4>
                    <ul id="recentMentionsList">
                        ${ci.recentMentions ? ci.recentMentions.map(mention =>
                            `<li>"${mention}"</li>`
                        ).join('') : '<li>No recent mentions</li>'}
                    </ul>
                </div>
            `);

            // Update sentiment chart if data available
            if (ci.sentimentBreakdown) {
                updateSentimentChart(ci.sentimentBreakdown);
            }

            $('#communityInteractionCard').show();
        }
        */

        // Final Results Card
        if (data.scores) {
            const scores = data.scores;
            $('#finalTrustScore').text(scores.trust_score + '/100');
            $('#finalReliabilityScore').text(scores.activity_score + '/100');
            $('#finalOverallRating').text(scores.overall_score + '/100');
            $('#finalSummary').text(scores.recommendation);

            // Update progress bars
            updateProgressBar('trustScoreBar', scores.trust_score);
            updateProgressBar('reliabilityScoreBar', scores.activity_score);
            updateProgressBar('overallRatingBar', scores.overall_score);

            $('#finalResultsCard').show();
        }

        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#resultsSection').offset().top - 100
        }, 500);
    }

    /**
     * Update validation UI
     */
    function updateValidationUI(validation) {
        const $card = $('#validationCard');

        // Update status icon and message
        const $statusIcon = $card.find('.validation-status-icon');
        const $statusMessage = $card.find('.validation-status-message');

        if (validation.isValid) {
            $statusIcon.html('<i class="fas fa-check-circle"></i>').removeClass('invalid').addClass('valid');
            $statusMessage.text(validation.message || 'Valid Solana address');
        } else {
            $statusIcon.html('<i class="fas fa-times-circle"></i>').removeClass('valid').addClass('invalid');
            $statusMessage.text(validation.message || 'Invalid Solana address');
        }

        // Update validation details
        $('#valFormat').text(validation.format || 'Unknown');
        $('#valLength').text(validation.length || 'Unknown');
        $('#valType').text(validation.type || 'Unknown');
        $('#valExists').text(validation.exists === true ? 'Yes' : validation.exists === false ? 'No' : 'Unknown');

        $card.show();
    }

    /**
     * Update progress bar
     */
    function updateProgressBar(barId, value) {
        const $bar = $('#' + barId);
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

    /**
     * Update token distribution chart
     */
    function updateTokenDistributionChart(data) {
        if (!window.tokenDistributionChart) return;

        window.tokenDistributionChart.data.labels = data.map(d => d.label);
        window.tokenDistributionChart.data.datasets[0].data = data.map(d => d.percentage);
        window.tokenDistributionChart.data.datasets[0].backgroundColor = data.map(d => d.color);
        window.tokenDistributionChart.update();
    }

    /**
     * Update sentiment chart (for future use)
     */
    function updateSentimentChart(data) {
        const canvas = document.getElementById('sentimentChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        if (window.sentimentChart) {
            window.sentimentChart.data.labels = data.map(d => d.label);
            window.sentimentChart.data.datasets[0].data = data.map(d => d.percentage);
            window.sentimentChart.data.datasets[0].backgroundColor = data.map(d => d.color);
            window.sentimentChart.update();
        } else {
            window.sentimentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.label),
                    datasets: [{
                        data: data.map(d => d.percentage),
                        backgroundColor: data.map(d => d.color),
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

    /**
     * Show loading state
     */
    function showLoading() {
        $('#loadingOverlay').fadeIn();
        $('#resultsSection').hide();
        $('.error-message').hide();
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
        $('#loadingOverlay').fadeOut();
    }

    /**
     * Show error message
     */
    function showError(message) {
        const $errorDiv = $('.error-message');
        $errorDiv.text(message).fadeIn();

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $errorDiv.fadeOut();
        }, 5000);
    }

})(jQuery);
