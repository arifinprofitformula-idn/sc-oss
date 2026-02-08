# Support Ticket Status Management System

## 1. Overview
This feature implements a comprehensive status management workflow for support tickets (chat issues) within the Silverchannel support system. It allows Admins to track, manage, and resolve issues effectively using a standardized set of statuses.

## 2. Status Definitions

The system uses 7 distinct statuses to track the lifecycle of a support ticket:

| Status | Code | Color | Description |
|---|---|---|---|
| **Open** | `open` | Blue | Tiket baru masuk, belum ditangani. (Default) |
| **Pending** | `pending` | Orange | Menunggu respon atau informasi tambahan dari pelanggan. |
| **On Progress** | `on_progress` | Indigo | Sedang ditangani oleh tim atau agent tertentu. |
| **Escalated** | `escalated` | Purple | Masalah dinaikkan ke level support yang lebih tinggi. |
| **Resolved** | `resolved` | Green | Solusi sudah diberikan, menunggu konfirmasi pelanggan. |
| **Closed** | `closed` | Gray | Tiket selesai dan ditutup. Silverchannel cannot send messages. |
| **Reopened** | `reopened` | Pink | Pelanggan merasa masalah belum benar-benar selesai, tiket dibuka kembali. |

## 3. Workflow & Logic

### Status Transitions
- **Initial Status**: All new conversations start as `open`.
- **Admin Action**: Admins can change status to any other status via the Admin Panel.
- **Closing a Ticket**: When changing status to `closed`, a **comment is mandatory** to document the resolution.
- **Reopening**: Closed tickets can be reopened (changed to `reopened` or `open`).

### Restrictions
- **Closed State**:
  - Silverchannel users **cannot** send new messages to a ticket marked as `closed`.
  - They will see a visual indicator that the ticket is closed.
- **Authorization**: Only users with `manage support` permission (Admin/Super Admin) can change statuses.

### Notifications/Visuals
- **Admin Panel**: Dropdown with color-coded badges.
- **Silverchannel Panel**: Status badge with description in the conversation list and chat header.

## 4. API Documentation

### Update Status
Updates the status of a support ticket (Order).

- **Endpoint**: `PATCH /api/chats/{order}/status`
- **Auth**: Required (Sanctum/Web Session), Admin role only.
- **Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`

#### Request Body
```json
{
  "status": "closed",
  "comment": "Issue resolved. Refund processed." // Required if status is 'closed'
}
```

- `status` (string, required): One of `open`, `pending`, `on_progress`, `escalated`, `resolved`, `closed`, `reopened`.
- `comment` (string, optional): Required if `status` is `closed`.

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Status updated successfully",
  "data": {
    "status": "closed",
    "updated_at": "2024-02-08T12:00:00.000000Z"
  }
}
```

#### Response (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "comment": [
      "The comment field is required when status is closed."
    ]
  }
}
```

## 5. Database Schema

### `orders` table
- `support_status` (enum/string): Stores the current status. Default 'open'.
- `support_closed_at` (timestamp): Nullable. Stores when the ticket was last closed.

### `support_status_histories` table
Tracks the history of status changes.
- `id` (PK)
- `order_id` (FK)
- `user_id` (FK): The admin who made the change.
- `old_status` (string)
- `new_status` (string)
- `comment` (text): Reason/note for the change.
- `created_at` (timestamp)

## 6. User Guide (Admin)

1. **Accessing Support Chats**: Go to **Admin Panel > Support / Chat Management**.
2. **Viewing a Ticket**: Click on a conversation from the list.
3. **Changing Status**:
   - Locate the status dropdown in the right-side context panel (under "Status Issue").
   - Click to open the dropdown.
   - Select the new status.
4. **Closing a Ticket**:
   - If you select **Closed**, a modal will appear.
   - You **MUST** enter a comment/reason for closing the ticket.
   - Click "Simpan Status" to confirm.
5. **Reopening**: Simply select a different status (e.g., Reopened) from the dropdown. No comment is strictly required for reopening, but the change is logged.

## 7. User Guide (Silverchannel)

- **Viewing Status**: You can see the status of your ticket in the conversation list and at the top of the chat window.
- **Closed Tickets**: If a ticket is Closed, you cannot reply. If you need further assistance on the same order, please contact support via other means or create a new inquiry if possible (depending on policy).
