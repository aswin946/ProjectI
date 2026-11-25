<?php
/*
 * test_connection.php  (TEMPORARY - Phase 1 only)
 * Checks that PHP, PDO and the PEMS database are ready.
 * DELETE THIS FILE before you submit/demonstrate the final project.
 */
require_once __DIR__ . '/config/config.php';

$checks = [];   // each check: [name, passed (true/false), details]

// Check 1: PHP version
$checks[] = ['PHP version (7.4 or higher needed)', version_compare(PHP_VERSION, '7.4.0', '>='), PHP_VERSION];

// Check 2: PDO MySQL driver
$hasPdoMysql = extension_loaded('pdo_mysql');
$checks[] = ['PDO MySQL extension', $hasPdoMysql, $hasPdoMysql ? 'Enabled' : 'Not enabled (check php.ini)'];

// Check 3: database connection (we do it here so a failure shows as a check, not a crash)
$pdo = null;
if ($hasPdoMysql) {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $checks[] = ['Connection to database "pems_db"', true, 'Connected'];

        // Also confirm that the real project file config/database.php works
        require_once __DIR__ . '/config/database.php';
        $checks[] = ['config/database.php creates $pdo', isset($pdo) && $pdo instanceof PDO, 'OK'];
    } catch (PDOException $e) {
        $checks[] = ['Connection to database "pems_db"', false, $e->getMessage()];
    }
}

// Check 4 and 5: tables and foreign keys
$tableRows = [];
if ($pdo) {
    foreach (['users', 'categories', 'transactions', 'budgets'] as $table) {
        try {
            // Table names come from this fixed list, not from user input
            $count = $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
            $tableRows[] = [$table, true, $count . ' row(s)'];
        } catch (PDOException $e) {
            $tableRows[] = [$table, false, 'Table not found. Did you import database.sql?'];
        }
    }
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL'
    );
    $stmt->execute([DB_NAME]);
    $fkCount = (int) $stmt->fetchColumn();
    $checks[] = ['Foreign keys (5 expected)', $fkCount === 5, $fkCount . ' found'];
}

$allPassed = true;
foreach ($checks as $c)    { if (!$c[1]) $allPassed = false; }
foreach ($tableRows as $r) { if (!$r[1]) $allPassed = false; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PEMS - Phase 1 Test</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<main class="simple-page">
    <h1>PEMS: Phase 1 Test</h1>

    <h2>Environment and connection</h2>
    <table>
        <tr><th>Check</th><th>Result</th><th>Details</th></tr>
        <?php foreach ($checks as $c): ?>
        <tr>
            <td><?php echo htmlspecialchars($c[0]); ?></td>
            <td class="<?php echo $c[1] ? 'ok' : 'fail'; ?>"><?php echo $c[1] ? 'PASS' : 'FAIL'; ?></td>
            <td><?php echo htmlspecialchars($c[2]); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($tableRows): ?>
    <h2>Tables</h2>
    <table>
        <tr><th>Table</th><th>Result</th><th>Details</th></tr>
        <?php foreach ($tableRows as $r): ?>
        <tr>
            <td><?php echo htmlspecialchars($r[0]); ?></td>
            <td class="<?php echo $r[1] ? 'ok' : 'fail'; ?>"><?php echo $r[1] ? 'PASS' : 'FAIL'; ?></td>
            <td><?php echo htmlspecialchars($r[2]); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if ($allPassed): ?>
        <p class="ok">All checks passed. Phase 1 is working.</p>
    <?php else: ?>
        <p class="fail">Some checks failed. Read the Details column and see the troubleshooting section.</p>
    <?php endif; ?>
    <p class="note">This is a temporary test page. It will be deleted before final submission.</p>
</main>
</body>
</html>
