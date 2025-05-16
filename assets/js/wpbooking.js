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
            url.searchParams.append('lang', WPBookingData.lang);

            fetch(url)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventReceive: function(info) {
            let data = {};
            if (info.draggedEl.dataset.event) {
                data = JSON.parse(info.draggedEl.dataset.event);
            }

            data.id = 'ev_' + Math.random().toString(36).substr(2, 9);
            data.start = info.event.startStr;

            fetch('/wp-json/wpbooking/v1/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': WPBookingData.nonce
                },
                body: JSON.stringify(data)
            })
            .then(showResponseAlert)
            .then(response => {
                if (response.success) {
                    info.event.remove(); // elimina el temporal
                    info.view.calendar.refetchEvents(); // recarga desde el backend
                }
            })
            .catch(console.error);
        },
        eventDrop: function (info) {
            const data = {
                id: info.event.id,
                type: info.event.extendedProps.type,
                title: info.event.title,
                color: info.event.backgroundColor,
                textColor: info.event.textColor,
                start: info.event.startStr,
                end: info.event.endStr,
                eventPostId: info.event.extendedProps.eventPostId
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
                type: info.event.extendedProps.type,
                title: info.event.title,
                color: info.event.backgroundColor,
                textColor: info.event.textColor,
                start: info.event.startStr,
                end: info.event.endStr,
                eventPostId: info.event.extendedProps.eventPostId
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
            if (!WPBookingData.is_admin) {
                const today = new Date();
                const eventDate = new Date(info.event.start);
                const isToday = eventDate.toDateString() === today.toDateString();

                if (isToday) {
                    info.jsEvent.preventDefault(); // Evita que siga el enlace
                    MicroModal.show('modal-wpbooking-exhausted');
                    return;
                }
                return; // Permitir que el enlace se abra si no es hoy
            }

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
    return response.json().then(data => {
        if (response.ok) {
            if (data.success === false) {
                alertify.error(data.message || WPBookingData.error_message);
                return Promise.reject(data.message);
            } else {
                alertify.success(data.message || 'Success');
                return data;
            }
        } else {
            alertify.error(data.message || WPBookingData.error_message);
            return Promise.reject(data.message);
        }
    }).catch(err => {
        alertify.error(WPBookingData.error_message);
        return Promise.reject(err);
    });
}