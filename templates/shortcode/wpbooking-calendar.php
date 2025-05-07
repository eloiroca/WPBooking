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
            //dayMaxEvents: true, // allow "more" link when too many events
            expandRows: true,
            //events: '/ruta-api-o-json-de-eventos', // cambia esta ruta
            events: [
                {
                    title: 'BÀSICA',
                    start: '2025-05-10',
                    end: '2025-05-10',
                    url: 'https://example.com/',
                    color: '#bad7b4',
                    textColor: '#000000'
                },
                {
                    title: 'PRÈMIUM',
                    start: '2025-05-10',
                    end: '2025-05-10',
                    url: 'https://example.com/',
                    color: '#fff6c9',
                    textColor: '#000000'
                },
                {
                    title: 'PRÈMIUM',
                    start: '2025-05-15',
                    end: '2025-05-19',
                    url: 'https://example.com/',
                    color: '#fff6c9',
                    textColor: '#000000'
                },
                {
                    title: 'EXHAURIDES',
                    start: '2025-05-20',
                    end: '2023-05-20',
                    overlap: false,
                    display: 'background',
                    color: '#e08e88',
                    textColor: '#ffffff'
                },
                {
                    title: 'EXHAURIDES',
                    start: '2025-05-21',
                    end: '2025-05-24',
                    color: '#e08e88', // sin fondo
                    textColor: '#cb2503'  // color del título
                },
                {
                    title: 'EXTRAORDINARIA',
                    start: '2025-05-25',
                    end: '2025-05-28',
                    color: '#bbdffb', // sin fondo
                    textColor: '#000000'  // color del título
                }
            ],
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