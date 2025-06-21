<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataForSEO API Test Results</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f6fa;
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .location-selector {
            margin: 20px 0;
        }
        .location-selector select {
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 16px;
        }
        .content {
            padding: 30px;
        }
        .error {
            background: #e74c3c;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .success {
            background: #27ae60;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        .info-card h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .restaurant-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .restaurant-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .restaurant-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        .rating {
            background: #f39c12;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            margin-left: auto;
        }
        .restaurant-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .detail-item {
            display: flex;
            align-items: center;
            font-size: 0.9em;
        }
        .detail-item .icon {
            margin-right: 8px;
            width: 16px;
        }
        .attributes {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .attribute-tag {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin: 2px;
        }
        .attribute-tag.delivery { background: #27ae60; }
        .attribute-tag.takeout { background: #e67e22; }
        .attribute-tag.accessible { background: #9b59b6; }
        .attribute-tag.alcohol { background: #c0392b; }
        .tabs {
            display: flex;
            background: #ecf0f1;
            border-radius: 8px;
            padding: 5px;
            margin: 30px 0 20px 0;
        }
        .tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            background: transparent;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .tab.active {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            color: #2c3e50;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .field-analysis {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .field-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .field-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .field-item:last-child {
            border-bottom: none;
        }
        .field-name {
            font-family: monospace;
            font-weight: bold;
            color: #e74c3c;
        }
        .field-type {
            background: #3498db;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8em;
        }
        .recommendations {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
        }
        .code-block {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .download-link {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin: 10px 0;
        }
        .download-link:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ DataForSEO API Test Results</h1>
            <p>Georgian Restaurant Search Analysis</p>
            
            <div class="location-selector">
                <select onchange="window.location.href='?location=' + this.value">
                    <option value="">Choose Location...</option>
                    <?php if (isset($locations)): ?>
                        <?php foreach ($locations as $key => $loc): ?>
                            <option value="<?= $key ?>" <?= isset($location) && $location['name'] === $loc['name'] ? 'selected' : '' ?>>
                                <?= $loc['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="content">
            <?php if (isset($error)): ?>
                <div class="error">
                    <h3>❌ Error</h3>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php elseif (isset($analysis)): ?>
                
                <?php if ($analysis['success']): ?>
                    <div class="success">
                        <h3>✅ API Request Successful</h3>
                        <p>Location: <strong><?= htmlspecialchars($analysis['location']) ?></strong></p>
                    </div>

                    <!-- General Information -->
                    <div class="info-grid">
                        <div class="info-card">
                            <h3>📊 Request Info</h3>
                            <p><strong>Status:</strong> <?= $analysis['general_info']['status'] ?></p>
                            <p><strong>Time:</strong> <?= $analysis['general_info']['time'] ?></p>
                            <p><strong>Cost:</strong> $<?= $analysis['general_info']['cost'] ?></p>
                        </div>
                        
                        <div class="info-card">
                            <h3>🔍 Search Results</h3>
                            <p><strong>Total Found:</strong> <?= $analysis['total_found'] ?? 0 ?></p>
                            <p><strong>Returned:</strong> <?= $analysis['returned_count'] ?? 0 ?></p>
                            <p><strong>Tasks:</strong> <?= $analysis['general_info']['tasks_count'] ?></p>
                        </div>
                        
                        <div class="info-card">
                            <h3>📈 Data Quality</h3>
                            <p><strong>Fields Analyzed:</strong> <?= $analysis['field_analysis']['total_fields'] ?? 0 ?></p>
                            <p><strong>Restaurants:</strong> <?= count($analysis['restaurants']) ?></p>
                        </div>
                        
                        <?php if (isset($filename)): ?>
                        <div class="info-card">
                            <h3>💾 Raw Data</h3>
                            <a href="<?= base_url('api-test/download/' . $filename) ?>" class="download-link">
                                Download JSON
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tabs -->
                    <div class="tabs">
                        <button class="tab active" onclick="showTab('restaurants')">🏪 Restaurants</button>
                        <button class="tab" onclick="showTab('fields')">🔍 Field Analysis</button>
                        <button class="tab" onclick="showTab('database')">🗄️ DB Recommendations</button>
                        <button class="tab" onclick="showTab('raw')">📄 Raw Response</button>
                    </div>

                    <!-- Restaurants Tab -->
                    <div id="restaurants" class="tab-content active">
                        <h2>Found Restaurants</h2>
                        
                        <?php foreach ($analysis['restaurants'] as $restaurant): ?>
                            <div class="restaurant-card">
                                <div class="restaurant-header">
                                    <h3 class="restaurant-title"><?= htmlspecialchars($restaurant['name']) ?></h3>
                                    <?php if ($restaurant['rating'] > 0): ?>
                                        <div class="rating">
                                            ⭐ <?= $restaurant['rating'] ?>/5 (<?= $restaurant['rating_count'] ?>)
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="restaurant-details">
                                    <div class="detail-item">
                                        <span class="icon">📍</span>
                                        <span><?= htmlspecialchars($restaurant['address']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="icon">📞</span>
                                        <span><?= htmlspecialchars($restaurant['phone']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="icon">🌐</span>
                                        <span><?= htmlspecialchars($restaurant['website']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="icon">🏷️</span>
                                        <span><?= htmlspecialchars($restaurant['category']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="icon">💰</span>
                                        <span>Price: <?= htmlspecialchars($restaurant['price_level']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="icon">📸</span>
                                        <span><?= $restaurant['photos_count'] ?> photos</span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="icon">🕐</span>
                                        <span><?= ucfirst(str_replace('_', ' ', $restaurant['current_status'])) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="icon">🆔</span>
                                        <span><?= htmlspecialchars($restaurant['place_id']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="attributes">
                                    <?php if ($restaurant['has_delivery']): ?>
                                        <span class="attribute-tag delivery">🚚 Delivery</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($restaurant['has_takeout']): ?>
                                        <span class="attribute-tag takeout">🥡 Takeout</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($restaurant['accepts_reservations']): ?>
                                        <span class="attribute-tag">📅 Reservations</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($restaurant['wheelchair_accessible']): ?>
                                        <span class="attribute-tag accessible">♿ Accessible</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($restaurant['serves_alcohol']): ?>
                                        <span class="attribute-tag alcohol">🍷 Alcohol</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($restaurant['family_friendly']): ?>
                                        <span class="attribute-tag">👨‍👩‍👧‍👦 Family Friendly</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($restaurant['is_claimed']): ?>
                                        <span class="attribute-tag">✅ Verified</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Field Analysis Tab -->
                    <div id="fields" class="tab-content">
                        <h2>📊 API Response Field Analysis</h2>
                        
                        <div class="field-analysis">
                            <h3>🔑 Important Fields</h3>
                            <div class="field-list">
                                <?php foreach ($analysis['field_analysis']['important_fields'] ?? [] as $field => $info): ?>
                                    <div class="field-item">
                                        <span class="field-name"><?= htmlspecialchars($field) ?></span>
                                        <div>
                                            <span class="field-type"><?= $info['type'] ?></span>
                                            <small>Count: <?= $info['count'] ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="field-analysis">
                            <h3>🖼️ Media Fields</h3>
                            <div class="field-list">
                                <?php foreach ($analysis['field_analysis']['media_fields'] ?? [] as $field => $info): ?>
                                    <div class="field-item">
                                        <span class="field-name"><?= htmlspecialchars($field) ?></span>
                                        <div>
                                            <span class="field-type"><?= $info['type'] ?></span>
                                            <small>Count: <?= $info['count'] ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="field-analysis">
                            <h3>✨ Attribute Fields</h3>
                            <div class="field-list">
                                <?php foreach ($analysis['field_analysis']['attribute_fields'] ?? [] as $field => $info): ?>
                                    <div class="field-item">
                                        <span class="field-name"><?= htmlspecialchars($field) ?></span>
                                        <div>
                                            <span class="field-type"><?= $info['type'] ?></span>
                                            <small>Count: <?= $info['count'] ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Database Recommendations Tab -->
                    <div id="database" class="tab-content">
                        <h2>🗄️ Database Structure Recommendations</h2>
                        
                        <div class="recommendations">
                            <h3>📝 New Columns to Add</h3>
                            <div class="code-block">
<?php foreach ($analysis['db_recommendations']['new_columns'] ?? [] as $column => $description): ?>
ALTER TABLE restaurants ADD COLUMN <?= $column ?> <?= $description ?>;
<?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="recommendations">
                            <h3>📊 JSON Columns for Complex Data</h3>
                            <div class="code-block">
<?php foreach ($analysis['db_recommendations']['json_columns'] ?? [] as $column => $description): ?>
ALTER TABLE restaurants ADD COLUMN <?= $column ?> <?= $description ?>;
<?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="recommendations">
                            <h3>🏗️ Suggested New Tables</h3>
                            <ul>
                                <?php foreach ($analysis['db_recommendations']['new_tables'] ?? [] as $table => $description): ?>
                                    <li><strong><?= $table ?></strong> - <?= $description ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="recommendations">
                            <h3>⚡ Recommended Indexes</h3>
                            <div class="code-block">
<?php foreach ($analysis['db_recommendations']['indexes'] ?? [] as $index => $description): ?>
CREATE INDEX <?= $index ?> ON restaurants(...); -- <?= $description ?>

<?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Raw Response Tab -->
                    <div id="raw" class="tab-content">
                        <h2>📄 Raw API Response</h2>
                        <div class="code-block" style="max-height: 600px; overflow-y: auto;">
                            <pre><?= htmlspecialchars(json_encode($raw_data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="error">
                        <h3>❌ API Request Failed</h3>
                        <p><?= htmlspecialchars($analysis['error'] ?? 'Unknown error') ?></p>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html>