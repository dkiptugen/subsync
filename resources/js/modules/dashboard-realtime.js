const debounce = (callback, delay) => {
    let timeoutId;

    return (...arguments_) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...arguments_), delay);
    };
};

const refreshDashboard = async () => {
    const dashboard = document.querySelector("#dashboard-realtime");
    if (!dashboard) {
        return;
    }

    const response = await fetch(dashboard.dataset.dashboardSnapshotUrl, {
        headers: {
            Accept: "text/html",
            "X-Requested-With": "XMLHttpRequest",
        },
    });

    if (!response.ok) {
        throw new Error("Unable to refresh the dashboard.");
    }

    const documentFragment = new DOMParser().parseFromString(await response.text(), "text/html");
    const refreshedDashboard = documentFragment.querySelector("#dashboard-realtime");

    if (refreshedDashboard) {
        dashboard.replaceWith(refreshedDashboard);
        window.feather?.replace();
    }
};

const dashboard = document.querySelector("#dashboard-realtime");

if (dashboard && window.Echo) {
    window.Echo.private("dashboard").listen(".dashboard.updated", debounce(() => {
        refreshDashboard().catch(() => undefined);
    }, 250));
}
