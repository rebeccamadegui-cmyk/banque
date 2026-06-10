<?php
/**
 * Script d'exécution des tests automatisés
 * Lance la suite complète de tests avec rapports
 */

// Charger le TestRunner
require_once __DIR__ . '/TestRunner.php';

// Initialiser et exécuter les tests
$runner = new TestRunner(__DIR__ . '/config.php');
$runner->runAll();

echo "\n✅ Tests terminés. Consultez les rapports pour plus de détails.\n";
?>
