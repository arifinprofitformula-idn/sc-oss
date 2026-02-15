# RBAC API

- Base: `/api/admin/rbac/*` (requires `auth:sanctum` and role `SUPER_ADMIN`)

## Roles
- `GET /api/admin/rbac/roles` — List roles
- `POST /api/admin/rbac/roles` — Create role
  - Body: `{ name, description?, permissions?: string[] }`
- `PUT /api/admin/rbac/roles/{role}` — Update role
  - Body: `{ description?, permissions?: string[] }`
- `DELETE /api/admin/rbac/roles/{role}` — Soft delete role

## Permissions
- `GET /api/admin/rbac/permissions` — List permissions
- `POST /api/admin/rbac/permissions` — Create permission
  - Body: `{ name, description? }`
- `PUT /api/admin/rbac/permissions/{permission}` — Update permission
- `DELETE /api/admin/rbac/permissions/{permission}` — Soft delete permission

## Notes
- All changes are versioned in `permission_versions` and `role_versions`.
- Admin activities logged in `admin_activity_logs`.
- Wildcard permissions enabled; `orders.*` matches `orders.view` etc.

