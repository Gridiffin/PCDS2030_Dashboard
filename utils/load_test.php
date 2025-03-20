<?php
/**
 * Simple load testing script for PCDS2030 Dashboard
 * 
 * This script simulates multiple concurrent users accessing the application.
 * Use it to identify potential bottlenecks or issues under load.
 * 
 * Usage: php load_test.php [number_of_requests] [concurrency]
 */

// Only allow execution from command line
if (php_sapi_name() !== 'cli') {
    die("This script can only be executed from the command line.");
}

// Parse command line arguments
$num_requests = isset($argv[1]) && is_numeric($argv[1]) ? (int)$argv[1] : 100;
$concurrency = isset($argv[2]) && is_numeric($argv[2]) ? (int)$argv[2] : 10;

// Configuration
$config = [
    'base_url' => 'http://localhost/pcds2030_dashboard/',
    'endpoints' => [
        'index.php',
        'login.php',
        'php/auth/get_current_user.php',
        'php/metrics/get_submissions.php',
        'php/metrics/get_programs.php'
    ],
    'timeout' => 30,
    'login_credentials' => [
        'username' => 'testuser',
        'password' => 'testpassword'
    ]
];

echo "=== PCDS2030 Dashboard Load Test ===\n";
echo "Testing with $num_requests requests, $concurrency concurrent connections\n\n";

// Track total execution time
$start_time = microtime(true);

// Initialize session and cookies
$cookieJar = tempnam("/tmp", "cookies");
$ch = curl_init();
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, $config['timeout']);

// Login first to establish session
echo "Logging in...\n";
curl_setopt($ch, CURLOPT_URL, $config['base_url'] . 'php/auth/login.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($config['login_credentials']));
$response = curl_exec($ch);
$login_data = json_decode($response, true);

if (!$login_data || !isset($login_data['success']) || !$login_data['success']) {
    echo "Login failed: " . ($login_data['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "Login successful\n\n";

// Prepare multi-curl handler for concurrent requests
$mh = curl_multi_init();
$curl_handles = [];
$results = [];

echo "Starting load test...\n";

// Create requests
for ($i = 0; $i < $num_requests; $i++) {
    // Select an endpoint randomly
    $endpoint = $config['endpoints'][array_rand($config['endpoints'])];
    
    $curl_handles[$i] = curl_init();
    curl_setopt($curl_handles[$i], CURLOPT_URL, $config['base_url'] . $endpoint);
    curl_setopt($curl_handles[$i], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handles[$i], CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl_handles[$i], CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($curl_handles[$i], CURLOPT_TIMEOUT, $config['timeout']);
    
    curl_multi_add_handle($mh, $curl_handles[$i]);
}

// Execute the requests in batches based on concurrency
$running = null;
$completed = 0;
$progress_bar_length = 40;
$endpoint_stats = [];

do {
    curl_multi_exec($mh, $running);
    
    // Process finished requests
    while ($info = curl_multi_info_read($mh)) {
        $ch = $info['handle'];
        $idx = array_search($ch, $curl_handles);
        
        if ($idx !== false) {
            // Get response data
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $endpoint = str_replace($config['base_url'], '', $url);
            $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Store result
            $results[] = [
                'endpoint' => $endpoint,
                'time' => $time,
                'http_code' => $http_code
            ];
            
            // Update endpoint statistics
            if (!isset($endpoint_stats[$endpoint])) {
                $endpoint_stats[$endpoint] = [
                    'count' => 0,
                    'total_time' => 0,
                    'min_time' => PHP_FLOAT_MAX,
                    'max_time' => 0,
                    'success' => 0,
                    'error' => 0
                ];
            }
            
            $endpoint_stats[$endpoint]['count']++;
            $endpoint_stats[$endpoint]['total_time'] += $time;
            $endpoint_stats[$endpoint]['min_time'] = min($endpoint_stats[$endpoint]['min_time'], $time);
            $endpoint_stats[$endpoint]['max_time'] = max($endpoint_stats[$endpoint]['max_time'], $time);
            
            if ($http_code >= 200 && $http_code < 300) {
                $endpoint_stats[$endpoint]['success']++;
            } else {
                $endpoint_stats[$endpoint]['error']++;
            }
            
            // Remove the handle
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            unset($curl_handles[$idx]);
            
            // Update progress
            $completed++;
            $percent = round(($completed / $num_requests) * 100);
            $progress_chars = round(($completed / $num_requests) * $progress_bar_length);
            
            echo "\r[" . str_repeat("#", $progress_chars) . str_repeat(" ", $progress_bar_length - $progress_chars) . "] $percent% ($completed/$num_requests)";
        }
    }
    
    // Wait a bit before checking again if we still have active transfers
    if ($running > 0) {
        usleep(100000); // 100ms
    }
    
} while ($running > 0 || count($curl_handles) > 0);

// Cleanup
curl_multi_close($mh);
unlink($cookieJar);

$total_time = microtime(true) - $start_time;

echo "\n\nLoad test completed in " . number_format($total_time, 2) . " seconds\n\n";

// Analyze results
$total_request_time = array_sum(array_column($results, 'time'));
$avg_request_time = $total_request_time / count($results);
$min_request_time = min(array_column($results, 'time'));
$max_request_time = max(array_column($results, 'time'));

echo "=== Overall Statistics ===\n";
echo "Total requests: " . count($results) . "\n";
echo "Requests per second: " . number_format(count($results) / $total_time, 2) . "\n";
echo "Average request time: " . number_format($avg_request_time * 1000, 2) . " ms\n";
echo "Min request time: " . number_format($min_request_time * 1000, 2) . " ms\n";
echo "Max request time: " . number_format($max_request_time * 1000, 2) . " ms\n\n";

echo "=== Endpoint Statistics ===\n";
foreach ($endpoint_stats as $endpoint => $stats) {
    echo "Endpoint: $endpoint\n";
    echo "  Requests: " . $stats['count'] . "\n";
    echo "  Success rate: " . number_format(($stats['success'] / $stats['count']) * 100, 2) . "%\n";
    echo "  Average time: " . number_format(($stats['total_time'] / $stats['count']) * 1000, 2) . " ms\n";
    echo "  Min time: " . number_format($stats['min_time'] * 1000, 2) . " ms\n";
    echo "  Max time: " . number_format($stats['max_time'] * 1000, 2) . " ms\n";
    echo "\n";
}
