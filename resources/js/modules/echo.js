import Echo from "laravel-echo";
import Pusher from "pusher-js";

const metaContent = (name) => document.querySelector(`meta[name="${name}"]`)?.content?.trim() || "";
const appKey = metaContent("pusher-app-key");
const cluster = metaContent("pusher-app-cluster") || "mt1";
const csrfToken = metaContent("csrf-token");

const publishConnectionState = (state) => {
    const connected = state === "connected";

    document.querySelectorAll("[data-realtime-status]").forEach((element) => {
        element.classList.toggle("is-connected", connected);
        element.classList.toggle("is-disconnected", !connected);

        const label = element.querySelector("[data-realtime-status-label]");
        if (label) {
            label.textContent = connected ? "Live" : "Reconnecting";
        }
    });

    window.dispatchEvent(new CustomEvent(`realtime:${connected ? "connected" : "disconnected"}`, {
        detail: { state },
    }));
};

if (appKey) {
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: "pusher",
        key: appKey,
        cluster,
        forceTLS: true,
        auth: {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
        },
    });

    window.Echo.connector.pusher.connection.bind("state_change", ({ current }) => {
        publishConnectionState(current);
    });
} else {
    publishConnectionState("unavailable");
}
