# 7. Seguridad y Laravel

Roles y permisos Spatie, sesiones y soporte de Laravel. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  password_reset_tokens {
    VARCHAR email PK
  }
  sessions {
    VARCHAR id PK
  }
  cache {
    VARCHAR key PK
  }
  cache_locks {
    VARCHAR key PK
  }
  jobs {
    BIGINT id PK
  }
  job_batches {
    VARCHAR id PK
  }
  failed_jobs {
    BIGINT id PK
  }
  migrations {
    BIGINT id PK
  }
  roles {
    BIGINT id PK
  }
  permissions {
    BIGINT id PK
  }
  model_has_roles {
    BIGINT id PK
  }
  model_has_permissions {
    BIGINT id PK
  }
  role_has_permissions {
    BIGINT id PK
  }
  roles ||--o{ model_has_roles : "role_id"
  permissions ||--o{ model_has_permissions : "permission_id"
  roles ||--o{ role_has_permissions : "role_id"
  permissions ||--o{ role_has_permissions : "permission_id"
``

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
