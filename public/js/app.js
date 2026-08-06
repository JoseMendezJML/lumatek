(() => {
    const body = document.body;
    const menuButton = document.querySelector('[data-menu-toggle]');

    menuButton?.addEventListener('click', () => {
        body.classList.toggle('sidebar-open');
    });

    document.querySelectorAll('[data-flash-close]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.alert-flash')?.remove());
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || '¿Deseas continuar?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-range-target]').forEach((range) => {
        const target = document.getElementById(range.dataset.rangeTarget);
        if (!target) return;

        range.addEventListener('input', () => {
            target.value = range.value;
        });

        target.addEventListener('input', () => {
            range.value = target.value;
        });
    });

    document.querySelectorAll('[data-print]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });

    const dashboard = document.querySelector('[data-telemetry-dashboard]');
    if (!dashboard) return;

    const url = dashboard.dataset.telemetryUrl;
    const pollSeconds = Number(dashboard.dataset.pollSeconds || 5);
    let lastReadingId = Number(dashboard.dataset.readingId || 0);

    const statusLabels = {
        normal: 'Normal',
        warning: 'Advertencia',
        critical: 'Crítico',
        unknown: 'Sin datos',
    };

    const sourceLabels = {
        simulation_auto: 'Simulación automática',
        simulation_manual: 'Simulación manual',
        simulation_scenario: 'Escenario simulado',
        iot: 'IoT',
    };

    const formatValue = (key, value) => {
        if (key === 'temperature') return `${Number(value).toFixed(1)} °C`;
        if (['soil_humidity', 'ambient_humidity', 'water_level'].includes(key)) return `${Number(value).toFixed(0)} %`;
        if (key === 'luminosity') return `${Number(value).toFixed(0)} lux`;
        return value;
    };

    const applyStatus = (element, status) => {
        if (!element) return;
        element.classList.remove('status-normal', 'status-warning', 'status-critical', 'status-unknown');
        element.classList.add(`status-${status}`);
        element.textContent = statusLabels[status] || status;
    };

    const refresh = async () => {
        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) return;
            const payload = await response.json();
            const reading = payload.reading;

            ['temperature', 'soil_humidity', 'ambient_humidity', 'luminosity', 'water_level'].forEach((key) => {
                document.querySelectorAll(`[data-reading="${key}"]`).forEach((element) => {
                    element.textContent = formatValue(key, reading[key]);
                });

                document.querySelectorAll(`[data-status="${key}"]`).forEach((element) => {
                    applyStatus(element, payload.statuses[key]);
                });

                document.querySelectorAll(`[data-progress="${key}"]`).forEach((element) => {
                    const numeric = Math.max(0, Math.min(100, Number(reading[key])));
                    element.style.width = `${numeric}%`;
                });
            });

            document.querySelectorAll('[data-reading="irrigation_status"]').forEach((element) => {
                element.textContent = reading.irrigation_status === 'active' ? 'Regando' : (
                    reading.irrigation_status === 'fault' ? 'Falla' : 'Inactivo'
                );
            });

            document.querySelectorAll('[data-reading="device_status"]').forEach((element) => {
                element.textContent = reading.device_status === 'connected' ? 'Conectado' : 'Desconectado';
            });

            document.querySelectorAll('[data-reading-source]').forEach((element) => {
                element.textContent = sourceLabels[reading.source] || reading.source;
            });

            document.querySelectorAll('[data-reading-time]').forEach((element) => {
                element.textContent = new Date(reading.recorded_at).toLocaleString('es-MX');
            });

            document.querySelectorAll('[data-alert-count]').forEach((element) => {
                element.textContent = payload.active_alerts;
                element.hidden = payload.active_alerts < 1;
            });

            document.querySelectorAll('[data-overall-state]').forEach((element) => {
                element.dataset.overallState = payload.statuses.overall;
                element.classList.remove('normal', 'warning', 'critical');
                element.classList.add(payload.statuses.overall);
            });

            document.querySelectorAll('[data-overall-title]').forEach((element) => {
                element.textContent = payload.statuses.overall === 'normal'
                    ? 'Todo está funcionando correctamente'
                    : payload.statuses.overall === 'warning'
                        ? 'Se detectaron condiciones fuera del rango'
                        : 'Se requiere atención inmediata';
            });

            if (reading.id !== lastReadingId) {
                lastReadingId = reading.id;
                dashboard.dataset.readingId = reading.id;
            }
        } catch (error) {
            console.warn('No fue posible actualizar la telemetría.', error);
        }
    };

    window.setInterval(refresh, Math.max(5, pollSeconds) * 1000);
})();
