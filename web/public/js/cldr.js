import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';

document.addEventListener('DOMContentLoaded', function() {
    let calendarEl = document.getElementById('calendar');

    function timestampToDate(ts) {
        let d = new Date();
        d.setTime(ts);
        return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2) + " " + ('0' + d.getHours()).slice(-2) + ":" + ('0' + d.getMinutes()).slice(-2);
    }
    let result;

    if (typeof notes != "undefined") {

        try {
            result = JSON.parse(notes);
        } catch(e) {
            result = notes;
        }
    }

    let newArray = result.map(element => {
        let re = /\\n/gi;
        element.title = element.title.replace(re, `\n`);
        return element;
    });

    let calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin ],
        selectable: true,
        navLinks: true,
        locale: 'ru',
        events: newArray,
        eventLimit: true,
        eventLimitText: 'больше',
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

            let start = timestampToDate(Date.parse(info.date));
            let start1 = start.split(" ");

            $('#kiraxe_admincrmbundle_calendar_date_date').val(start1[0]);
            $('#kiraxe_admincrmbundle_calendar_date_time').val(start1[1]);
            $('#kiraxe_admincrmbundle_calendar_datecl_date').val("");
            $('#kiraxe_admincrmbundle_calendar_datecl_time').val("");
            $('#kiraxe_admincrmbundle_calendar_name').val("");
            $('#kiraxe_admincrmbundle_calendar_phone').val("");
            $('#kiraxe_admincrmbundle_calendar_text').val("");

            $( function() {

                $( "#dialog" ).dialog({
                    title: "Создать заметку",
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

            let start = timestampToDate(Date.parse(calEvent.event.start));
            let end = timestampToDate(Date.parse(calEvent.event.end));
            let title = calEvent.event.title.split(`\n`);

            let start1 = start.split(" ");
            let end1 = end.split(" ");

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
                    title: "Редактировать заметку",
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