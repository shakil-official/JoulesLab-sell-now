<?php

// Simple debug to see what's happening with routes
echo "=== DEBUGGING ROUTES ===\n";

// Test prefix handling
$prefix = '/cart';
$uri1 = '';
$uri2 = '/';

$route1 = $prefix . $uri1;  // Should be /cart
$route2 = $prefix . $uri2;  // Should be /cart/

echo "Prefix: '$prefix'\n";
echo "URI1: '$uri1' -> Route: '$route1'\n";
echo "URI2: '$uri2' -> Route: '$route2'\n";

// Test what FastRoute would see
echo "\n=== FASTROUTE MATCHING ===\n";
echo "Request: /cart\n";
echo "Would match route: '$route1' ? " . (('/cart' === $route1 ? 'YES' : 'NO') . "\n";
echo "Would match route: '$route2' ? " . (('/cart' === $route2 ? 'YES' : 'NO') . "\n";

echo "\nRequest: /cart/\n";
echo "Would match route: '$route1' ? " . (('/cart/' === $route1 ? 'YES' : 'NO') . "\n";
echo "Would match route: '$route2' ? " . (('/cart/' === $route2 ? 'YES' : 'NO') . "\n";
