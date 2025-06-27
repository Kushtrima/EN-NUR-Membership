<x-app-layout>
    <style>
        .test-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        .test-header {
            background: linear-gradient(135deg, #1F6E38 0%, #28a745 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .test-controls {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .run-tests-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .run-tests-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        
        .run-tests-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .test-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .summary-card.success {
            border-left: 4px solid #28a745;
        }
        
        .summary-card.warning {
            border-left: 4px solid #ffc107;
        }
        
        .summary-card.danger {
            border-left: 4px solid #dc3545;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .summary-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .test-results {
            margin-bottom: 2rem;
        }
        
        .test-category-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .category-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .category-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        
        .category-stats {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .test-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .test-table th {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.85rem;
        }
        
        .test-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: top;
        }
        
        .test-table tr:hover {
            background: #f8f9fa;
        }
        
        .test-table tr.failed {
            background: rgba(220, 53, 69, 0.02);
        }
        
        .test-table tr.failed:hover {
            background: rgba(220, 53, 69, 0.05);
        }
        
        .test-table tr:last-child td {
            border-bottom: none;
        }
        
        .test-name {
            font-weight: 500;
            color: #2d3748;
            font-size: 0.9rem;
        }
        
        .test-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-pass {
            color: #28a745;
        }
        
        .status-fail {
            color: #dc3545;
        }
        
        .test-error {
            margin-top: 0.5rem;
            padding: 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-family: 'Monaco', 'Menlo', monospace;
            white-space: pre-wrap;
            word-break: break-word;
        }
        
        .error-critical {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .error-moderate {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            color: #92400e;
        }
        
        .error-minor {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #1e40af;
        }
        
        .error-severity {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .severity-critical {
            background: #dc3545;
            color: white;
        }
        
        .severity-moderate {
            background: #f59e0b;
            color: white;
        }
        
        .severity-minor {
            background: #3b82f6;
            color: white;
        }
        
        .test-duration {
            color: #718096;
            font-size: 0.8rem;
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
        <!-- Header -->
        <div class="test-header">
            <h1 style="margin: 0 0 0.5rem 0; font-size: 2.5rem;">Testing Dashboard</h1>
            <p style="margin: 0; opacity: 0.9; font-size: 1.1rem;">Comprehensive Business Logic & System Testing</p>
        </div>

        <!-- Controls -->
        <div class="test-controls">
            <button id="runTestsBtn" class="run-tests-btn" onclick="runAllTests()">
                Run All Tests
            </button>
            <p style="margin: 1rem 0 0 0; color: #666;">
                This will test all business logic, data integrity, system integration, and performance
            </p>
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
            <div class="summary-card">
                <div class="summary-number" id="totalCategories">0</div>
                <div class="summary-label">Test Categories</div>
            </div>
        </div>

        <!-- Test Results -->
        <div id="testResults" class="test-results" style="display: none;">
            <!-- Results will be populated here -->
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="loading" style="display: none;">
            <div class="loading-spinner"></div>
            <p>Running comprehensive tests...</p>
            <p style="font-size: 0.9rem; color: #999;">This may take a few moments</p>
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
            runBtn.textContent = 'Running Tests...';
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
                runBtn.textContent = 'Run All Tests';
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
            
            // Group results by category
            const categories = {};
            let currentCategory = '';
            
            results.forEach(result => {
                if (result.type === 'category') {
                    currentCategory = result.name;
                    categories[currentCategory] = {
                        name: currentCategory,
                        tests: [],
                        passed: 0,
                        failed: 0
                    };
                } else if (result.type === 'test' && currentCategory) {
                    categories[currentCategory].tests.push(result);
                    if (result.passed) {
                        categories[currentCategory].passed++;
                    } else {
                        categories[currentCategory].failed++;
                    }
                }
            });
            
            // Build separated category sections
            let resultsHTML = '';
            
            Object.values(categories).forEach(category => {
                const totalTests = category.tests.length;
                const successRate = totalTests > 0 ? Math.round((category.passed / totalTests) * 100) : 0;
                
                resultsHTML += `
                    <div class="test-category-section">
                        <div class="category-header">
                            <h3 class="category-title">${category.name}</h3>
                            <div class="category-stats">
                                ${category.passed} passed, ${category.failed} failed • ${successRate}% success rate
                            </div>
                        </div>
                        <table class="test-table">
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                category.tests.forEach(test => {
                    const statusClass = test.passed ? '' : 'failed';
                    const statusText = test.passed ? 'Pass' : 'Fail';
                    const statusIcon = test.passed ? '✓' : '✗';
                    const statusColorClass = test.passed ? 'status-pass' : 'status-fail';
                    const duration = test.duration || '< 1ms';
                    
                    resultsHTML += `
                        <tr class="${statusClass}">
                            <td>
                                <div class="test-name">${test.name}</div>
                                ${test.error ? generateErrorDisplay(test.error, test.error_details) : ''}
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
                });
                
                resultsHTML += `
                            </tbody>
                        </table>
                    </div>
                `;
            });
            
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