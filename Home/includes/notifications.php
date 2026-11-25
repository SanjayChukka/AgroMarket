<?php

function markNotificationsRead(mysqli $link, int $userId, array $actions): void
{
    if (isset($actions['mark_read'])) {
        $notificationId = (int) ($actions['notification_id'] ?? 0);
        $stmt           = $link->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($actions['mark_all_read'])) {
        $stmt = $link->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }
}

function unreadNotificationCount(mysqli $link, int $userId): int
{
    $stmt = $link->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int) $count;
}

function notificationsForUser(mysqli $link, int $userId): array
{
    $stmt = $link->prepare("SELECT notification_id, title, message, notification_type,
                                   related_order_id, is_read, created_at
                            FROM notifications WHERE user_id = ?
                            ORDER BY created_at DESC, notification_id DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $notifications;
}

function notificationBadgeCount(mysqli $link, int $userId): int
{
    $stmt = $link->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND bell_seen = 0 AND is_read = 0");
    $stmt->execute([$userId]);
    return (int) $stmt->get_result()->fetch_row()[0];
}

function acknowledgeNotificationVisit(mysqli $link, int $userId, array $notifications): void
{
    // A notification arriving after the list was loaded still deserves a badge.
    foreach (array_chunk(array_column($notifications, 'notification_id'), 200) as $ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt         = $link->prepare("UPDATE notifications SET bell_seen = 1 WHERE user_id = ? AND bell_seen = 0 AND notification_id IN ($placeholders)");
        $stmt->execute([$userId, ...$ids]);
        $stmt->close();
    }
}
