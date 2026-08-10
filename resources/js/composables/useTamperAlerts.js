import { ref } from 'vue';
import echo from '../echo';

const alerts = ref([]);
let connected = false;

function connect() {
    if (connected) return;

    connected = true;

    echo.channel('audit.tamper').listen('.audit-log.tampered', (payload) => {
        const log = payload.audit_log ?? payload;

        alerts.value.unshift({
            id: log.id ?? crypto.randomUUID(),
            title: log.title ?? 'Audit log',
            file_hash: log.file_hash ?? null,
            integrity_status: log.integrity_status ?? 'tampered',
            received_at: Date.now(),
        });

        if (alerts.value.length > 20) {
            alerts.value.pop();
        }
    });
}

export function useTamperAlerts() {
    connect();

    return { alerts };
}
