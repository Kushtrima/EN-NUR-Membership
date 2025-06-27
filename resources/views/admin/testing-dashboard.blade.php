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
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .test-category {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            font-weight: bold;
            font-size: 1.1rem;
            color: #495057;
        }
        
        .test-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .test-item:last-child {
            border-bottom: none;
        }
        
        .test-item.passed {
            background: rgba(40, 167, 69, 0.05);
        }
        
        .test-item.failed {
            background: rgba(220, 53, 69, 0.05);
        }
        
        .test-name {
            flex: 1;
            font-weight: 500;
        }
        
        .test-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .test-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .test-icon.passed {
            background: #28a745;
        }
        
        .test-icon.failed {
            background: #dc3545;
        }
        
        .test-details {
            font-size: 0.9rem;
            color: #666;
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
            
            // Build results HTML
            let resultsHTML = '';
            
            results.forEach(result => {
                if (result.type === 'category') {
                    resultsHTML += `<div class="test-category">${result.name}</div>`;
                } else if (result.type === 'test') {
                    const statusClass = result.passed ? 'passed' : 'failed';
                    const iconClass = result.passed ? 'passed' : 'failed';
                    const iconText = result.passed ? '✓' : '✗';
                    
                    resultsHTML += `
                        <div class="test-item ${statusClass}">
                            <div class="test-name">
                                ${result.name}
                                ${result.details ? `<div class="test-details">${result.details}</div>` : ''}
                            </div>
                            <div class="test-status">
                                <div class="test-icon ${iconClass}">${iconText}</div>
                            </div>
                        </div>
                    `;
                }
            });
            
            testResults.innerHTML = resultsHTML;
            
            // Show results
            testSummary.style.display = 'grid';
            testResults.style.display = 'block';
            
            // Show success message
            if (summary.failed_tests === 0) {
                showSuccess(`All ${summary.total_tests} tests passed! Your system is working perfectly.`);
            } else {
                showWarning(`${summary.failed_tests} out of ${summary.total_tests} tests failed. Please review the results below.`);
            }
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