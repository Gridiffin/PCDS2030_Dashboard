<?php
// Debug helper to test endpoint responses
// Visit this page in the browser to see what's happening

// Test getting roles
echo "<h2>Testing getRoles endpoint</h2>";
$rolesUrl = 'php/admin/manage_users.php?operation=getRoles';
$rolesCh = curl_init($rolesUrl);
curl_setopt($rolesCh, CURLOPT_RETURNTRANSFER, true);
$rolesResponse = curl_exec($rolesCh);
curl_close($rolesCh);

echo "<pre>";
echo "Response from $rolesUrl:<br>";
echo htmlspecialchars($rolesResponse);
echo "</pre>";

// Test getting agencies
echo "<h2>Testing getAgencies endpoint</h2>";
$agenciesUrl = 'php/admin/manage_users.php?operation=getAgencies';
$agenciesCh = curl_init($agenciesUrl);
curl_setopt($agenciesCh, CURLOPT_RETURNTRANSFER, true);
$agenciesResponse = curl_exec($agenciesCh);
curl_close($agenciesCh);

echo "<pre>";
echo "Response from $agenciesUrl:<br>";
echo htmlspecialchars($agenciesResponse);
echo "</pre>";

// Test getting users
echo "<h2>Testing getUsers endpoint</h2>";
$usersUrl = 'php/admin/manage_users.php?operation=get';
$usersCh = curl_init($usersUrl);
curl_setopt($usersCh, CURLOPT_RETURNTRANSFER, true);
$usersResponse = curl_exec($usersCh);
curl_close($usersCh);

echo "<pre>";
echo "Response from $usersUrl:<br>";
echo htmlspecialchars($usersResponse);
echo "</pre>";
?>
