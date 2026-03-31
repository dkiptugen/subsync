import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

window.notyf = new Notyf();

document.addEventListener('DOMContentLoaded', () => {
    const flashNotification = window.appConfig?.flashNotification;

    if (!flashNotification?.message) {
        return;
    }

    const notificationType = flashNotification.type === 'error' ? 'error' : 'success';

    window.notyf.open({
        type: notificationType,
        message: flashNotification.message,
        duration: 5000,
    });
});
