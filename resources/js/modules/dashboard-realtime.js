const chartInstances = new WeakMap();
const numberFormatter = new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 });
const compactNumberFormatter = new Intl.NumberFormat(undefined, {
    notation: "compact",
    maximumFractionDigits: 1,
});

const debounce = (callback, delay) => {
    let timeoutId;

    return (...arguments_) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...arguments_), delay);
    };
};

const parseChartData = (canvas, attribute) => {
    try {
        const value = JSON.parse(canvas.dataset[attribute] ?? "[]");

        return Array.isArray(value) ? value : [];
    } catch {
        return [];
    }
};

const chartDefinition = (type) => {
    if (type === "churn-rate") {
        return {
            label: "Churn rate",
            borderColor: "#f0ad4e",
            backgroundColor: "rgba(240, 173, 78, 0.08)",
            borderDash: [6, 4],
            pointStyle: "rectRot",
            fill: false,
            valueLabel: (value) => `${numberFormatter.format(value)}%`,
            axisLabel: (value) => `${numberFormatter.format(value)}%`,
        };
    }

    return {
        label: "Cumulative paid revenue",
        borderColor: "#3b7ddd",
        backgroundColor: "rgba(59, 125, 221, 0.1)",
        borderDash: [],
        pointStyle: "circle",
        fill: true,
        valueLabel: (value) => numberFormatter.format(value),
        axisLabel: (value) => compactNumberFormatter.format(value),
    };
};

const renderDashboardCharts = (root = document) => {
    if (!window.Chart) {
        return;
    }

    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    root.querySelectorAll("[data-dashboard-chart]").forEach((canvas) => {
        const labels = parseChartData(canvas, "chartLabels");
        const values = parseChartData(canvas, "chartValues").map(Number);

        if (labels.length === 0 || labels.length !== values.length) {
            return;
        }

        chartInstances.get(canvas)?.destroy();

        const definition = chartDefinition(canvas.dataset.dashboardChart);
        const chart = new window.Chart(canvas.getContext("2d"), {
            type: "line",
            data: {
                labels,
                datasets: [{
                    label: definition.label,
                    data: values,
                    borderColor: definition.borderColor,
                    backgroundColor: definition.backgroundColor,
                    borderDash: definition.borderDash,
                    borderWidth: 2,
                    fill: definition.fill,
                    lineTension: 0.2,
                    pointBackgroundColor: definition.borderColor,
                    pointBorderColor: "#ffffff",
                    pointBorderWidth: 2,
                    pointHoverRadius: 5,
                    pointRadius: 3,
                    pointStyle: definition.pointStyle,
                }],
            },
            options: {
                animation: {
                    duration: reduceMotion ? 0 : 200,
                },
                hover: {
                    animationDuration: reduceMotion ? 0 : 200,
                },
                responsiveAnimationDuration: reduceMotion ? 0 : 200,
                maintainAspectRatio: false,
                responsive: true,
                legend: {
                    display: true,
                    position: "bottom",
                    labels: {
                        boxWidth: 12,
                        padding: 16,
                    },
                },
                tooltips: {
                    callbacks: {
                        label: (tooltipItem) => `${definition.label}: ${definition.valueLabel(Number(tooltipItem.yLabel))}`,
                    },
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                        },
                        ticks: {
                            autoSkip: true,
                            maxRotation: 0,
                            maxTicksLimit: 6,
                        },
                    }],
                    yAxes: [{
                        gridLines: {
                            color: "rgba(108, 117, 125, 0.14)",
                            drawBorder: false,
                        },
                        ticks: {
                            beginAtZero: true,
                            callback: definition.axisLabel,
                            maxTicksLimit: 6,
                        },
                    }],
                },
            },
        });

        chartInstances.set(canvas, chart);
    });
};

const destroyDashboardCharts = (root) => {
    root.querySelectorAll("[data-dashboard-chart]").forEach((canvas) => {
        chartInstances.get(canvas)?.destroy();
        chartInstances.delete(canvas);
    });
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
        destroyDashboardCharts(dashboard);
        dashboard.replaceWith(refreshedDashboard);
        renderDashboardCharts(refreshedDashboard);
        window.feather?.replace();
    }
};

const dashboard = document.querySelector("#dashboard-realtime");

if (dashboard) {
    renderDashboardCharts(dashboard);
}

if (dashboard && window.Echo) {
    window.Echo.private("dashboard").listen(".dashboard.updated", debounce(() => {
        refreshDashboard().catch(() => undefined);
    }, 250));
}
