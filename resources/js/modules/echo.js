import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const reverbAppKey = process.env.MIX_REVERB_APP_KEY;

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
}
