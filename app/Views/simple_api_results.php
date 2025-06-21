<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataForSEO API Test Results</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: #2d3748;
            line-height: 1.6;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .location-selector {
            margin: 1rem 0;
        }
        .location-selector select {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: none;
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 1rem;
        }
        .location-selector select option {
            color: #333;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .alert-error { background: #fed7d7; color: #c53030; border: 1px solid #feb2b2; }
        .alert-success { background: #c6f6d5; color: #22543d; border: 1px solid #9ae6b4; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #4299e1;
        }
        .stat-card h3 { font-size: 0.875rem; color: #718096; margin-bottom: 0.5rem; }
        .stat-card .value { font-size: 1.5rem; font-weight: bold; color: #2d3748; }
        .restaurant-grid {
            display: grid;
            gap: 1rem;
        }
        .restaurant-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        .restaurant-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .restaurant-name {
            font-size: 1.25rem;
            font-weight: bold;
            color: #2d3748;
        }
        .rating-badge {
            background: #ed8936;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            white-space: nowrap;
        }
        .restaurant-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 0.75rem;
            margin: 1rem 0;
        }
        .detail {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        .detail-icon {
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        .detail-text {
            color: #4a5568;
        }
        .features {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        .feature-tag {
            display: inline-block;
            background: #edf2f7;
            color: #4a5568;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            margin: 0.125rem;
        }
        .feature-tag.active {
            background: #48bb78;
            color: white;
        }
        .download-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #4299e1;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #3182ce;
        }
        .raw-data {
            background: #1a202c;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.75rem;
            margin: 1rem 0;
            max-height: 400px;
            overflow-y: auto;
        }
        .section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section h2 {
            margin-bottom: 1rem;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🇬🇪 DataForSEO API Test</h1>
            <p>Testing Georgian Restaurant Search</p>
            
            <div class="location-selector">
                <label for="location">Test Location:</label>
                <select id="location" onchange="changeLocation(this.value)">
                    <?php if (isset($locations)): ?>
                        <?php foreach ($locations as $key => $location): ?>
                            <option value="<?= $key ?>" <?= (isset($current_location) && $current_location === $key) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($location['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
            
            <div class="section">
                <h2>Setup Instructions</h2>
                <p>To use this API test, please:</p>
                <ol style="margin: 1rem 0; padding-left: 2rem;">
                    <li>Sign up for DataForSEO API account</li>
                    <li>Add your credentials to .env file:</li>
                </ol>
                <div class="raw-data">
DATAFORSEO_LOGIN=your_login_here
DATAFORSEO_PASSWORD=your_password_here
                </div>
            </div>
            
        <?php elseif (isset($result)): ?>
            
            <?php if ($result['success']): ?>
                <div class="alert alert-success">
                    <strong>✅ Success:</strong> API request completed successfully for <?= htmlspecialchars($result['location']['name']) ?>
                </div>

                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>API Response</h3>
                        <div class="value"><?= $result['general']['status'] ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Response Time</h3>
                        <div class="value"><?= $result['general']['time'] ?>s</div>
                    </div>
                    <div class="stat-card">
                        <h3>API Cost</h3>
                        <div class="value">$<?= $result['general']['cost'] ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Results Found</h3>
                        <div class="value"><?= $result['total_found'] ?? 0 ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Results Returned</h3>
                        <div class="value"><?= $result['returned_count'] ?? 0 ?></div>
                    </div>
                </div>

                <!-- Restaurants -->
                <?php if (!empty($result['restaurants'])): ?>
                    <div class="section">
                        <h2>🏪 Found Restaurants (<?= count($result['restaurants']) ?>)</h2>
                        
                        <div class="restaurant-grid">
                            <?php foreach ($result['restaurants'] as $restaurant): ?>
                                <div class="restaurant-card">
                                    <div class="restaurant-header">
                                        <div class="restaurant-name"><?= htmlspecialchars($restaurant['name']) ?></div>
                                        <?php if ($restaurant['rating'] > 0): ?>
                                            <div class="rating-badge">
                                                ⭐ <?= $restaurant['rating'] ?>/5 (<?= $restaurant['rating_count'] ?>)
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="restaurant-details">
                                        <div class="detail">
                                            <span class="detail-icon">📍</span>
                                            <span class="detail-text"><?= htmlspecialchars($restaurant['address']) ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-icon">📞</span>
                                            <span class="detail-text"><?= htmlspecialchars($restaurant['phone']) ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-icon">🌐</span>
                                            <span class="detail-text"><?= htmlspecialchars($restaurant['website']) ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-icon">🏷️</span>
                                            <span class="detail-text"><?= htmlspecialchars($restaurant['category']) ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-icon">💰</span>
                                            <span class="detail-text">Price: <?= htmlspecialchars($restaurant['price_level']) ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-icon">📸</span>
                                            <span class="detail-text"><?= $restaurant['photos_count'] ?> photos</span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-icon">🕐</span>
                                            <span class="detail-text"><?= ucfirst(str_replace('_', ' ', $restaurant['current_status'])) ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-icon">🆔</span>
                                            <span class="detail-text"><?= htmlspecialchars($restaurant['place_id']) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="features">
                                        <span class="feature-tag <?= ($restaurant['is_claimed'] ?? false) ? 'active' : '' ?>">
                                            ✅ Verified
                                        </span>
                                        <span class="feature-tag <?= ($restaurant['has_delivery'] ?? false) ? 'active' : '' ?>">
                                            🚚 Delivery
                                        </span>
                                        <span class="feature-tag <?= ($restaurant['has_takeout'] ?? false) ? 'active' : '' ?>">
                                            🥡 Takeout
                                        </span>
                                        <span class="feature-tag <?= ($restaurant['accepts_reservations'] ?? false) ? 'active' : '' ?>">
                                            📅 Reservations
                                        </span>
                                        <span class="feature-tag <?= ($restaurant['wheelchair_accessible'] ?? false) ? 'active' : '' ?>">
                                            ♿ Accessible
                                        </span>
                                        <span class="feature-tag <?= ($restaurant['serves_alcohol'] ?? false) ? 'active' : '' ?>">
                                            🍷 Alcohol
                                        </span>
                                        <span class="feature-tag <?= ($restaurant['family_friendly'] ?? false) ? 'active' : '' ?>">
                                            👨‍👩‍👧‍👦 Family Friendly
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Download Section -->
                <?php if (isset($result['filename'])): ?>
                    <div class="download-section">
                        <h3>📄 Raw API Response</h3>
                        <p>Download the complete JSON response for detailed analysis</p>
                        <a href="<?= base_url('simple-api-test/download/' . $result['filename']) ?>" class="btn">
                            📥 Download JSON File
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Raw Data Preview -->
                <div class="section">
                    <h2>🔍 API Response Preview</h2>
                    <div class="raw-data">
                        <?= htmlspecialchars(json_encode($result['raw_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-error">
                    <strong>❌ API Error:</strong> <?= htmlspecialchars($result['error'] ?? 'Unknown error') ?>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>

    <script>
        function changeLocation(location) {
            window.location.href = '?location=' + location;
        }
    </script>
</body>
</html>