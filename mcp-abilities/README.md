# MCP Ability Collection — Novamira WordPress Server

Collected from the `novamira-growwithrokibul` MCP endpoint via the
`mcp-adapter/discover-abilities` and `mcp-adapter/get-ability-info` tools.

- **Collection date:** 2026-07-21
- **Server environment:** WordPress 7.0.2 · PHP 8.1.34 · Locale en_US
- **Active theme:** Hello Elementor
- **Total abilities:** 28
- Machine-readable schemas: [`abilities.json`](abilities.json)

## Installed plugins on the server

| Plugin | Version | Status |
|---|---|---|
| Advanced Custom Fields | 6.8.6 | active |
| Akismet Anti-spam | 5.7 | inactive |
| Elementor | 4.2.0 | active |
| Hello Dolly | 1.7.2 | inactive |
| Novamira | 1.9.2 | active |
| Ultimate Addons for Elementor (UAE) | 2.9.2 | active |
| WPCode Lite | 2.3.7 | active |

## Server-side Novamira skills

- `gutenberg-edit-content` *(built-in)* — create/edit WordPress content in the native Gutenberg block editor.
- `skill-creator` *(built-in)* — guidance for creating and refining Novamira skills stored in WordPress.

---

## Ability index

| # | Ability | Label | Read-only | Destructive | Idempotent |
|---|---|---|---|---|---|
| 1 | `novamira/execute-php` | Execute PHP Code | no | yes | no |
| 2 | `novamira/read-file` | Read File | yes | no | yes |
| 3 | `novamira/write-file` | Write File | no | no | yes |
| 4 | `novamira/edit-file` | Edit File | no | no | yes |
| 5 | `novamira/delete-file` | Delete File | no | yes | yes |
| 6 | `novamira/create-upload-link` | Create Upload Link | no | no | no |
| 7 | `novamira/create-admin-access-link` | Create Admin Access Link | no | no | no |
| 8 | `novamira/disable-file` | Disable File | no | no | yes |
| 9 | `novamira/enable-file` | Enable File | no | no | yes |
| 10 | `novamira/list-directory` | List Directory | yes | no | yes |
| 11 | `mcp-adapter/discover-abilities` | Discover Abilities | yes | no | yes |
| 12 | `novamira/run-wp-cli` | Run WP-CLI Command | no | yes | no |
| 13 | `novamira/get-wp-cli-job` | Get WP-CLI Job Status | yes | no | yes |
| 14 | `novamira/gutenberg-get-finalizer-runtime` | Get Block Editor Queue Runtime | yes | no | yes |
| 15 | `novamira/gutenberg-get-content` | Get Gutenberg Content | yes | no | yes |
| 16 | `novamira/gutenberg-write-content` | Write Gutenberg Content | no | yes | yes |
| 17 | `novamira/gutenberg-create-pending-batch` | Create Gutenberg Pending Batch | no | no | no |
| 18 | `novamira/gutenberg-add-pending-change` | Add Gutenberg Pending Change | no | yes | no |
| 19 | `novamira/gutenberg-enable-batch-finalization` | Enable Gutenberg Batch Finalization | no | yes | yes |
| 20 | `novamira/gutenberg-get-pending-batch` | Get Gutenberg Pending Batch | yes | no | yes |
| 21 | `novamira/gutenberg-list-pending-batches` | List Gutenberg Pending Batches | yes | no | yes |
| 22 | `novamira/gutenberg-delete-pending-batch` | Delete Gutenberg Pending Batch | no | yes | yes |
| 23 | `novamira/gutenberg-delete-pending-change` | Delete Gutenberg Pending Change | no | yes | yes |
| 24 | `novamira/gutenberg-get-finalization-url` | Get Gutenberg Finalization URL | yes | no | yes |
| 25 | `novamira/skill-get` | Get Skill | yes | no | yes |
| 26 | `novamira/skill-write` | Write Skill | no | yes | no |
| 27 | `novamira/skill-edit` | Edit Skill | no | no | no |
| 28 | `novamira/skill-delete` | Delete Skill | no | yes | yes |

---

## Ability details

### 1. `novamira/execute-php` — Execute PHP Code

Executes PHP code on the WordPress server. The full WordPress environment is available
including `$wpdb`, all WordPress functions, and loaded plugins. Returns the return value,
any echoed output, and captured warnings/notices.

- **Input:** `code` (string, required) — PHP code without `<?php` tags; use `return $value;` to inspect data.
- **Output:** `success`, `return_value`, `output`, `errors[]`, `error_message`, `error_class`, `execution_time_ms`.
- **Notes:** 30-second time limit; never call `exit()`/`die()`; eval'd code does not persist —
  persist PHP by writing files to the sandbox (`wp-content/novamira-sandbox/`).

### 2. `novamira/read-file` — Read File

Reads a file from the server filesystem. Binary/invalid-UTF-8 files come back base64-encoded.
Supports partial reads.

- **Input:** `path` (required), `offset` (default 0), `limit` (bytes, default 1 MiB, `-1` for all).
- **Output:** `path`, `content`, `encoding` (`utf-8`|`base64`), `size`, `bytes_read`, `truncated`, `mime_type`.

### 3. `novamira/write-file` — Write File

Writes small UTF-8 text to a file. PHP files can ONLY be written to the sandbox
(`wp-content/novamira-sandbox/`); other non-PHP files can go anywhere under ABSPATH.
Creates parent directories automatically. No base64/binary — use `create-upload-link` for that.

- **Input:** `path`, `content` (both required), `encoding` (`utf-8` only), `mode` (`overwrite`|`append`), `create_directories` (default true).
- **Output:** `path`, `bytes_written`, `created`, `directories_created[]`, `size`.
- **Crash recovery:** if a sandbox plugin fatals, the loader enters safe mode; fix the file then
  delete `wp-content/novamira-sandbox/.crashed` to exit safe mode.

### 4. `novamira/edit-file` — Edit File

Exact-string find-and-replace edit of an existing file (like an AI code agent's edit tool).
The old string must be unique unless `replace_all` is set.

- **Input:** `path`, `old_string`, `new_string` (required), `replace_all` (default false).
- **Output:** `path`, `replacements`, `size`.
- **Notes:** same PHP-sandbox rules as write-file.

### 5. `novamira/delete-file` — Delete File

Deletes a file or directory. Non-empty directories require `recursive`. ABSPATH root,
`wp-admin`, and `wp-includes` are protected. Idempotent.

- **Input:** `path` (required), `recursive` (default false).
- **Output:** `path`, `type` (`file`|`directory`|`not_found`), `deleted`, `items_deleted`.

### 6. `novamira/create-upload-link` — Create Upload Link

Creates a temporary upload endpoint + header-only bearer token for uploading one file
(ZIPs, plugins, themes, media, binaries, large payloads). Accepts raw PUT/POST bodies and
multipart form-data with a `file` field.

- **Input:** `path` (required), `expires_in` (30–3600 s, default 900), `max_bytes` (default 512 MiB), `overwrite` (default false), `create_directories` (default true).
- **Output:** `upload_url`, `upload_token`, `token_header`, `method`, `path`, `expires_at`, `max_bytes`, `overwrite`, `curl_examples[]`.
- **Notes:** PHP uploads only allowed into the sandbox directory.

### 7. `novamira/create-admin-access-link` — Create Admin Access Link

Creates a temporary one-time wp-admin access exchange for browser automation. POST the
returned token and nonce in headers to receive a short-lived one-time login URL.

- **Input:** `expires_in` (30–600 s, default 300), `session_expires_in` (60–3600 s, default 1800), `admin_path` (wp-admin-relative, external URLs rejected).
- **Output:** `exchange_url`, `exchange_method`, `access_token`, `token_header`, `access_nonce`, `nonce_header`, `expires_at`, `session_expires_in`, `redirect_url`, `one_time`, `curl_example`.
- **Notes:** login URL nonce expires in ≤60 s; tokens are never accepted in query strings.

### 8. `novamira/disable-file` — Disable File

Disables a sandbox file by appending `.disabled` so the loader skips it. Safer than
deleting; sandbox-only.

- **Input:** `path` (required, inside `wp-content/novamira-sandbox/`).
- **Output:** `original_path`, `disabled_path`, `disabled`.

### 9. `novamira/enable-file` — Enable File

Re-enables a `.disabled` sandbox file (accepts original or disabled filename). Sandbox-only.

- **Input:** `path` (required).
- **Output:** `disabled_path`, `enabled_path`, `enabled`.

### 10. `novamira/list-directory` — List Directory

Lists files/directories with glob filtering, recursive listing, and hidden-file inclusion.
Directories first, then alphabetical; capped output.

- **Input:** `path` (default ABSPATH), `pattern` (default `*`), `recursive` (default false), `max_depth` (1–10, default 3), `include_hidden` (default false), `limit` (1–5000, default 500).
- **Output:** `path`, `entries[]` (`name`, `path`, `type`, `size`, `permissions`, `modified`), `total`, `truncated`.

### 11. `mcp-adapter/discover-abilities` — Discover Abilities

Lists all registered WordPress abilities with basic info plus Novamira environment
instructions. No input parameters.

- **Output:** `novamira_instructions`, `abilities[]` (`name`, `label`, `description`).

### 12. `novamira/run-wp-cli` — Run WP-CLI Command

Runs a WP-CLI command synchronously (default) or asynchronously in the background.

- **Input:** `args` (string array, required, e.g. `["plugin", "list", "--format=json"]`), `async` (default false).
- **Output:** `success`, `exit_code`, `stdout`, `stderr`, `job_id` (async), `pid` (async).

### 13. `novamira/get-wp-cli-job` — Get WP-CLI Job Status

Checks a background WP-CLI job's status and reads its output log.

- **Input:** `job_id` (required), `offset` (default 0), `limit` (default 1 MiB, `-1` for all).
- **Output:** `success`, `job_id`, `status` (`running`|`completed`|`not_found`), `exit_code`, `stdout`, `bytes_read`, `truncated`.

### 14. `novamira/gutenberg-get-finalizer-runtime` — Get Block Editor Queue Runtime

Reports whether the Novamira Block Editor Queue admin page is open and heartbeating,
including token-gated SSE and poll URLs watchable with curl. Call at the start of
Gutenberg work; if offline, ask the user to open the returned queue page URL.

- **Input:** none.
- **Output:** `finalizer_runtime` (object with `online`, `sse_url`, `poll_url`, `dashboard_url`, …), `user_instruction`.

### 15. `novamira/gutenberg-get-content` — Get Gutenberg Content

Reads the live saved Gutenberg `post_content` for one target as a compact parsed block
tree. Also reports queue runtime status; summarizes any pending queued change separately.

- **Input:** `target_id`/`post_id` (one required), `target_type`/`post_type`, `max_depth` (default 4), `include_attributes` (default true), `include_raw_content` (default false).
- **Output:** `target_id`, `target_type`, `target_title`, `live_content_only`, `blocks[]`, `pending_gutenberg_change`, `finalizer_runtime`, `user_instruction`, `raw_content`.

### 16. `novamira/gutenberg-write-content` — Write Gutenberg Content

Directly writes Gutenberg `post_content` — only when every supplied block is a registered
Novamira-owned dynamic-only block. Native/static blocks must go through the pending queue.

- **Input:** `block_spec` (array of `{name, attributes, innerBlocks}`, required), `target_id`/`post_id` (one required), `target_type`/`post_type`.
- **Output:** `target_id`, `target_type`, `written`, `finalization_required`, `warnings[]`.

### 17. `novamira/gutenberg-create-pending-batch` — Create Gutenberg Pending Batch

Creates an empty draft pending batch. Optional convenience — `gutenberg-add-pending-change`
auto-creates a batch when `batch_id` is omitted.

- **Input:** `label`, `agent_label`, `agent_session_id`, `agent_note` (all optional).
- **Output:** `batch_id`, `batch_status`, `finalization_required`, `finalizer_runtime`, `user_instruction`.

### 18. `novamira/gutenberg-add-pending-change` — Add Gutenberg Pending Change

Adds one replace-content target change to a draft batch (auto-creates a batch if omitted).
Static/native blocks are serialized in a hidden editor iframe by the Block Editor Queue
page. Refuses all-raw-HTML content unless `allow_raw_html=true`.

- **Input:** `block_spec` (required), `target_id`/`post_id` (one required), `batch_id`, `label`, `agent_label`, `agent_session_id`, `agent_note`, `target_type`/`post_type`, `operation` (`replace-content`), `allow_raw_html` (default false).
- **Output:** `batch_id`, `item_id`, `batch_status`, `target`, `finalization_required`, `finalizer_runtime`, `user_instruction`.

### 19. `novamira/gutenberg-enable-batch-finalization` — Enable Gutenberg Batch Finalization

Marks a draft batch ready after all changes are queued. An open Block Editor Queue page can
then pick it up automatically. Changes are not live until the batch reports finalized.

- **Input:** `batch_id` (required).
- **Output:** `batch_id`, `batch_status`, `finalization_required`, `finalization_url`, `finalizer_runtime`, `user_instruction`.

### 20. `novamira/gutenberg-get-pending-batch` — Get Gutenberg Pending Batch

Compact status, target summaries, validation errors, and runtime status for one batch
(no full block_spec payloads).

- **Input:** `batch_id` (required).

### 21. `novamira/gutenberg-list-pending-batches` — List Gutenberg Pending Batches

Lists compact queue state grouped by batch, for agent recovery.

- **Input:** `status` (optional filter: `draft`|`ready`|`running`|`finalized`|`failed`|`conflicted`|`canceled`|`stale`), `limit` (default 20).
- **Output:** `batches[]`, `finalizer_runtime`, `user_instruction`.

### 22. `novamira/gutenberg-delete-pending-batch` — Delete Gutenberg Pending Batch

Cancels a non-finalized batch and its items without touching target content.

- **Input:** `batch_id` (required).

### 23. `novamira/gutenberg-delete-pending-change` — Delete Gutenberg Pending Change

Cancels one pending item without touching target content (per-item recovery).

- **Input:** `item_id` (required).

### 24. `novamira/gutenberg-get-finalization-url` — Get Gutenberg Finalization URL

Returns the Block Editor Queue admin page URL for a ready/failed batch plus runtime status
and curl SSE/poll URLs.

- **Input:** `batch_id` (required).
- **Output:** `batch_id`, `batch_status`, `finalization_url`, `finalizer_runtime`, `user_instruction`.

### 25. `novamira/skill-get` — Get Skill

Loads a Novamira skill by slug; returns full SKILL.md content plus metadata.

- **Input:** `slug` (required).
- **Output:** `found`, `slug`, `name`, `description`, `content`, `enable_prompt`, `enable_agentic`, `source`.

### 26. `novamira/skill-write` — Write Skill

Creates or updates a Novamira user skill. `title` is the only identifier — sanitized
server-side into the slug.

- **Input:** `title`, `description`, `content` (required), `enable_prompt`, `enable_agentic`, `on_conflict` (`fail`|`replace`|`rename`).
- **Output:** `success`, `slug`, `action` (`created`|`updated`|`renamed`).

### 27. `novamira/skill-edit` — Edit Skill

Updates one or more fields on an existing user skill; untouched fields are preserved.

- **Input:** `slug` (required), `title`, `description`, `content`, `enable_prompt`, `enable_agentic`, `enabled`.
- **Output:** `success`, `slug`, `changed_fields[]`.

### 28. `novamira/skill-delete` — Delete Skill

Moves a user skill to trash; `permanent=true` deletes immediately.

- **Input:** `slug` (required), `permanent`.
- **Output:** `success`, `deleted`, `trashed`, `reason`.
