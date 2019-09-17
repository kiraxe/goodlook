import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    function timestampToDate(ts) {
        var d = new Date();
        d.setTime(ts);
        return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2) + " " + ('0' + d.getHours()).slice(-2) + ":" + ('0' + d.getMinutes()).slice(-2);
    }


    var result = JSON.parse(notes);

    var calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin ],
        selectable: true,
        navLinks: true,
        locale: 'ru',
        events: result,
        eventLimit: true,
        eventColor: "black",
        buttonText: {
            today: "Сегодня",
            month: "Месяц",
            week: "Неделя",
            day: "День"
        },
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        dateClick: function (info) {

            var start = timestampToDate(Date.parse(info.date));
            var start1 = start.split(" ");

            $('#kiraxe_admincrmbundle_calendar_date_date').val(start1[0]);
            $('#kiraxe_admincrmbundle_calendar_date_time').val(start1[1]);
            $('#kiraxe_admincrmbundle_calendar_datecl_date').val("");
            $('#kiraxe_admincrmbundle_calendar_datecl_time').val("");
            $('#kiraxe_admincrmbundle_calendar_name').val("");
            $('#kiraxe_admincrmbundle_calendar_phone').val("");
            $('#kiraxe_admincrmbundle_calendar_text').val("");

            $( function() {

                $( "#dialog" ).dialog({
                    autoOpen: false,
                    width: 330
                });

                $( "#dialog" ).dialog( "open" );

            } );

            if (!$('.form-button-new').hasClass('active')) {
                $('.form-button-new').addClass('active');
                $('.form-button-edit').removeClass('active');
            }
            //alert('clicked ' + info.dateStr);
        },
        select: function (info) {
            //alert('selected ' + info.startStr + ' to ' + info.endStr);
        },
        eventClick: function(calEvent, jsEvent, view){


            $.ajax({
                type: "POST",
                url: Routing.generate('calendar_ajaxdeletform'),
                data: {"calendarID" : calEvent.event.id},
                cache: false,
                dataType : "html",
                success: function(data){
                    $('.buttonDelete').remove();
                    $('.form-button-edit ul').append("<li class='buttonDelete'>"+ data +"</li>");
                }
            });

            var start = timestampToDate(Date.parse(calEvent.event.start));
            var end = timestampToDate(Date.parse(calEvent.event.end));
            var title = calEvent.event.title.split('\n');

            var start1 = start.split(" ");
            var end1 = end.split(" ");

            $('#kiraxe_admincrmbundle_calendar_id').val(calEvent.event.id);
            $('#kiraxe_admincrmbundle_calendar_name').val(title[1]);
            $('#kiraxe_admincrmbundle_calendar_phone').val(title[2]);
            $('#kiraxe_admincrmbundle_calendar_text').val(title[3]);
            $('#kiraxe_admincrmbundle_calendar_date_date').val(start1[0]);
            $('#kiraxe_admincrmbundle_calendar_date_time').val(start1[1]);
            $('#kiraxe_admincrmbundle_calendar_datecl_date').val(end1[0]);
            $('#kiraxe_admincrmbundle_calendar_datecl_time').val(end1[1]);

            $( function() {

                $( "#dialog" ).dialog({
                    autoOpen: false,
                    width: 330
                });

                $( "#dialog" ).dialog( "open" );

            } );

            if (!$('.form-button-edit').hasClass('active')) {
                $('.form-button-edit').addClass('active');
                $('.form-button-new').removeClass('active');
            }
        }
    });

    calendar.render();
});