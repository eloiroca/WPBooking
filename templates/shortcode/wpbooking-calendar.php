<div id="wpbooking-calendar"></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('wpbooking-calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: '<?php echo LANG_WPBOOKING; ?>',
            firstDay: 1,
            //events: '/ruta-api-o-json-de-eventos', // cambia esta ruta
            events: [
                {
                    title: 'Reserva 1',
                    start: '2025-05-10',
                    end: '2025-05-10'
                },
                {
                    title: 'Reserva 2',
                    start: '2025-05-15',
                    end: '2025-05-19'
                }
            ],
            expandRows: true,
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
</style>