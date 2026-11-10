<?php

/**
 * CI-only MariaDB smoke test. No Laravel model, migration, destructive reset or
 * institutional dump is used here. All database credentials are ephemeral CI values.
 *
 * Usage: php tests/Fixtures/mariadb_check.php setup|verify
 */
declare(strict_types=1);

/**
 * Verify all six original tm_* table definitions remain byte-for-byte identical
 * when we apply store migrations in ephemeral GitHub Actions MariaDB.
 */
function legacyFingerprint(PDO $pdo): string
{
    $tables = ['tm_conceptos', 'tm_oficina', 'tm_recaudacion', 'tm_resultado', 'tm_rol', 'tm_usuario'];
    $ddl = [];
    foreach ($tables as $table) {
        $row = $pdo->query("SHOW CREATE TABLE {$table}")->fetch(PDO::FETCH_ASSOC);
        if (! isset($row['Create Table'])) {
            throw new RuntimeException("Missing original SISPAGOS table: {$table}");
        }
        $ddl[$table] = $row['Create Table'];
    }

    return hash('sha256', json_encode($ddl, JSON_THROW_ON_ERROR));
}

$legacyFingerprintPath = sys_get_temp_dir().'/undc_ci_legacy_modular_fingerprint.sha256';

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

    if (file_put_contents($legacyFingerprintPath, legacyFingerprint($connection)) === false) {
        throw new RuntimeException('Failed to store original SISPAGOS schema signature');
    }

    echo "PASS: six sanitized legacy tables created in ephemeral MariaDB.\n";
    exit(0);
}

$baseline = @file_get_contents($legacyFingerprintPath);
if ($baseline === false || $baseline !== legacyFingerprint($connection)) {
    throw new RuntimeException('A migration changed the original SISPAGOS schema');
}

// 6 legacy + 24 store + 9 Laravel core + 5 Spatie permissions = 44.
$numberOfTables = (int) $connection->query(
    "SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'"
)->fetchColumn();
if ($numberOfTables !== 44) {
    throw new RuntimeException("The expected 44 tables were not created (found {$numberOfTables})");
}

$expected = [
    'fk_categoria_oficina_tm',
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
     WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IN ('tm_oficina', 'tm_conceptos', 'tm_usuario', 'tm_resultado', 'tm_recaudacion')"
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
$connection->exec("INSERT INTO tienda_configuracion_oficinas (legacy_office_id, slug) VALUES (7, 'prueba')");
$connection->exec("INSERT INTO tienda_conceptos_catalogo (legacy_office_id, legacy_concept_id, slug) VALUES (7, 1, 'prueba')");
$row = $connection->query(
    "SELECT c.concepto FROM tienda_conceptos_catalogo AS i
     JOIN tm_conceptos AS c ON c.id_concepto = i.legacy_concept_id
     JOIN tm_oficina AS o ON o.oficina_id = i.legacy_office_id"
)->fetch(PDO::FETCH_ASSOC);
if (($row['concepto'] ?? '') !== 'Concepto ficticio') {
    throw new RuntimeException('Catalog join with canonical treasury masters failed');
}

// Prove physically invalid office references are rejected by InnoDB.
try {
    $connection->exec("INSERT INTO tienda_configuracion_oficinas (legacy_office_id, slug) VALUES (999999, 'invalido')");
    throw new RuntimeException('Missing office was accepted despite mandatory FK');
} catch (PDOException $e) {
    if ($e->getCode() !== '23000') {
        throw $e;
    }
}
try {
    $connection->exec("INSERT INTO tienda_conceptos_catalogo (legacy_office_id, legacy_concept_id, slug) VALUES (7, 999999, 'invalido')");
    throw new RuntimeException('Missing concept was accepted despite mandatory FK');
} catch (PDOException $e) {
    if ($e->getCode() !== '23000') {
        throw $e;
    }
}

// El selector usa una FK simple; la FK compuesta protege el ámbito de oficina.
$connection->exec("INSERT INTO tm_oficina (oficina_id, oficina_nom, estado) VALUES (9, 'Otra oficina ficticia', 1)");
$connection->exec("INSERT INTO tienda_categorias (legacy_office_id, codigo, nombre) VALUES (7, 'certificados_constancias', 'Certificados y constancias')");
$categoryId = (int) $connection->lastInsertId();
$connection->exec("UPDATE tienda_conceptos_catalogo SET categoria_id = {$categoryId} WHERE slug = 'prueba'");
$connection->exec("INSERT INTO tienda_categorias (legacy_office_id, codigo, nombre) VALUES (9, 'certificados_constancias', 'Certificados de otra oficina')");
$otherCategoryId = (int) $connection->lastInsertId();
try {
    $connection->exec("UPDATE tienda_conceptos_catalogo SET categoria_id = {$otherCategoryId} WHERE slug = 'prueba'");
    throw new RuntimeException('Una publicación aceptó una categoría de otra oficina');
} catch (PDOException $e) {
    if ($e->getCode() !== '23000') {
        throw $e;
    }
}
try {
    $connection->exec("DELETE FROM tienda_categorias WHERE id = {$categoryId}");
    throw new RuntimeException('Se eliminó una categoría referenciada');
} catch (PDOException $e) {
    if ($e->getCode() !== '23000') {
        throw $e;
    }
}

echo 'PASS: '.count($expected)." real FKs, canonical catalog joins, invalid references rejected.\n";
