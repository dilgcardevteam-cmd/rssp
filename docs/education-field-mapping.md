# Education Field Mapping Guide (Admin + Developer)

This guide explains how to maintain related-degree matching used by qualification checks.

## Source of Truth

Mapping is maintained in:

- `config/education_field_mapping.php`

Main keys:

- `field_groups`: Canonical discipline groups and their aliases.
- `related_groups`: Which groups are accepted as related to each canonical group.

Evaluator logic reads this config, so HR can update mappings without changing controller code.

## How `field_groups` Works

`field_groups` maps one canonical group key to many text aliases.

Example:

```php
'field_groups' => [
    'public_administration' => [
        'public administration',
        'public admin',
        'master in public administration',
        'master of public administration',
        'mpa',
    ],
]
```

Meaning:

- If requirement/applicant degree text contains one of these aliases, it is treated as `public_administration`.

## How `related_groups` Works

`related_groups` defines which groups are acceptable as "or related field".

Example:

```php
'related_groups' => [
    'public_administration' => ['political_science', 'governance', 'public_policy'],
]
```

Meaning:

- Requirement group = `public_administration`
- Applicant group can be any listed related group and still pass when requirement says "or related field".

## Safe Process To Add a New Discipline

1. Add a new canonical key under `field_groups`.
2. Add clear aliases (common degree names/abbreviations).
3. Add explicit related keys under `related_groups` only when policy allows.
4. Keep aliases discipline-specific; avoid generic words (`science`, `management`, `studies`) by themselves.
5. Run qualification tests:
   - `php artisan test --filter QualificationGatePolicyTest`
6. If config cache is enabled, refresh cache:
   - `php artisan config:clear`
   - or `php artisan optimize:clear`

## Correct vs Incorrect Mapping Examples

Correct:

- `statistics` aliases include `statistics`, `applied statistics`, `biostatistics`.
- `statistics` related groups include `mathematics`, `applied_mathematics`, `data_science`.
- `public_administration` related groups include `political_science`, `governance`, `public_policy`.

Incorrect:

- Using broad aliases like `science`, `arts`, `management` as standalone aliases.
- Marking unrelated groups as related (example: `statistics` -> `public_administration`) without policy basis.
- Adding a group in `related_groups` that is not defined in `field_groups`.

## Strict Unmapped Behavior (Important)

Current policy is strict for unmapped fields:

- If a field is not recognized in `field_groups`, it does **not** auto-pass as related.
- Unmapped text only passes by direct phrase match (exact/specific text presence), not fuzzy similarity.

This is intentional to avoid false positives.

## Included Sample Government-Relevant Groups

The default config includes seed/sample mappings for common public sector tracks:

- Statistics / Analytics
- Governance / Public Administration / Public Policy
- Budget / Finance / Accountancy / Auditing
- ICT (IT / CS / IS / Software Engineering)
- HR / Social Services

Expand only with approved HR policy and test each change.

