import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const reverbAppKey = process.env.MIX_REVERB_APP_KEY;

function updateTopNotificationUi(payload) {
    const countElement = document.querySelector('[data-top-notification-count]');
    const headerElement = document.querySelector('[data-top-notification-header]');
    const listElement = document.querySelector('[data-top-notification-list]');
    const emptyStateElement = document.querySelector('[data-top-notification-empty-state]');

    if (!countElement || !headerElement || !listElement) {
        return;
    }

    const nextCount = Number.isInteger(payload.count)
        ? payload.count
        : (parseInt(countElement.textContent ?? '0', 10) || 0) + 1;

    countElement.textContent = String(nextCount);
    headerElement.textContent = nextCount > 0
        ? `${nextCount} New Notification${nextCount === 1 ? '' : 's'}`
        : 'No New Notifications';

    emptyStateElement?.remove();

    const itemElement = document.createElement(payload.url ? 'a' : 'div');
    itemElement.className = 'list-group-item';
    itemElement.setAttribute('data-top-notification-item', '');

    if (payload.url) {
        itemElement.setAttribute('href', payload.url);
    }

    itemElement.innerHTML = `
        <div class="row g-0 align-items-center">
            <div class="col-2">
                <i class="${payload.iconClass ?? 'text-primary'}" data-feather="${payload.icon ?? 'bell'}"></i>
            </div>
            <div class="col-10">
                <div class="text-dark">${payload.title ?? 'Notification'}</div>
                <div class="text-muted small mt-1">${payload.message ?? ''}</div>
                <div class="text-muted small mt-1">${payload.meta ?? 'Just now'}</div>
            </div>
        </div>
    `;

    listElement.prepend(itemElement);

    const existingItems = listElement.querySelectorAll('[data-top-notification-item]');

    Array.from(existingItems).slice(6).forEach((element) => {
        element.remove();
    });

    if (window.feather) {
        window.feather.replace();
    }

    if (window.notyf) {
        window.notyf.open({
            type: 'success',
            message: payload.message ? `${payload.title}: ${payload.message}` : (payload.title ?? 'Notification received'),
            duration: 5000,
        });
    }
}

if (reverbAppKey) {
    const reverbScheme = process.env.MIX_REVERB_SCHEME ?? window.location.protocol.replace(':', '');
    const reverbHost = process.env.MIX_REVERB_HOST ?? window.location.hostname;
    const reverbPort = Number(process.env.MIX_REVERB_PORT ?? 8080);

    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbAppKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    const authenticatedUserId = window.appConfig?.authenticatedUserId;
    const authenticatedUserRoles = Array.isArray(window.appConfig?.authenticatedUserRoles)
        ? window.appConfig.authenticatedUserRoles
        : [];

    const subscribedChannels = new Set();
    const subscribeToChannel = (channelName) => {
        if (!channelName || subscribedChannels.has(channelName)) {
            return;
        }

        subscribedChannels.add(channelName);

        window.Echo.private(channelName)
            .listen('.top-navigation.notification', (event) => {
                if (event?.notification) {
                    updateTopNotificationUi(event.notification);
                }
            });
    };

    if (authenticatedUserId) {
        subscribeToChannel(`top-navigation.user.${authenticatedUserId}`);
    }

    authenticatedUserRoles.forEach((roleName) => {
        subscribeToChannel(`top-navigation.role.${roleName}`);
    });
}
