<div id="wpbooking-calendar"></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('wpbooking-calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: '<?php echo LANG_WPBOOKING; ?>',
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
            //editable: true,
            //selectable: true,
            nowIndicator: true,
            businessHours: true, // Hacer que el fin de semana salga en gris
            expandRows: true,
            dayMaxEvents: true, // allow "more" link when too many events
            events: '/wp-json/wpbooking/v1/events', // cambia esta ruta
        });

        calendar.render();
    });
</script>
<style>
    #wpbooking-calendar {
        width: 100%;
    }
    #wpbooking-calendar table {
        margin: 0 0 0 !important;
    }
    #wpbooking-calendar .fc-h-event .fc-event-title-container {
        text-align: center;
    }
</style>