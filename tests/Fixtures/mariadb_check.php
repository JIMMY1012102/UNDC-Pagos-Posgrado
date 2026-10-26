<?php

/**
 * CI-only MariaDB smoke test. No Laravel model, migration, destructive reset or
 * institutional dump is used here. All database credentials are ephemeral CI values.
 *
 * Usage: php tests/Fixtures/mariadb_check.php setup|verify
 */
declare(strict_types=1);

$mode = $argv[1] ?? '';
if (! in_array($mode, ['setup', 'verify'], true)) {
    fwrite(STDERR, "Usage: php tests/Fixtures/mariadb_check.php setup|verify\n");
    exit(2);
}

$connection = new PDO(
    sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST') ?: '127.0.0.1',
        getenv('DB_PORT') ?: '3306',
        getenv('DB_DATABASE') ?: 'undc_tesoreria_ci'
    ),
    getenv('DB_USERNAME') ?: 'root',
    getenv('DB_PASSWORD') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
);

if ($mode === 'setup') {
    $sql = file_get_contents(__DIR__.'/tesoreria_schema_ci.sql');
    if ($sql === false) {
        throw new RuntimeException('Missing sanitized schema fixture');
    }

    $sql = preg_replace('/^--[^\n]*$/m', '', $sql);
    foreach (explode(';', (string) $sql) as $statement) {
        if (trim($statement) !== '') {
            $connection->exec($statement);
        }
    }

    echo "PASS: six sanitized legacy tables created in ephemeral MariaDB.\n";
    exit(0);
}

$expected = [
    'fk_store_office_tm',
    'fk_store_catalog_office_tm',
    'fk_store_catalog_concept_tm',
    'fk_store_order_item_office_tm',
    'fk_store_order_item_concept_tm',
    'fk_store_recon_importer_tm',
    'fk_store_recon_result_tm',
    'fk_store_recon_charge_tm',
    'fk_store_validation_reviewer_tm',
    'fk_store_cert_issuer_tm',
    'fk_store_cert_revoker_tm',
    'fk_store_audit_staff_tm',
];

$stmt = $connection->prepare(
    "SELECT DISTINCT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME LIKE 'tm\\_%'"
);
$stmt->execute();
$found = $stmt->fetchAll(PDO::FETCH_COLUMN);
$missing = array_diff($expected, $found);
if ($missing !== []) {
    throw new RuntimeException('Missing physical legacy FKs: '.implode(', ', $missing));
}

// Prove valid legacy master references can be used without duplicating them.
$connection->exec("INSERT INTO tm_oficina (oficina_id, oficina_nom, estado) VALUES (7, 'Oficina ficticia', 1)");
$connection->exec("INSERT INTO tm_conceptos (id_concepto, codigo_concepto, concepto, costo) VALUES (1, 'TEST-01', 'Concepto ficticio', 10.00)");
$connection->exec("INSERT INTO store_office_settings (legacy_office_id, slug) VALUES (7, 'prueba')");
$connection->exec("INSERT INTO store_catalog_items (legacy_office_id, legacy_concept_id, slug) VALUES (7, 1, 'prueba')");
$row = $connection->query(
    "SELECT c.concepto FROM store_catalog_items AS i
     JOIN tm_conceptos AS c ON c.id_concepto = i.legacy_concept_id
     JOIN tm_oficina AS o ON o.oficina_id = i.legacy_office_id"
)->fetch(PDO::FETCH_ASSOC);
if (($row['concepto'] ?? '') !== 'Concepto ficticio') {
    throw new RuntimeException('Catalog join with canonical treasury masters failed');
}

// Prove physically invalid office references are rejected by InnoDB.
try {
    $connection->exec("INSERT INTO store_office_settings (legacy_office_id, slug) VALUES (999999, 'invalido')");
    throw new RuntimeException('Missing office was accepted despite mandatory FK');
} catch (PDOException $e) {
    if ($e->getCode() !== '23000') {
        throw $e;
    }
}
try {
    $connection->exec("INSERT INTO store_catalog_items (legacy_office_id, legacy_concept_id, slug) VALUES (7, 999999, 'invalido')");
    throw new RuntimeException('Missing concept was accepted despite mandatory FK');
} catch (PDOException $e) {
    if ($e->getCode() !== '23000') {
        throw $e;
    }
}

echo 'PASS: '.count($expected)." real FKs, canonical catalog joins, invalid references rejected.\n";
