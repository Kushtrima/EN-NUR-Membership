<x-app-layout>
    <style>
        .test-dashboard {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .test-container {
            background: #1a202c;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .test-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 2rem;
            border-bottom: 1px solid #4a5568;
        }
        
        .test-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .test-subtitle {
            color: #a0aec0;
            font-size: 1rem;
            margin: 0;
        }
        
        .test-controls {
            background: #2d3748;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #4a5568;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .run-tests-btn {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .run-tests-btn:hover {
            background: linear-gradient(135deg, #2f855a 0%, #276749 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4);
        }
        
        .run-tests-btn:disabled {
            background: #4a5568;
            cursor: not-allowed;
            transform: none;
        }
        
        .test-info {
            color: #a0aec0;
            font-size: 0.9rem;
        }
        
        .test-summary {
            background: #2d3748;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #4a5568;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1.5rem;
        }
        
        .summary-card {
            text-align: center;
        }
        
        .summary-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .summary-label {
            color: #a0aec0;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-card.success .summary-number {
            color: #68d391;
        }
        
        .summary-card.warning .summary-number {
            color: #f6ad55;
        }
        
        .summary-card.danger .summary-number {
            color: #fc8181;
        }
        
        .test-results {
            background: #2d3748;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .test-results::-webkit-scrollbar {
            width: 8px;
        }
        
        .test-results::-webkit-scrollbar-track {
            background: #1a202c;
        }
        
        .test-results::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 4px;
        }
        
        .test-results::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }
        
        .test-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .test-table th {
            background: #1a202c;
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #e2e8f0;
            border-bottom: 1px solid #4a5568;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .test-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(74, 85, 104, 0.3);
            vertical-align: top;
        }
        
        .test-table tr:hover {
            background: rgba(74, 85, 104, 0.2);
        }
        
        .test-table tr.failed {
            background: rgba(245, 101, 101, 0.1);
        }
        
        .test-table tr.failed:hover {
            background: rgba(245, 101, 101, 0.15);
        }
        
        .category-row {
            background: #1a202c !important;
            font-weight: 600;
            color: #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
        }
        
        .category-row:hover {
            background: #1a202c !important;
        }
        
        .category-row td {
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid #4a5568;
        }
        
        .test-name {
            font-weight: 500;
            color: #e2e8f0;
            font-size: 0.9rem;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
        }
        
        .test-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            font-family: monospace;
        }
        
        .status-pass {
            color: #68d391;
        }
        
        .status-fail {
            color: #fc8181;
        }
        
        .test-error {
            margin-top: 0.75rem;
            padding: 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.4;
        }
        
        .error-critical {
            background: rgba(245, 101, 101, 0.1);
            border: 1px solid rgba(245, 101, 101, 0.3);
            color: #fc8181;
        }
        
        .error-moderate {
            background: rgba(246, 173, 85, 0.1);
            border: 1px solid rgba(246, 173, 85, 0.3);
            color: #f6ad55;
        }
        
        .error-minor {
            background: rgba(99, 179, 237, 0.1);
            border: 1px solid rgba(99, 179, 237, 0.3);
            color: #63b3ed;
        }
        
        .error-severity {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        
        .severity-critical {
            background: #fc8181;
            color: #1a202c;
        }
        
        .severity-moderate {
            background: #f6ad55;
            color: #1a202c;
        }
        
        .severity-minor {
            background: #63b3ed;
            color: #1a202c;
        }
        
        .test-duration {
            color: #a0aec0;
            font-size: 0.8rem;
            font-family: monospace;
        }
        
        .log-entry {
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid rgba(74, 85, 104, 0.3);
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        
        .log-entry:hover {
            background: rgba(74, 85, 104, 0.2);
        }
        
        .log-timestamp {
            color: #a0aec0;
            margin-right: 1rem;
        }
        
        .log-level {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 1rem;
            min-width: 60px;
            text-align: center;
        }
        
        .log-level.info {
            background: #63b3ed;
            color: #1a202c;
        }
        
        .log-level.warning {
            background: #f6ad55;
            color: #1a202c;
        }
        
        .log-level.error {
            background: #fc8181;
            color: #1a202c;
        }
        
        .log-level.debug {
            background: #68d391;
            color: #1a202c;
        }
        
        .log-message {
            color: #e2e8f0;
        }
        
        .log-context {
            color: #a0aec0;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            padding-left: 1rem;
        }
        
        .logs-header {
            background: #1a202c;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #4a5568;
            color: #e2e8f0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .logs-stats {
            color: #a0aec0;
            font-size: 0.8rem;
            font-weight: normal;
            text-transform: none;
            letter-spacing: normal;
            margin-left: 1rem;
        }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #28a745;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            border: 1px solid #f5c6cb;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            border: 1px solid #c3e6cb;
        }
    </style>

    <div class="test-dashboard">
        <div class="test-container">
            <!-- Header -->
            <div class="test-header">
                <h1 class="test-title">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    System Testing Suite
                </h1>
                <p class="test-subtitle">Comprehensive Business Logic & System Integration Testing</p>
            </div>

            <!-- Controls -->
            <div class="test-controls">
                <div class="test-info">
                    Business Logic • Data Integrity • Performance • Integration
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button id="runTestsBtn" class="run-tests-btn" onclick="runAllTests()">
                        Execute Tests
                    </button>
                    <button id="viewLogsBtn" class="run-tests-btn" onclick="showSystemLogs()" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);">
                        View System Logs
                    </button>
                </div>
            </div>

        <!-- Test Summary -->
        <div id="testSummary" class="test-summary" style="display: none;">
            <div class="summary-card success">
                <div class="summary-number" id="totalTests">0</div>
                <div class="summary-label">Total Tests</div>
            </div>
            <div class="summary-card success">
                <div class="summary-number" id="passedTests">0</div>
                <div class="summary-label">Passed Tests</div>
            </div>
            <div class="summary-card danger">
                <div class="summary-number" id="failedTests">0</div>
                <div class="summary-label">Failed Tests</div>
            </div>
            <div class="summary-card">
                <div class="summary-number" id="successRate">0%</div>
                <div class="summary-label">Success Rate</div>
            </div>
            <div class="summary-card success">
                <div class="summary-number" id="totalCategories">0</div>
                <div class="summary-label">Test Categories</div>
            </div>
        </div>

            <!-- Test Results -->
            <div id="testResults" class="test-results" style="display: none;">
                <!-- Results will be populated here -->
            </div>

            <!-- System Logs -->
            <div id="systemLogs" class="test-results" style="display: none;">
                <!-- Logs will be populated here -->
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="loading" style="display: none;">
                <div class="loading-spinner"></div>
                <p>Running comprehensive tests...</p>
                <p style="font-size: 0.9rem; color: #999;">This may take a few moments</p>
            </div>
        </div>
    </div>

    <script>
        async function runAllTests() {
            const runBtn = document.getElementById('runTestsBtn');
            const loadingState = document.getElementById('loadingState');
            const testSummary = document.getElementById('testSummary');
            const testResults = document.getElementById('testResults');
            
            // Show loading state
            runBtn.disabled = true;
            runBtn.textContent = 'Executing...';
            loadingState.style.display = 'block';
            testSummary.style.display = 'none';
            testResults.style.display = 'none';
            
            try {
                const response = await fetch('/testing-dashboard/run-tests', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayTestResults(data.results, data.summary);
                } else {
                    showError('Test execution failed: ' + (data.error || 'Unknown error'));
                }
                
            } catch (error) {
                showError('Failed to run tests: ' + error.message);
            } finally {
                // Hide loading state
                loadingState.style.display = 'none';
                runBtn.disabled = false;
                runBtn.textContent = 'Execute Tests';
            }
        }
        
        function displayTestResults(results, summary) {
            const testSummary = document.getElementById('testSummary');
            const testResults = document.getElementById('testResults');
            
            // Update summary
            document.getElementById('totalTests').textContent = summary.total_tests;
            document.getElementById('passedTests').textContent = summary.passed_tests;
            document.getElementById('failedTests').textContent = summary.failed_tests;
            document.getElementById('successRate').textContent = summary.success_rate + '%';
            document.getElementById('totalCategories').textContent = summary.total_categories;
            
            // Update summary card colors based on results
            const successRateCard = document.getElementById('successRate').closest('.summary-card');
            if (summary.success_rate >= 90) {
                successRateCard.className = 'summary-card success';
            } else if (summary.success_rate >= 70) {
                successRateCard.className = 'summary-card warning';
            } else {
                successRateCard.className = 'summary-card danger';
            }
            
            // Build unified testing table with category separators
            let resultsHTML = `
                <table class="test-table">
                    <thead>
                        <tr>
                            <th>Test Case</th>
                            <th>Status</th>
                            <th>Execution Time</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            let currentCategory = '';
            let categoryStats = {};
            
            // First pass: collect category statistics
            results.forEach(result => {
                if (result.type === 'category') {
                    currentCategory = result.name;
                    categoryStats[currentCategory] = { passed: 0, failed: 0, total: 0 };
                } else if (result.type === 'test' && currentCategory) {
                    categoryStats[currentCategory].total++;
                    if (result.passed) {
                        categoryStats[currentCategory].passed++;
                    } else {
                        categoryStats[currentCategory].failed++;
                    }
                }
            });
            
            // Second pass: build table
            currentCategory = '';
            results.forEach(result => {
                if (result.type === 'category') {
                    currentCategory = result.name;
                    const stats = categoryStats[currentCategory];
                    const successRate = stats.total > 0 ? Math.round((stats.passed / stats.total) * 100) : 0;
                    
                    resultsHTML += `
                        <tr class="category-row">
                            <td colspan="3">
                                ${currentCategory} 
                                <span style="font-weight: normal; opacity: 0.7; margin-left: 1rem;">
                                    ${stats.passed}/${stats.total} passed (${successRate}%)
                                </span>
                            </td>
                        </tr>
                    `;
                } else if (result.type === 'test') {
                    const statusClass = result.passed ? '' : 'failed';
                    const statusText = result.passed ? 'PASS' : 'FAIL';
                    const statusIcon = result.passed ? '✓' : '✗';
                    const statusColorClass = result.passed ? 'status-pass' : 'status-fail';
                    const duration = result.duration || '< 1ms';
                    
                    resultsHTML += `
                        <tr class="${statusClass}">
                            <td>
                                <div class="test-name">${result.name}</div>
                                ${result.error ? generateErrorDisplay(result.error, result.error_details) : ''}
                            </td>
                            <td>
                                <div class="test-status ${statusColorClass}">
                                    ${statusIcon} ${statusText}
                                </div>
                            </td>
                            <td>
                                <div class="test-duration">${duration}</div>
                            </td>
                        </tr>
                    `;
                }
            });
            
            resultsHTML += `
                    </tbody>
                </table>
            `;
            
            testResults.innerHTML = resultsHTML;
            
            // Show results
            testSummary.style.display = 'grid';
            testResults.style.display = 'block';
            
            // Show success message
            if (summary.failed_tests === 0) {
                showSuccess(`All ${summary.total_tests} tests passed! Your system is working perfectly.`);
            } else {
                showWarning(`${summary.failed_tests} out of ${summary.total_tests} tests failed. Please review the detailed error information below.`);
            }
        }
        
        async function showSystemLogs() {
            const viewLogsBtn = document.getElementById('viewLogsBtn');
            const testResults = document.getElementById('testResults');
            const systemLogs = document.getElementById('systemLogs');
            const testSummary = document.getElementById('testSummary');
            const loadingState = document.getElementById('loadingState');
            
            // Show loading state
            viewLogsBtn.disabled = true;
            viewLogsBtn.textContent = 'Loading...';
            loadingState.style.display = 'block';
            testResults.style.display = 'none';
            systemLogs.style.display = 'none';
            testSummary.style.display = 'none';
            
            try {
                const response = await fetch('/testing-dashboard/system-logs', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displaySystemLogs(data.logs, data.stats);
                } else {
                    showError('Failed to load system logs: ' + (data.error || 'Unknown error'));
                }
                
            } catch (error) {
                showError('Failed to load system logs: ' + error.message);
            } finally {
                // Hide loading state
                loadingState.style.display = 'none';
                viewLogsBtn.disabled = false;
                viewLogsBtn.textContent = 'View System Logs';
            }
        }
        
        function displaySystemLogs(logs, stats) {
            const systemLogs = document.getElementById('systemLogs');
            
            let logsHTML = `
                <div class="logs-header">
                    System Logs (Last 30 Days)
                    <span class="logs-stats">
                        ${stats.total_logs} entries • ${stats.error_count} errors • ${stats.warning_count} warnings
                    </span>
                </div>
            `;
            
            if (logs.length === 0) {
                logsHTML += `
                    <div style="padding: 2rem; text-align: center; color: #a0aec0;">
                        No log entries found in the last 30 days
                    </div>
                `;
            } else {
                logs.forEach(log => {
                    const levelClass = log.level.toLowerCase();
                    const timestamp = new Date(log.timestamp).toLocaleString();
                    
                    logsHTML += `
                        <div class="log-entry">
                            <span class="log-timestamp">${timestamp}</span>
                            <span class="log-level ${levelClass}">${log.level}</span>
                            <span class="log-message">${log.message}</span>
                            ${log.context ? `<div class="log-context">${log.context}</div>` : ''}
                        </div>
                    `;
                });
            }
            
            systemLogs.innerHTML = logsHTML;
            systemLogs.style.display = 'block';
            
            // Show success message
            showSuccess(`Loaded ${logs.length} log entries from the last 30 days. Auto-cleanup enabled.`);
        }
        
        function generateErrorDisplay(error, errorDetails) {
            // Determine error severity based on keywords
            const severity = determineErrorSeverity(error, errorDetails);
            const severityClass = `error-${severity.level}`;
            const severityBadgeClass = `severity-${severity.level}`;
            
            return `
                <div class="test-error ${severityClass}">
                    <div class="error-severity ${severityBadgeClass}">${severity.label}</div>
                    <strong>Error:</strong> ${error}
                    ${errorDetails ? `\n\n<strong>Details:</strong>\n${errorDetails}` : ''}
                </div>
            `;
        }
        
        function determineErrorSeverity(error, errorDetails) {
            const errorText = (error + ' ' + (errorDetails || '')).toLowerCase();
            
            // Critical errors - system breaking issues
            if (errorText.includes('database') || errorText.includes('connection') || 
                errorText.includes('exception') || errorText.includes('critical') ||
                errorText.includes('fatal') || errorText.includes('cannot connect') ||
                errorText.includes('table doesn\'t exist') || errorText.includes('column not found')) {
                return { level: 'critical', label: 'Critical' };
            }
            
            // Moderate errors - functionality issues
            if (errorText.includes('relationship') || errorText.includes('model') ||
                errorText.includes('method') || errorText.includes('class') ||
                errorText.includes('undefined') || errorText.includes('null') ||
                errorText.includes('permission') || errorText.includes('access denied')) {
                return { level: 'moderate', label: 'Moderate' };
            }
            
            // Minor errors - cosmetic or non-critical issues
            return { level: 'minor', label: 'Minor' };
        }
        
        function showError(message) {
            const testResults = document.getElementById('testResults');
            testResults.innerHTML = `<div class="error-message"><strong>Error:</strong> ${message}</div>`;
            testResults.style.display = 'block';
        }
        
        function showSuccess(message) {
            const testResults = document.getElementById('testResults');
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `<strong>Success:</strong> ${message}`;
            testResults.insertBefore(successDiv, testResults.firstChild);
        }
        
        function showWarning(message) {
            const testResults = document.getElementById('testResults');
            const warningDiv = document.createElement('div');
            warningDiv.className = 'error-message';
            warningDiv.innerHTML = `<strong>Warning:</strong> ${message}`;
            testResults.insertBefore(warningDiv, testResults.firstChild);
        }
    </script>
</x-app-layout> 