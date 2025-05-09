document.addEventListener('DOMContentLoaded', function() {
    const Draggable = FullCalendar.Draggable;

    const containerEl = document.getElementById('external-events');
    const calendarEl = document.getElementById('wpbooking-calendar');

    if (!calendarEl) return;

    if (containerEl) {
        // Inicializar eventos externos
        new Draggable(containerEl, {
            itemSelector: '.fc-event',
            eventData: function (el) {
                return {
                    title: el.innerText
                };
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
        //selectable: true,
        nowIndicator: true,
        businessHours: true, // Hacer que el fin de semana salga en gris
        expandRows: true,
        dayMaxEvents: true, // allow "more" link when too many events
        events: '/wp-json/wpbooking/v1/events', // cambia esta ruta
        drop: function (info) {
            //if (checkbox && checkbox.checked) {
            //    info.draggedEl.parentNode.removeChild(info.draggedEl);
            //}
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