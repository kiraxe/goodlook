$(document).ready(function(){

    $('form').on('change', 'select[id$="workers"]', function(){
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
    })
});