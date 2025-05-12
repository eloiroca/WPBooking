document.addEventListener('DOMContentLoaded', function() {
    const Draggable = FullCalendar.Draggable;

    const containerEl = document.getElementById('external-events');
    const calendarEl = document.getElementById('wpbooking-calendar');

    if (!calendarEl) return;

    if (containerEl) {
        // Inicializar eventos externos
        new Draggable(containerEl, {
            itemSelector: '.fc-event',
            eventData: function(el) {
                const json = el.dataset.event;
                return json ? JSON.parse(json) : { title: el.innerText };
            }
        });
    }

    const calendarOptions = {
        initialView: 'dayGridMonth',
        locale: WPBookingData.lang,
        firstDay: 1,
        //slotMinTime: '08:00',
        //slotMaxTime: '20:00',
        headerToolbar: {
            left: '',
            center: 'title',
            right: 'today prev,next'
        },
        //initialDate: '2023-01-12',
        //navLinks: true, // can click day/week names to navigate views
        editable: WPBookingData.is_admin,
        eventResizableFromStart: WPBookingData.is_admin,
        //selectable: true,
        nowIndicator: true,
        businessHours: true, // Hacer que el fin de semana salga en gris
        expandRows: true,
        dayMaxEvents: true, // allow "more" link when too many events
        events: function(fetchInfo, successCallback, failureCallback) {
            const url = new URL('/wp-json/wpbooking/v1/events', window.location.origin);
            url.searchParams.append('start', fetchInfo.startStr);
            url.searchParams.append('end', fetchInfo.endStr);
            url.searchParams.append('is_admin', WPBookingData.is_admin ? '1' : '0');

            fetch(url)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        drop: function (info) {
            let data = {};
            if (info.draggedEl.dataset.event) {
                data = JSON.parse(info.draggedEl.dataset.event);
            }

            // Generar un ID único si no existe
            data.id = data.id || 'ev_' + Math.random().toString(36).substr(2, 9);
            data.start = info.dateStr;

            fetch('/wp-json/wpbooking/v1/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': WPBookingData.nonce
                },
                body: JSON.stringify(data)
            })
            .then(showResponseAlert)
            .catch((error) => {
                console.error(error); // Para logear cualquier error
            });
        },
        eventDrop: function (info) {
            const data = {
                id: info.event.id,
                type: info.event.extendedProps.type || '',
                title: info.event.title,
                color: info.event.backgroundColor,
                textColor: info.event.textColor,
                start: info.event.startStr,
                end: info.event.endStr
            };

            fetch('/wp-json/wpbooking/v1/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': WPBookingData.nonce
                },
                body: JSON.stringify(data)
            })
            .then(showResponseAlert)
            .catch(() => {});
        },
        eventResize: function (info) {
            const data = {
                id: info.event.id,
                type: info.event.extendedProps.type || '',
                title: info.event.title,
                color: info.event.backgroundColor,
                textColor: info.event.textColor,
                start: info.event.startStr,
                end: info.event.endStr
            };

            fetch('/wp-json/wpbooking/v1/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': WPBookingData.nonce
                },
                body: JSON.stringify(data)
            })
            .then(showResponseAlert)
            .catch(() => {});
        },
        eventClick: function(info) {
            if(!WPBookingData.is_admin) return;
            if (confirm("¿Quieres eliminar este evento?")) {
                fetch('/wp-json/wpbooking/v1/events/' + info.event.id, {
                    method: 'DELETE',
                    headers: {
                        'X-WP-Nonce': WPBookingData.nonce
                    }
                })
                .then(showResponseAlert)
                .then(() => info.event.remove())
                .catch(() => {});
            }
        }
    }

    if (!WPBookingData.is_admin) {
        calendarOptions.validRange = {
            start: WPBookingData.start_calendar,
            end: WPBookingData.end_calendar
        };
    }

    const calendar = new FullCalendar.Calendar(calendarEl, calendarOptions);
    calendar.render();
});

function showResponseAlert(response) {
    if (!response.ok) {
        alertify.error("Error al guardar los datos.");
        return Promise.reject(); // Detener el flujo aquí si hay un error de red
    }
    return response.json().then(data => {
        // Si el evento no fue guardado correctamente (ejemplo: fecha en pasado)
        if (data.success === false) {
            alertify.error(data.message || "Ocurrió un error inesperado.");
            return Promise.reject(data.message); // Detener el flujo aquí
        } else {
            alertify.success(data.message || "Evento guardado con éxito.");
        }
        return data;
    });
}