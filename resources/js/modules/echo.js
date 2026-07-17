import Echo from "laravel-echo";
import Pusher from "pusher-js";

const metaContent = (name) => document.querySelector(`meta[name="${name}"]`)?.content?.trim() || "";
const appKey = metaContent("pusher-app-key");
const cluster = metaContent("pusher-app-cluster") || "mt1";
const host = metaContent("pusher-app-host");
const scheme = metaContent("pusher-app-scheme").toLowerCase() || "https";
const port = Number.parseInt(metaContent("pusher-app-port"), 10) || (scheme === "https" ? 443 : 80);
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
    Pusher.logToConsole = true;
    window.Pusher = Pusher;
    const echoOptions = {
        broadcaster: "pusher",
        key: appKey,
        cluster,
        forceTLS: scheme === "https",
        auth: {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
        },
    };

    if (host) {
        Object.assign(echoOptions, {
            wsHost: host,
            wsPort: port,
            wssPort: port,
            enabledTransports: ["ws", "wss"],
        });
    }

    window.Echo = new Echo(echoOptions);

    window.Echo.connector.pusher.connection.bind("state_change", ({ current }) => {
        publishConnectionState(current);
    });
} else {
    publishConnectionState("unavailable");
}
