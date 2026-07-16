import { notify } from "./Notyf.js";

const notificationCenter = document.querySelector("#notification-center");

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || "";

const createNotificationItem = (notification) => {
    const link = document.createElement("a");
    link.href = notification.url;
    link.className = `list-group-item notification-item${notification.read ? "" : " is-unread"}`;

    const row = document.createElement("div");
    row.className = "row g-0 align-items-center";

    const iconColumn = document.createElement("div");
    iconColumn.className = "col-2";
    const icon = document.createElement("i");
    icon.className = `text-${notification.tone}`;
    icon.dataset.feather = notification.icon;
    icon.setAttribute("aria-hidden", "true");
    iconColumn.append(icon);

    const content = document.createElement("div");
    content.className = "col-10";

    const title = document.createElement("div");
    title.className = "text-dark fw-semibold";
    title.textContent = notification.title;

    const message = document.createElement("div");
    message.className = "text-muted small mt-1";
    message.textContent = notification.message;

    const timestamp = document.createElement("div");
    timestamp.className = "text-muted small mt-1";
    timestamp.textContent = notification.created_at || "Just now";

    content.append(title, message, timestamp);
    row.append(iconColumn, content);
    link.append(row);

    return link;
};

const renderNotifications = ({ notifications, unread_count: unreadCount }) => {
    const list = notificationCenter.querySelector("[data-notification-list]");
    const count = notificationCenter.querySelector("[data-notification-count]");
    const heading = notificationCenter.querySelector("[data-notification-heading]");
    const readAllButton = notificationCenter.querySelector("[data-notifications-read-all]");

    list.replaceChildren();

    if (notifications.length === 0) {
        const empty = document.createElement("div");
        empty.className = "notification-empty";
        empty.textContent = "No notifications yet";
        list.append(empty);
    } else {
        notifications.forEach((notification) => list.append(createNotificationItem(notification)));
    }

    count.textContent = unreadCount > 99 ? "99+" : String(unreadCount);
    count.classList.toggle("d-none", unreadCount === 0);
    heading.textContent = unreadCount === 1 ? "1 new notification" : `${unreadCount} new notifications`;
    readAllButton.disabled = unreadCount === 0;
    window.feather?.replace();
};

const loadNotifications = async () => {
    const response = await fetch(notificationCenter.dataset.indexUrl, {
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
    });

    if (!response.ok) {
        throw new Error("Unable to load notifications.");
    }

    renderNotifications(await response.json());
};

if (notificationCenter) {
    loadNotifications().catch(() => undefined);

    notificationCenter.querySelector("[data-notifications-read-all]").addEventListener("click", async () => {
        const response = await fetch(notificationCenter.dataset.readUrl, {
            method: "PATCH",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken(),
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (response.ok) {
            await loadNotifications();
        }
    });

    if (window.Echo) {
        window.Echo.private(`App.Models.User.${notificationCenter.dataset.userId}`).notification((notification) => {
            notify.success(notification.title || "New notification");
            loadNotifications().catch(() => undefined);
        });
    }
}
