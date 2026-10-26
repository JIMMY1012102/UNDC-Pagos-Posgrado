# Diccionario de datos (migraciones existentes)

Incluye las 23 tablas de la tienda y los campos observados en las cuatro migraciones. Tipo indicado en sintaxis Laravel; `foreignId` y `id` representan BIGINT UNSIGNED. No sustituye un diccionario SQL del esquema institucional vigente.

## Seis tablas históricas

- tm_oficina: PK `oficina_id` (INT firmado); conservación obligatoria, sin alteraciones.
- tm_rol: PK `rol_id` (INT firmado); conservación obligatoria, sin alteraciones.
- tm_usuario: PK `usu_id` (INT firmado); conservación obligatoria, sin alteraciones.
- tm_conceptos: PK `id_concepto` (INT firmado); conservación obligatoria, sin alteraciones.
- tm_recaudacion: PK `id_recaudacion` (INT firmado); conservación obligatoria, sin alteraciones.
- tm_resultado: PK `i_resultado` (INT firmado); conservación obligatoria, sin alteraciones.

## identidad

### store_customer_profiles

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| user_id | foreignId | FK | No indicado |
| first_names | string |  | Sí |
| last_names | string |  | Sí |
| document_type | string |  | Sí |
| document_number | string |  | Sí |
| phone | string |  | Sí |
| contact_email | string |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** user_id → users.id.

**Índices explícitos:** `$table->index(['document_type', 'document_number'], 'idx_store_customer_document');`.

### store_oauth_identities

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| user_id | foreignId | FK | No indicado |
| provider | string |  | No indicado |
| provider_subject | string |  | No indicado |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** user_id → users.id.

**Índices explícitos:** `$table->unique(['provider', 'provider_subject'], 'uq_store_oauth_identity');`.

### store_beneficiaries

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| customer_profile_id | foreignId | FK | No indicado |
| first_names | string |  | No indicado |
| last_names | string |  | No indicado |
| document_type | string |  | No indicado |
| document_number | string |  | No indicado |
| email | string |  | Sí |
| phone | string |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** customer_profile_id → store_customer_profiles.id.

**Índices explícitos:** `$table->index(['document_type', 'document_number'], 'idx_store_beneficiary_document');`.

## catalogo

### store_office_settings

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| legacy_office_id | integer | FK | No indicado |
| slug | string |  | No indicado |
| catalog_enabled | boolean |  | No indicado |
| display_name | string |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** legacy_office_id → tm_oficina.oficina_id.

**Índices explícitos:** Consultar restricciones inline (unique/PK/FK).

### store_catalog_items

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| legacy_concept_id | integer | FK | No indicado |
| legacy_office_id | integer | FK | No indicado |
| slug | string |  | No indicado |
| description | text |  | Sí |
| requirements | text |  | Sí |
| category | string |  | Sí |
| requires_eligibility_check | boolean |  | No indicado |
| is_published | boolean |  | No indicado |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** legacy_office_id → tm_oficina.oficina_id; legacy_concept_id → tm_conceptos.id_concepto.

**Índices explícitos:** `$table->unique(['legacy_office_id', 'legacy_concept_id'], 'uq_store_catalog_legacy_pair');`; `$table->unique(['legacy_office_id', 'slug'], 'uq_store_catalog_slug');`; `$table->index(['legacy_concept_id'], 'idx_store_catalog_legacy_concept');`.

### store_catalog_price_versions

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| catalog_item_id | foreignId | FK | No indicado |
| amount | decimal |  | No indicado |
| currency | char |  | No indicado |
| valid_from | timestamp |  | No indicado |
| valid_until | timestamp |  | Sí |
| approval_reference | string |  | Sí |
| is_approved | boolean |  | No indicado |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** catalog_item_id → store_catalog_items.id.

**Índices explícitos:** `$table->index(['catalog_item_id', 'valid_from', 'valid_until'], 'idx_store_price_validity');`.

## pedidos

### store_carts

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| user_id | foreignId | FK | Sí |
| token_hash | char |  | Sí |
| currency | char |  | No indicado |
| status | string |  | No indicado |
| expires_at | timestamp |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** user_id → users.id.

**Índices explícitos:** `$table->index(['user_id', 'status'], 'idx_cart_owner_status');`.

### store_cart_items

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| cart_id | foreignId | FK | No indicado |
| catalog_item_id | foreignId | FK | No indicado |
| beneficiary_id | foreignId | FK | Sí |
| quantity | unsignedInteger |  | No indicado |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** cart_id → store_carts.id; catalog_item_id → store_catalog_items.id; beneficiary_id → store_beneficiaries.id.

**Índices explícitos:** `$table->index(['cart_id', 'catalog_item_id'], 'idx_cart_concept');`.

### store_orders

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| code | string |  | No indicado |
| user_id | foreignId | FK | No indicado |
| cart_id | foreignId | FK | Sí |
| status | string |  | No indicado |
| currency | char |  | No indicado |
| total_amount | decimal |  | No indicado |
| buyer_first_names | string |  | No indicado |
| buyer_last_names | string |  | No indicado |
| buyer_document_type | string |  | No indicado |
| buyer_document_number | string |  | No indicado |
| buyer_email | string |  | No indicado |
| buyer_phone | string |  | Sí |
| expires_at | timestamp |  | Sí |
| confirmed_at | timestamp |  | Sí |
| paid_at | timestamp |  | Sí |
| cancelled_at | timestamp |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** user_id → users.id; cart_id → store_carts.id.

**Índices explícitos:** `$table->index(['user_id', 'created_at'], 'idx_orders_customer_date');`; `$table->index(['status', 'created_at'], 'idx_orders_status_date');`.

### store_order_items

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| order_id | foreignId | FK | No indicado |
| catalog_item_id | foreignId | FK | No indicado |
| legacy_concept_id | integer | FK | No indicado |
| legacy_office_id | integer | FK | No indicado |
| beneficiary_id | foreignId | FK | Sí |
| concept_code | string |  | No indicado |
| concept_name | string |  | No indicado |
| quantity | unsignedInteger |  | No indicado |
| unit_amount | decimal |  | No indicado |
| total_amount | decimal |  | No indicado |
| beneficiary_first_names | string |  | No indicado |
| beneficiary_last_names | string |  | No indicado |
| beneficiary_document_type | string |  | No indicado |
| beneficiary_document_number | string |  | No indicado |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** order_id → store_orders.id; catalog_item_id → store_catalog_items.id; beneficiary_id → store_beneficiaries.id; legacy_office_id → tm_oficina.oficina_id; legacy_concept_id → tm_conceptos.id_concepto.

**Índices explícitos:** `$table->index(['legacy_office_id', 'order_id'], 'idx_items_office_order');`.

### store_order_status_history

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| order_id | foreignId | FK | No indicado |
| actor_user_id | foreignId | FK | Sí |
| from_status | string |  | Sí |
| to_status | string |  | No indicado |
| reason | string |  | Sí |
| context | json |  | Sí |
| occurred_at | timestamp |  | No indicado |

**FK:** order_id → store_orders.id; actor_user_id → users.id.

**Índices explícitos:** `$table->index(['order_id', 'occurred_at'], 'idx_order_history');`.

## pagos

### store_payment_attempts

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| order_id | foreignId | FK | No indicado |
| channel | string |  | No indicado |
| provider | string |  | No indicado |
| status | string |  | No indicado |
| idempotency_key | string |  | No indicado |
| provider_session_reference | string |  | Sí |
| requested_amount | decimal |  | No indicado |
| currency | char |  | No indicado |
| started_at | timestamp |  | No indicado |
| finished_at | timestamp |  | Sí |
| non_sensitive_metadata | json |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** order_id → store_orders.id.

**Índices explícitos:** `$table->index(['order_id', 'status'], 'idx_attempts_order_status');`; `$table->unique(['provider', 'provider_session_reference'], 'uq_provider_session');`.

### store_payment_transactions

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| order_id | foreignId | FK | No indicado |
| payment_attempt_id | foreignId | FK | Sí |
| provider | string |  | No indicado |
| provider_transaction_reference | string |  | Sí |
| status | string |  | No indicado |
| amount | decimal |  | No indicado |
| currency | char |  | No indicado |
| verification_source | string |  | Sí |
| observed_at | timestamp |  | No indicado |
| verified_at | timestamp |  | Sí |
| settled_at | timestamp |  | Sí |
| evidence_digest | char |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** order_id → store_orders.id; payment_attempt_id → store_payment_attempts.id.

**Índices explícitos:** `$table->unique(['provider', 'provider_transaction_reference'], 'uq_provider_transaction');`; `$table->index(['order_id', 'status'], 'idx_transactions_order_status');`.

### store_order_payment_claims

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| order_id | foreignId | FK | No indicado |
| payment_transaction_id | foreignId | FK | No indicado |
| claimed_at | timestamp |  | No indicado |

**FK:** order_id → store_orders.id; payment_transaction_id → store_payment_transactions.id.

**Índices explícitos:** Consultar restricciones inline (unique/PK/FK).

### store_payment_webhook_events

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| provider | string |  | No indicado |
| event_key | string |  | No indicado |
| payment_attempt_id | foreignId | FK | Sí |
| processing_status | string |  | No indicado |
| signature_valid | boolean |  | No indicado |
| payload_digest | char |  | No indicado |
| received_at | timestamp |  | No indicado |
| processed_at | timestamp |  | Sí |
| failure_reason | string |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** payment_attempt_id → store_payment_attempts.id.

**Índices explícitos:** `$table->unique(['provider', 'event_key'], 'uq_gateway_event');`; `$table->index(['processing_status', 'received_at'], 'idx_events_processing');`.

### store_payment_incidents

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| order_id | foreignId | FK | No indicado |
| payment_transaction_id | foreignId | FK | Sí |
| kind | string |  | No indicado |
| status | string |  | No indicado |
| description | text |  | Sí |
| resolved_by | foreignId | FK | Sí |
| resolved_at | timestamp |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** order_id → store_orders.id; payment_transaction_id → store_payment_transactions.id; resolved_by → users.id.

**Índices explícitos:** `$table->index(['kind', 'status'], 'idx_incident_queue');`.

### store_refunds

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| payment_transaction_id | foreignId | FK | No indicado |
| provider_refund_reference | string |  | Sí |
| amount | decimal |  | No indicado |
| currency | char |  | No indicado |
| status | string |  | No indicado |
| reason | string |  | Sí |
| requested_by | foreignId | FK | Sí |
| requested_at | timestamp |  | No indicado |
| completed_at | timestamp |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** payment_transaction_id → store_payment_transactions.id; requested_by → users.id.

**Índices explícitos:** `$table->unique(['payment_transaction_id', 'provider_refund_reference'], 'uq_refund_provider_reference');`.

## conciliacion

### store_reconciliation_batches

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| provider | string |  | No indicado |
| channel | string |  | No indicado |
| external_batch_reference | string |  | Sí |
| source_file_digest | char |  | Sí |
| status | string |  | No indicado |
| period_from | date |  | Sí |
| period_until | date |  | Sí |
| reported_total | decimal |  | Sí |
| currency | char |  | No indicado |
| legacy_importer_id | integer | FK | Sí |
| reconciled_at | timestamp |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** legacy_importer_id → tm_usuario.usu_id.

**Índices explícitos:** `$table->unique(['provider', 'external_batch_reference'], 'uq_recon_provider_batch');`; `$table->unique(['provider', 'source_file_digest'], 'uq_recon_provider_file');`; `$table->index(['provider', 'status'], 'idx_recon_queue');`.

### store_reconciliation_entries

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| reconciliation_batch_id | foreignId | FK | No indicado |
| row_number | unsignedInteger |  | No indicado |
| legacy_result_id | integer | FK | Sí |
| legacy_recaudacion_id | integer | FK | Sí |
| payment_transaction_id | foreignId | FK | Sí |
| external_payment_reference | string |  | Sí |
| debtor_code | string |  | Sí |
| concept_code | string |  | Sí |
| installment_reference | string |  | Sí |
| gross_amount | decimal |  | No indicado |
| fee_amount | decimal |  | No indicado |
| net_amount | decimal |  | No indicado |
| currency | char |  | No indicado |
| status | string |  | No indicado |
| bank_operation_at | timestamp |  | Sí |
| matched_at | timestamp |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** reconciliation_batch_id → store_reconciliation_batches.id; payment_transaction_id → store_payment_transactions.id; legacy_result_id → tm_resultado.i_resultado; legacy_recaudacion_id → tm_recaudacion.id_recaudacion.

**Índices explícitos:** `$table->unique(['reconciliation_batch_id', 'row_number'], 'uq_recon_batch_row');`; `$table->index(['external_payment_reference'], 'idx_recon_reference');`; `$table->index('legacy_result_id', 'idx_recon_legacy_result');`; `$table->index('legacy_recaudacion_id', 'idx_recon_legacy_recaudacion');`; `$table->index(['status', 'bank_operation_at'], 'idx_recon_status_date');`.

## control

### store_external_system_links

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| system_code | string |  | No indicado |
| external_entity | string |  | No indicado |
| external_record_key | string |  | No indicado |
| legacy_record_id | integer |  | Sí |
| order_id | foreignId | FK | Sí |
| payment_transaction_id | foreignId | FK | Sí |
| sync_status | string |  | No indicado |
| last_synced_at | timestamp |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** order_id → store_orders.id; payment_transaction_id → store_payment_transactions.id.

**Índices explícitos:** `$table->unique(['system_code', 'external_entity', 'external_record_key'], 'uq_external_link');`; `$table->index(['order_id', 'system_code'], 'idx_external_order');`.

### store_payment_validations

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| order_id | foreignId | FK | No indicado |
| payment_transaction_id | foreignId | FK | No indicado |
| legacy_reviewer_id | integer | FK | Sí |
| status | string |  | No indicado |
| evidence_reference | string |  | Sí |
| decision_reason | string |  | Sí |
| reviewed_at | timestamp |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** order_id → store_orders.id; payment_transaction_id → store_payment_transactions.id; legacy_reviewer_id → tm_usuario.usu_id.

**Índices explícitos:** `$table->index(['status', 'created_at'], 'idx_validation_queue');`.

### store_validation_certificates

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| payment_validation_id | foreignId | FK | No indicado |
| order_id | foreignId | FK | No indicado |
| certificate_code | string |  | No indicado |
| verification_token_hash | char |  | No indicado |
| status | string |  | No indicado |
| legacy_issuer_id | integer | FK | Sí |
| issued_at | timestamp |  | No indicado |
| legacy_revoker_id | integer | FK | Sí |
| revoked_at | timestamp |  | Sí |
| revocation_reason | string |  | Sí |
| created_at | timestamps |  | No indicado |
| updated_at | timestamps |  | No indicado |

**FK:** payment_validation_id → store_payment_validations.id; order_id → store_orders.id; legacy_issuer_id → tm_usuario.usu_id; legacy_revoker_id → tm_usuario.usu_id.

**Índices explícitos:** `$table->index(['order_id', 'status'], 'idx_certificate_order');`.

### store_audit_events

| Campo | Tipo Laravel | Clave | ¿Nullable? |
|---|---|---|---|
| id | id | PK | No indicado |
| actor_buyer_user_id | foreignId | FK | Sí |
| legacy_staff_id | integer | FK | Sí |
| event_name | string |  | No indicado |
| subject_type | string |  | No indicado |
| correlation_id | string |  | Sí |
| safe_context | json |  | Sí |
| occurred_at | timestamp |  | No indicado |

**FK:** actor_buyer_user_id → users.id; legacy_staff_id → tm_usuario.usu_id.

**Índices explícitos:** `$table->index(['subject_type', 'subject_id', 'occurred_at'], 'idx_audit_subject');`; `$table->index(['event_name', 'occurred_at'], 'idx_audit_event');`.
