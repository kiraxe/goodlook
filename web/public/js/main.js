"use strict";
$(document).ready(function(){
    $('.add-another-collection-widget').click(function (e) {
        var list = $($(this).attr('data-list'));
        // Try to find the counter of the list or use the length of the list
        var counter = list.data('widget-counter') | list.children().length;

        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);

        // create a new list element and add it to the list
        var newElem = $(list.attr('data-widget-tags')).html(newWidget);
        var remove_tag = '<a href="#" class="remove-tag btn btn-danger">Удалить</a>';
        newElem.append(remove_tag);
        newElem.appendTo(list);
    });
    $('body').on( 'click', '.remove-tag', function(e) {
        e.preventDefault();

        $(this).parent().remove();

        return false;
    });

    $('.nav-link-collapse').on('click', function() {
        $('.nav-link-collapse').not(this).removeClass('nav-link-show');
        $(this).toggleClass('nav-link-show');
    });

    $('.nav-link-collapse').on('click', function(){
        if ($(this).find('.fa-angle-right').hasClass('fa-angle-right-active')) {
            $(this).find('.fa-angle-right').removeClass('fa-angle-right-active');
        } else {
            $(this).find('.fa-angle-right').addClass('fa-angle-right-active');
        }
    });



    $('#bars').click(function(){
        $('#navbarCollapse').slideToggle(300, function(){
            if ($(this).css('display') === 'none') {
                $(this).removeAttr('style');
            }
        });
    });

    /*$("select[id$='servicesparent']").each(function () {
        $(this).attr('disabled','disabled');
    });


    $('select[id$="services"]').each(function(){
        $(this).attr('disabled','disabled');
    });

    $('select[id$="materials"]').each(function(){
        $(this).attr('disabled','disabled');
    });*/

    /*$('.btn-info').click(function(){

        $("select[id$='servicesparent']").each(function (step) {
            if (step != 0) {
                $(this).attr('disabled', 'disabled');
            }
        });


        $('select[id$="services"]').each(function(step){
            if (step != 0) {
                $(this).attr('disabled', 'disabled');
            }
        });

        $('select[id$="materials"]').each(function(step){
            if (step != 0) {
                $(this).attr('disabled', 'disabled');
            }
        });
    });*/

    /*$('form').on('change', 'select[id$="workers"]', function(){
       var id = $(this).val();
       var url = Routing.generate('orders_ajax');
       var sibling = $(this);
       var parent = sibling.parent().parent();
       $.ajax({
           type:"POST",
           url: url,
           data: {'idWorker': id},
           cache: false,
           dataType: 'json',
           success: function(data) {
               if (data) {
                   var services = data['parent'];
               } else {
                   parent.find('select[id$="servicesparent"]').attr('disabled','disabled');

                   parent.find('select[id$="services"] option').each(function(){
                       if ($(this).val() != "" && $(this).prop('selected') === false) {
                           $(this).remove();
                       }
                   });

                   parent.find('select[id$="materials"] option').each(function(){
                       if ($(this).val() != "" && $(this).prop('selected') === false) {
                           $(this).remove();
                       }
                   });

                   parent.find('select[id$="services"]').attr('disabled','disabled');
                   parent.find('select[id$="materials"]').attr('disabled','disabled');
               }
               var servicesSel = [];

               parent.find('select[id$="servicesparent"] option').each(function() {
                   if (services){

                       if ($(this).val() != "" && $(this).prop('selected') === false) {
                           $(this).remove();
                       }
                       if ($(this).prop('selected') === true) {
                           servicesSel = $(this).val();
                       }
                   } else {
                       if ($(this).val() != "") {
                           $(this).remove();
                       }
                   }
               });


               if (services) {
                   parent.find('select[id$="servicesparent"]').removeAttr('disabled');
                   services.forEach(function (item, i) {
                       if (services[i].id != servicesSel) {
                           parent.find('select[id$="servicesparent"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                       }
                   });
               }
           },
           error: function(jqXHR, textStatus, errorThrown){
               console.log(textStatus);
           }
       })
    });

    $('form').on('change', 'select[id$="servicesparent"]', function(){
        var id = $(this).val();
        var url = Routing.generate('orders_ajax');
        var sibling = $(this);
        var parent = sibling.parent().parent();
        $.ajax({
            type: "POST",
            url: url,
            data: {'idSelect': id},
            cache: false,
            dataType: 'json',
            success: function(data) {
                if (data){
                    var services = data['services'];
                    var materials = data['materials'];
                } else {
                    parent.find('select[id$="services"]').attr('disabled','disabled');
                    parent.find('select[id$="materials"]').attr('disabled','disabled');
                }

                var servicesSel = [];
                var materialsSel = [];

                parent.find('select[id$="services"] option').each(function(){
                    if ($(this).val() != "" && $(this).prop('selected') === false) {
                        $(this).remove();
                    }
                    if ($(this).prop('selected') === true) {
                        servicesSel = $(this).val();
                    }

                });

                parent.find('select[id$="materials"] option').each(function(){
                    if ($(this).val() != "" && $(this).prop('selected') === false) {
                        $(this).remove();
                    }
                    if ($(this).prop('selected') === true) {
                        materialsSel = $(this).val();
                    }
                });


                if (services) {
                    parent.find('select[id$="services"]').removeAttr('disabled');
                    services.forEach(function (item, i) {
                        if (services[i].id != servicesSel) {
                            parent.find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                        }
                    });
                }

                if (materials) {
                    parent.find('select[id$="materials"]').removeAttr('disabled');
                    materials.forEach(function (item, i) {
                        if (materials[i].id != materialsSel) {
                            parent.find('select[id$="materials"]').append('<option value="' + materials[i].id + '">' + materials[i].name + '</option>')
                        }
                    });
                }

            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log(textStatus);
            }
        });
    })

    $('form').on('change', '#kiraxe_admincrmbundle_orders_brandId', function(){
        var id = $(this).val();
        var url = Routing.generate('orders_ajaxmodel');
        $.ajax({
            type: "POST",
            url: url,
            data: {'idSelect': id},
            cache: false,
            dataType: 'json',
            success: function(data){

                var brand = data['brand'];
                var brandSel = [];

                $('#kiraxe_admincrmbundle_orders_carId').find('option').each(function(){

                    if ($(this).val() != "" && $(this).prop('selected') === false) {
                        $(this).remove();
                    }
                    if ($(this).prop('selected') === true) {
                        brandSel = $(this).val();
                    }
                });

                if (brand) {
                    brand.forEach(function (item, i) {
                        if (brand[i].id != brandSel) {
                            $('#kiraxe_admincrmbundle_orders_carId').append('<option value="' + brand[i].id + '">' + brand[i].name + '</option>')
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log(textStatus);
            }
        });
    })*/

    //var url = Routing.generate('workers_edit');

    /*$('form').on('change', '#kiraxe_admincrmbundle_workers_typeworkers', function(){
        var reading = $(this).val();
        var url = Routing.generate('workers_edit', {id: 8});
        $.ajax({
            type: "POST",
            url: url,
            success: function(data){
                console.log(data);
                if (reading == 1) {
                    var formAjax = $(data).find('#manager-percent');
                    var formHtml = formAjax.html();
                    var form = $("#worker-percent");
                    form.removeAttr('id');
                    form.attr('id', 'manager-percent')
                } else if (reading == 0) {
                    var formAjax = $(data).find('#worker-percent');
                    var formHtml = formAjax.html();
                    var form = $("#manager-percent");
                    form.removeAttr('id');
                    form.attr('id', 'worker-percent')
                }
                form.html(formHtml);



            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log(textStatus);
            }
        });
    });*/

    /*$('form').on('change', '#kiraxe_admincrmbundle_workers_typeworkers', function(){
        $('.btn-success').trigger('click');
    })*/




    $('#kiraxe_admincrmbundle_orders_number').on('keypress', function() {
        var that = this;
        setTimeout(function() {
            var res = /[^0-9а-яА-ЯїЇєЄіІёЁ ]/g.exec(that.value);
            that.value = that.value.replace(res, '');
        }, 0);
    });

    $(function(){
        //2. Получить элемент, к которому необходимо добавить маску
        $("#kiraxe_admincrmbundle_orders_phone").mask("+7(999) 999-9999");
        $("#form_tel").mask("+7(999) 999-9999");
    });


    $('form').on('change', '#kiraxe_admincrmbundle_workers_typeworkers', function(){
        changeManagerType($(this).val());
    });

    var changeManagerType = value => {
       if (value == 1) {
           $('#worker-percent').hide();
           $('#manager-percent').show();

           $('#worker-percent').find('input').each(function(){
              $(this).prop('disabled', true);
           });
           $('#worker-percent').find('select').each(function(){
               $(this).prop('disabled', true);
           });

           $('#manager-percent').find('input').each(function(){
               $(this).prop('disabled', false);
           });
       } else if(value == 0) {

           $('#worker-percent').show();
           $('#manager-percent').hide();

           $('#worker-percent').find('input').each(function(){
               $(this).prop('disabled', false);
           });
           $('#worker-percent').find('select').each(function(){
               $(this).prop('disabled', false);
           });

           $('#manager-percent').find('input').each(function(){
               $(this).prop('disabled', true);
           });
       }
    };

    var valueSelectTypeWorker = $('#kiraxe_admincrmbundle_workers_typeworkers').val();

    changeManagerType(valueSelectTypeWorker);

    /*$( function() {
        var dateFormat = "yy-mm-dd",
            from = $( "#kiraxe_admincrmbundle_calendar_date" )
                .datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    dateFormat: dateFormat,
                    numberOfMonths: 1
                })
                .on( "change", function() {
                    to.datepicker( "option", "minDate", getDate( this ) );
                }),
            to = $( "#kiraxe_admincrmbundle_calendar_datecl" )
                .datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    dateFormat: dateFormat,
                    numberOfMonths: 1
                })
                .on( "change", function() {
                    from.datepicker( "option", "maxDate", getDate( this ) );
                });

        function getDate( element ) {
            var date;
            try {
                date = $.datepicker.parseDate( dateFormat, element.value );
            } catch( error ) {
                date = null;
            }

            return date;
        }
    } );*/

    $( function() {
        var dateFormat = "yy-mm-dd",
            from = $( "#form_dateFrom" )
                .datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    dateFormat: dateFormat,
                    numberOfMonths: 1
                })
                .on( "change", function() {
                    to.datepicker( "option", "minDate", getDate( this ) );
                }),
            to = $( "#form_dateTo" )
                .datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    dateFormat: dateFormat,
                    numberOfMonths: 1
                })
                .on( "change", function() {
                    from.datepicker( "option", "maxDate", getDate( this ) );
                });

        function getDate( element ) {
            var date;
            try {
                date = $.datepicker.parseDate( dateFormat, element.value );
            } catch( error ) {
                date = null;
            }

            return date;
        }
    } );

    function checkContentPrint(val) {

        var element = document.querySelectorAll(".workorder");
        if (val == 1) {
            element[0].classList.add('active');
            if (element[1].classList.contains('active')) {
                element[1].classList.remove('active');
            }
        } else if (val == 2) {
            element[1].classList.add('active');
            if (element[0].classList.contains('active')) {
                element[0].classList.remove('active');
            }
        }

        window.print();

        return false;
    }

    $('a[data-toggl]').on('click', function(){
        checkContentPrint($(this).attr('data-toggl'));
    });

    $( function() {
        $( "#dialog" ).dialog({
            title: "Создать заметку",
            autoOpen: false,
            width: 330
        });

        $( "#opener" ).on( "click", function() {
            $( "#dialog" ).dialog( "open" );
        });
    } );

    $('form[name="kiraxe_admincrmbundle_orders"]').submit(function(){
        if ($('#kiraxe_admincrmbundle_orders_close').prop('checked')) {
            var dateClose = $('#kiraxe_admincrmbundle_orders_dateClose_date').val();
            var timeClose = $('#kiraxe_admincrmbundle_orders_dateClose_time').val();
            var datePayment = $('#kiraxe_admincrmbundle_orders_datePayment_date').val();
            var timePayment = $('#kiraxe_admincrmbundle_orders_datePayment_time').val();

            if (dateClose == "" || timeClose == "" || datePayment == "" || timePayment == "") {
                alert('Запоните поля "Дата и время закрытия" и "Дата и время оплаты"');
                return false;
            }
        }

    })

});