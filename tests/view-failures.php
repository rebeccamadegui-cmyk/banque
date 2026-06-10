<?php
$json = json_decode(file_get_contents(__DIR__ . '/../test-reports/report-2026-06-10-161529.json'), true);

echo "\n❌ TESTS ÉCHOUÉS - DÉTAILS:\n";
echo str_repeat("=", 70) . "\n\n";

$failed_count = 0;
foreach ($json['results'] as $r) {
    if (!($r['passed'] ?? false)) {
        $failed_count++;
        echo "[$failed_count] {$r['name']}\n";
        echo "    Expected HTTP: {$r['expected_code']}\n";
        echo "    Got HTTP: {$r['http_code']}\n";
        echo "    Endpoint: {$r['endpoint']}\n";
        echo "\n";
    }
}

echo str_repeat("=", 70) . "\n";
echo "\n📊 RÉSUMÉ:\n";
echo "- Total Tests: {$json['statistics']['total_tests']}\n";
echo "- Réussis: {$json['statistics']['passed']} ✅\n";
echo "- Échoués: {$json['statistics']['failed']} ❌\n";
echo "- Taux: {$json['statistics']['pass_rate']}%\n";
?>
