const storageKey = "app-theme";
const rootElement = document.documentElement;
const themeToggleSelector = "[data-theme-toggle]";

const colorTokenKeys = [
  "primary",
  "secondary",
  "success",
  "info",
  "warning",
  "danger",
  "surface",
  "surface-alt",
  "surface-strong",
  "border",
  "text-base",
  "text-muted",
  "icon",
  "gray-100",
  "gray-200",
  "gray-300",
  "gray-400",
  "gray-500",
  "gray-600",
  "gray-700",
  "gray-800",
  "gray-900",
  "white",
  "black"
];

const chartThemeKeyMap = {
  primary: "primary",
  secondary: "secondary",
  success: "success",
  info: "info",
  warning: "warning",
  danger: "danger",
  white: "white",
  black: "black",
  "gray-100": "gray-100",
  "gray-200": "gray-200",
  "gray-300": "gray-300",
  "gray-400": "gray-400",
  "gray-500": "gray-500",
  "gray-600": "text-muted",
  "gray-700": "text-base",
  "gray-800": "surface-strong",
  "gray-900": "body-bg"
};

function getPreferredTheme() {
  const storedTheme = localStorage.getItem(storageKey);

  if(storedTheme === "dark" || storedTheme === "light") {
    return storedTheme;
  }

  return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

function readCssVariable(name) {
  return getComputedStyle(rootElement).getPropertyValue(name).trim();
}

function buildWindowTheme() {
  const theme = {};

  colorTokenKeys.forEach((key) => {
    theme[key] = readCssVariable(`--${key}`);
  });

  Object.entries(chartThemeKeyMap).forEach(([themeKey, cssKey]) => {
    theme[themeKey] = readCssVariable(`--${cssKey}`);
  });

  window.theme = theme;

  if(window.Chart?.defaults?.global) {
    window.Chart.defaults.global.defaultFontColor = theme["gray-600"];
  }
}

function updateToggleState(theme) {
  document.querySelectorAll(themeToggleSelector).forEach((toggleButton) => {
    const nextTheme = theme === "dark" ? "light" : "dark";
    const label = theme === "dark" ? "Dark" : "Light";

    toggleButton.setAttribute("aria-label", `Switch to ${nextTheme} mode`);
    toggleButton.setAttribute("title", `Switch to ${nextTheme} mode`);
    toggleButton.dataset.currentTheme = theme;

    const labelElement = toggleButton.querySelector(".theme-toggle-label");

    if(labelElement) {
      labelElement.textContent = label;
    }
  });
}

function applyTheme(theme, persist = true) {
  rootElement.setAttribute("data-theme", theme);

  if(persist) {
    localStorage.setItem(storageKey, theme);
  }

  updateToggleState(theme);
  buildWindowTheme();

  document.dispatchEvent(new CustomEvent("app-theme:changed", {
    detail: { theme }
  }));
}

function initializeThemeToggle() {
  const currentTheme = rootElement.getAttribute("data-theme") || getPreferredTheme();

  applyTheme(currentTheme, false);

  document.querySelectorAll(themeToggleSelector).forEach((toggleButton) => {
    toggleButton.addEventListener("click", () => {
      const activeTheme = rootElement.getAttribute("data-theme") || "light";
      const nextTheme = activeTheme === "dark" ? "light" : "dark";

      applyTheme(nextTheme);
    });
  });

  window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (event) => {
    if(localStorage.getItem(storageKey)) {
      return;
    }

    applyTheme(event.matches ? "dark" : "light", false);
  });
}

initializeThemeToggle();
