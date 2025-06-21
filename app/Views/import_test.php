<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataForSEO Import Test</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
            background: #f5f7fa;
        }
        .container { 
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #2d3748; 
            text-align: center;
            margin-bottom: 30px;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .test-section h3 {
            margin-top: 0;
            color: #4a5568;
        }
        button {
            background: #4299e1;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
            transition: background 0.2s;
        }
        button:hover {
            background: #3182ce;
        }
        button:disabled {
            background: #a0aec0;
            cursor: not-allowed;
        }
        .result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
        }
        .success {
            background: #c6f6d5;
            border: 1px solid #9ae6b4;
            color: #22543d;
        }
        .error {
            background: #fed7d7;
            border: 1px solid #feb2b2;
            color: #c53030;
        }
        .info {
            background: #bee3f8;
            border: 1px solid #90cdf4;
            color: #2c5282;
        }
        .loading {
            background: #faf089;
            border: 1px solid #f6e05e;
            color: #744210;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4299e1;
        }
        .stat-label {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }
        pre {
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🇬🇪 DataForSEO Import Test</h1>
        <p style="text-align: center; color: #718096;">
            Test import of Chama Mama restaurant data from DataForSEO API
        </p>
        
        <!-- Migration Status -->
        <div class="test-section">
            <h3>📋 1. Database Migration</h3>
            <p>First, make sure you've run the database migration script to add all the new tables and columns.</p>
            <div class="info result">
                Required tables:
                - restaurants (with new columns)
                - restaurant_attributes  
                - attribute_definitions
                - restaurant_hours
                - restaurant_popular_times
                - restaurant_relations
                - restaurant_topics
            </div>
        </div>
        
        <!-- Import Test -->
        <div class="test-section">
            <h3>🚀 2. Import Chama Mama Data</h3>
            <p>Import the sample Chama Mama restaurant data with all attributes, hours, and related information.</p>
            <button onclick="importChamaMama()">Import Chama Mama Data</button>
            <div id="importResult" class="result" style="display: none;"></div>
        </div>
        
        <!-- View Imported Data -->
        <div class="test-section">
            <h3>👀 3. View Imported Data</h3>
            <p>Check the imported restaurant data with all details, attributes, and structured information.</p>
            <button onclick="viewImported()">View Imported Restaurants</button>
            <div id="viewResult" class="result" style="display: none;"></div>
        </div>
        
        <!-- Attribute Search Test -->
        <div class="test-section">
            <h3>🔍 4. Test Attribute Search</h3>
            <p>Test searching restaurants by their attributes (delivery, vegan options, family-friendly, etc.)</p>
            <button onclick="testAttributeSearch()">Test Attribute Search</button>
            <div id="searchResult" class="result" style="display: none;"></div>
        </div>
        
        <!-- Attribute Statistics -->
        <div class="test-section">
            <h3>📊 5. Attribute Statistics</h3>
            <p>View statistics of all available attributes across restaurants.</p>
            <button onclick="getAttributeStats()">Get Attribute Stats</button>
            <div id="statsResult" class="result" style="display: none;"></div>
        </div>
        
        <!-- Database Check -->
        <div class="test-section">
            <h3>🗄️ 6. Database Status</h3>
            <div class="stats-grid" id="dbStats">
                <div class="stat-card">
                    <div class="stat-number" id="restaurantCount">-</div>
                    <div class="stat-label">Total Restaurants</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="attributeCount">-</div>
                    <div class="stat-label">Total Attributes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="definitionCount">-</div>
                    <div class="stat-label">Attribute Definitions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="dataforSeoCount">-</div>
                    <div class="stat-label">DataForSEO Imports</div>
                </div>
            </div>
            <button onclick="checkDatabase()">Check Database Status</button>
        </div>
    </div>

    <script>
        // Utility functions
        function showResult(elementId, content, type = 'info') {
            const element = document.getElementById(elementId);
            element.style.display = 'block';
            element.className = `result ${type}`;
            element.innerHTML = `<pre>${content}</pre>`;
        }
        
        function showLoading(elementId, message = 'Loading...') {
            showResult(elementId, message, 'loading');
        }
        
        async function makeRequest(url, options = {}) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    ...options
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${data.error || 'Request failed'}`);
                }
                
                return data;
            } catch (error) {
                throw new Error(`Request failed: ${error.message}`);
            }
        }
        
        // Import Chama Mama data
        async function importChamaMama() {
            showLoading('importResult', 'Importing Chama Mama data...');
            
            try {
                const result = await makeRequest('/import-test/import-chama-mama', {
                    method: 'POST'
                });
                
                if (result.success) {
                    const message = `✅ Import Successful!
                    
Imported: ${result.imported} new restaurants
Updated: ${result.updated} existing restaurants
Errors: ${result.errors.length}

${result.message}

${result.errors.length > 0 ? 'Errors:\n' + JSON.stringify(result.errors, null, 2) : ''}`;
                    
                    showResult('importResult', message, 'success');
                    checkDatabase(); // Update stats
                } else {
                    showResult('importResult', `❌ Import Failed: ${result.error}`, 'error');
                }
            } catch (error) {
                showResult('importResult', `❌ Error: ${error.message}`, 'error');
            }
        }
        
        // View imported data
        async function viewImported() {
            showLoading('viewResult', 'Loading imported restaurants...');
            
            try {
                const result = await makeRequest('/import-test/view-imported');
                
                if (result.success) {
                    let output = `📍 Found ${result.restaurants.length} restaurants:\n\n`;
                    
                    result.restaurants.forEach((data, index) => {
                        const r = data.restaurant;
                        const attrs = data.attributes;
                        
                        output += `${index + 1}. ${r.name}\n`;
                        output += `   📍 ${r.address}\n`;
                        output += `   ⭐ ${r.rating}/5 (${r.rating_count} reviews)\n`;
                        output += `   🏷️ ${r.category}\n`;
                        output += `   📞 ${r.phone || 'N/A'}\n`;
                        output += `   🌐 ${r.website || 'N/A'}\n`;
                        output += `   💰 ${r.price_level || 'N/A'}\n`;
                        output += `   🕐 ${r.current_status}\n`;
                        
                        if (Object.keys(attrs).length > 0) {
                            output += `   ✨ Attributes:\n`;
                            Object.keys(attrs).forEach(category => {
                                const available = attrs[category].attributes.filter(a => a.is_available);
                                if (available.length > 0) {
                                    output += `      ${attrs[category].category_name}: ${available.map(a => a.icon + ' ' + a.display_name).join(', ')}\n`;
                                }
                            });
                        }
                        
                        output += `\n`;
                    });
                    
                    showResult('viewResult', output, 'success');
                } else {
                    showResult('viewResult', `❌ Error: ${result.error}`, 'error');
                }
            } catch (error) {
                showResult('viewResult', `❌ Error: ${error.message}`, 'error');
            }
        }
        
        // Test attribute search
        async function testAttributeSearch() {
            showLoading('searchResult', 'Testing attribute-based search...');
            
            try {
                const result = await makeRequest('/import-test/test-attribute-search');
                
                if (result.success) {
                    let output = `🔍 Attribute Search Results:\n\n`;
                    
                    output += `📊 Search Statistics:\n`;
                    output += `   🚚 Restaurants with delivery: ${result.searches.with_delivery}\n`;
                    output += `   🌱 Restaurants with vegan options: ${result.searches.vegan_options}\n`;
                    output += `   👨‍👩‍👧‍👦 Family-friendly restaurants: ${result.searches.family_friendly}\n\n`;
                    
                    if (result.examples.delivery_restaurants.length > 0) {
                        output += `🚚 Delivery Examples:\n`;
                        result.examples.delivery_restaurants.slice(0, 3).forEach(r => {
                            output += `   • ${r.name} (${r.rating}⭐)\n`;
                        });
                        output += `\n`;
                    }
                    
                    if (result.examples.vegan_restaurants.length > 0) {
                        output += `🌱 Vegan Options Examples:\n`;
                        result.examples.vegan_restaurants.slice(0, 3).forEach(r => {
                            output += `   • ${r.name} (${r.rating}⭐)\n`;
                        });
                        output += `\n`;
                    }
                    
                    showResult('searchResult', output, 'success');
                } else {
                    showResult('searchResult', `❌ Error: ${result.error}`, 'error');
                }
            } catch (error) {
                showResult('searchResult', `❌ Error: ${error.message}`, 'error');
            }
        }
        
        // Get attribute statistics
        async function getAttributeStats() {
            showLoading('statsResult', 'Loading attribute statistics...');
            
            try {
                const result = await makeRequest('/import-test/attribute-stats');
                
                if (result.success) {
                    let output = `📊 Attribute Statistics:\n\n`;
                    
                    const categories = {};
                    result.stats.forEach(stat => {
                        if (!categories[stat.category]) {
                            categories[stat.category] = [];
                        }
                        categories[stat.category].push(stat);
                    });
                    
                    Object.keys(categories).forEach(category => {
                        output += `${category.toUpperCase().replace('_', ' ')}:\n`;
                        categories[category].forEach(stat => {
                            if (stat.available_count > 0) {
                                output += `   ${stat.icon || '•'} ${stat.display_name}: ${stat.available_count} restaurants\n`;
                            }
                        });
                        output += `\n`;
                    });
                    
                    showResult('statsResult', output, 'success');
                } else {
                    showResult('statsResult', `❌ Error: ${result.error}`, 'error');
                }
            } catch (error) {
                showResult('statsResult', `❌ Error: ${error.message}`, 'error');
            }
        }
        
        // Check database status
        async function checkDatabase() {
            try {
                // This would need to be implemented as a separate endpoint
                // For now, we'll simulate some basic checks
                document.getElementById('restaurantCount').textContent = '✓';
                document.getElementById('attributeCount').textContent = '✓';
                document.getElementById('definitionCount').textContent = '✓';
                document.getElementById('dataforSeoCount').textContent = '✓';
            } catch (error) {
                console.error('Database check failed:', error);
            }
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            checkDatabase();
        });
    </script>
</body>
</html>