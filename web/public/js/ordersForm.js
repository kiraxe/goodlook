$(document).ready(function(){

    $('form').on('change', 'select[id$="workers"]', function(){
        var id = [$(this).val()];
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
                console.log(textStatus + errorThrown);
            }
        })
    });


        $(function () {
            var id = [];
            var sibling = [];
            var parent = [];
            for (let i = 0; i < $('select[id$="workers"]').length; i++) {
                id[i] = $('select[id$="workers"]').eq(i).val();
                sibling[i] = $('select[id$="workers"]').eq(i).val();
                parent[i] = $('select[id$="workers"]').eq(i).parent().parent();
            }

            var url = Routing.generate('orders_ajax');
            $.ajax({
                type: "POST",
                url: url,
                data: {'idWorker': id},
                cache: false,
                dataType: 'json',
                success: function (data) {
                    if (data) {
                        var services = data['parent'];
                    } else {

                        for (let i = 0; i < parent.length; i++) {
                            parent[i].find('select[id$="servicesparent"]').attr('disabled', 'disabled');

                            parent[i].find('select[id$="services"] option').each(function () {
                                if ($(this).val() != "" && $(this).prop('selected') === false) {
                                    $(this).remove();
                                }
                            });

                            parent[i].find('select[id$="materials"] option').each(function () {
                                if ($(this).val() != "" && $(this).prop('selected') === false) {
                                    $(this).remove();
                                }
                            });

                            parent[i].find('select[id$="services"]').attr('disabled', 'disabled');
                            parent[i].find('select[id$="materials"]').attr('disabled', 'disabled');
                        }
                    }
                    var servicesSel = [];

                    for (let i = 0; i < parent.length; i++) {
                        parent[i].find('select[id$="servicesparent"] option').each(function () {
                            if (services) {
                                if ($(this).val() != "" && $(this).prop('selected') === false) {
                                    $(this).remove();
                                }
                                if ($(this).prop('selected') === true) {
                                    servicesSel[i] = $(this).val();
                                }
                            } else {
                                if ($(this).val() != "") {
                                    $(this).remove();
                                }
                            }
                        });
                    }


                    if (services) {
                        for (let j = 0; j < parent.length; j++) {
                            parent[j].find('select[id$="servicesparent"]').removeAttr('disabled');
                            services.forEach(function (item, i) {
                                if (services[i].id != servicesSel[j] && services[i].worker_id == id[j]) {
                                    parent[j].find('select[id$="servicesparent"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                                }
                            });
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus + errorThrown);
                }
            })
        });




    $('form').on('change', 'select[id$="servicesparent"]', function(){
        var id = [$(this).val()];
        var url = Routing.generate('orders_ajax');
        var sibling = $(this);
        var parent = sibling.parent().parent();
        parent.find('textarea[id$="free"]').val('');
        parent.find('input[id$="pricefr"]').val('');
        parent.find('input[id$="pricefr"]').attr('value', '');
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
                    parent.find('select[id$="services"]').removeAttr('data-free');
                    parent.find('select[id$="services"]').removeAttr('data-pricefr');
                    parent.find('select[id$="services"]').removeAttr('data-free-pricefr');
                    services.forEach(function (item, i) {
                        if (services[i].id != servicesSel) {
                            if (services[i].free && !services[i].pricefr) {
                                parent.find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                                parent.find('select[id$="services"]').attr('data-free', services[i].id);
                            } else if (services[i].pricefr && !services[i].free) {
                                parent.find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                                parent.find('select[id$="services"]').attr('data-pricefr', services[i].id);
                            } else if (services[i].free && services[i].pricefr) {
                                parent.find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                                parent.find('select[id$="services"]').attr('data-free-pricefr', services[i].id);
                            } else {
                                parent.find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                            }
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
                console.log(textStatus + errorThrown);
            }
        });
    })






        $(function () {
            var id = [];
            var sibling = [];
            var parent = [];
            var url = Routing.generate('orders_ajax');
            for (let i = 0; i < $('select[id$="servicesparent"]').length; i++) {
                id[i] = $('select[id$="servicesparent"]').eq(i).val();
                sibling[i] = $('select[id$="servicesparent"]').eq(i).val();
                parent[i] = $('select[id$="servicesparent"]').eq(i).parent().parent();
            }

            $.ajax({
                type: "POST",
                url: url,
                data: {'idSelect': id},
                cache: false,
                dataType: 'json',
                success: function (data) {
                    if (data) {
                        var services = data['services'];
                        var materials = data['materials'];
                    } else {
                        for (let i = 0; i < parent.length; i++) {
                            parent[i].find('select[id$="services"]').attr('disabled', 'disabled');
                            parent[i].find('select[id$="materials"]').attr('disabled', 'disabled');
                        }
                    }

                    var servicesSel = [];
                    var materialsSel = [];

                    for (let i = 0; i < parent.length; i++) {

                        parent[i].find('select[id$="services"] option').each(function () {
                            if ($(this).val() != "" && $(this).prop('selected') === false) {
                                $(this).remove();
                            }
                            if ($(this).prop('selected') === true) {
                                servicesSel[i] = $(this).val();
                            }

                        });

                        parent[i].find('select[id$="materials"] option').each(function () {
                            if ($(this).val() != "" && $(this).prop('selected') === false) {
                                $(this).remove();
                            }
                            if ($(this).prop('selected') === true) {
                                materialsSel[i] = $(this).val();
                            }
                        });
                    }
                    if (services) {
                        for (let j = 0; j < parent.length; j++) {
                            parent[j].find('select[id$="services"]').removeAttr('disabled');
                            services.forEach(function (item, i) {

                                if (services[i].id != servicesSel[j] && services[i].parent_id == sibling[j]) {
                                    if (services[i].free && !services[i].pricefr) {
                                        parent[j].find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                                        parent[j].find('select[id$="services"]').attr('data-free', services[i].id);
                                    } else if (services[i].pricefr && !services[i].free) {
                                        parent[j].find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                                        parent[j].find('select[id$="services"]').attr('data-pricefr', services[i].id);
                                    } else if (services[i].free && services[i].pricefr) {
                                        parent[j].find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                                        parent[j].find('select[id$="services"]').attr('data-free-pricefr', services[i].id);
                                    } else {
                                        parent[j].find('select[id$="services"]').append('<option value="' + services[i].id + '">' + services[i].name + '</option>')
                                    }
                                } else if(services[i].parent_id == sibling[j]) {
                                    if (services[i].free && !services[i].pricefr) {
                                        parent[j].find('select[id$="services"]').attr('data-free', services[i].id);
                                        parent[j].find('label[for$="free"]').addClass('active');
                                        parent[j].find('textarea[id$="free"]').addClass('active');
                                    } else if (services[i].pricefr && !services[i].free) {
                                        parent[j].find('select[id$="services"]').attr('data-pricefr', services[i].id);
                                        parent[j].find('label[for$="pricefr"]').addClass('active');
                                        parent[j].find('input[id$="pricefr"]').addClass('active');
                                        parent[j].find('input[id$="pricefrAction"]').val(true);
                                    } else if (services[i].free && services[i].pricefr) {
                                        parent[j].find('select[id$="services"]').attr('data-free', services[i].id);
                                        parent[j].find('label[for$="free"]').addClass('active');
                                        parent[j].find('textarea[id$="free"]').addClass('active');
                                        parent[j].find('select[id$="services"]').attr('data-pricefr', services[i].id);
                                        parent[j].find('label[for$="pricefr"]').addClass('active');
                                        parent[j].find('input[id$="pricefr"]').addClass('active');
                                        parent[j].find('input[id$="pricefrAction"]').val(true);
                                    }
                                }
                            });
                        }
                    }

                    if (materials) {
                        for (let j = 0; j < parent.length; j++) {
                            parent[j].find('select[id$="materials"]').removeAttr('disabled');
                            materials.forEach(function (item, i) {
                                if (materials[i].id != materialsSel) {
                                    parent[j].find('select[id$="materials"]').append('<option value="' + materials[i].id + '">' + materials[i].name + '</option>')
                                }
                            });
                        }
                    }

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus + errorThrown);
                }
            });
        })


        $('form').on('change', '#kiraxe_admincrmbundle_orders_brandId', function () {
            var id = $(this).val();
            var url = Routing.generate('orders_ajaxmodel');
            $.ajax({
                type: "POST",
                url: url,
                data: {'idSelect': id},
                cache: false,
                dataType: 'json',
                success: function (data) {

                    var brand = data['brand'];
                    var brandSel = [];

                    $('#kiraxe_admincrmbundle_orders_carId').find('option').each(function () {

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
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus + errorThrown);
                }
            });
        })


        $(function () {
            var id = $('#kiraxe_admincrmbundle_orders_brandId').val();
            var url = Routing.generate('orders_ajaxmodel');
            $.ajax({
                type: "POST",
                url: url,
                data: {'idSelect': id},
                cache: false,
                dataType: 'json',
                success: function (data) {

                    var brand = data['brand'];
                    var brandSel = [];

                    $('#kiraxe_admincrmbundle_orders_carId').find('option').each(function () {

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
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus + errorThrown);
                }
            });
        })



    $('form').on('change', 'select[id$="services"]', function(){
        var id = $(this).attr("id");
        var parent = $(this).parent().parent();

        if ($(this).val() == $('#' + id).attr('data-free')) {
            parent.find('label[for$="free"]').addClass('active');
            parent.find('textarea[id$="free"]').addClass('active');
        } else if (parent.find('textarea[id$="free"]').hasClass('active')) {
            parent.find('label[for$="free"]').removeClass('active');
            parent.find('textarea[id$="free"]').removeClass('active');
            parent.find('textarea[id$="free"]').val('');
        }

        if ($(this).val() == $('#' + id).attr('data-pricefr')) {
            parent.find('label[for$="pricefr"]').addClass('active');
            parent.find('input[id$="pricefr"]').addClass('active');
            parent[j].find('input[id$="pricefrAction"]').val(true);
        } else if (parent.find('input[id$="pricefr"]').hasClass('active')) {
            parent.find('label[for$="pricefr"]').removeClass('active');
            parent.find('input[id$="pricefr"]').removeClass('active');
            parent.find('input[id$="pricefr"]').val('');
            parent.find('input[id$="pricefr"]').attr('value', '');
            parent[j].find('input[id$="pricefrAction"]').val(false);
        }

        if ($(this).val() == $('#' + id).attr('data-free-pricefr')) {
            parent.find('label[for$="free"]').addClass('active');
            parent.find('textarea[id$="free"]').addClass('active');
            parent.find('label[for$="pricefr"]').addClass('active');
            parent.find('input[id$="pricefr"]').addClass('active');
            parent[j].find('input[id$="pricefrAction"]').val(true);
        } else if (parent.find('input[id$="pricefr"]').hasClass('active') && parent.find('textarea[id$="free"]').hasClass('active')) {
            parent.find('label[for$="free"]').removeClass('active');
            parent.find('textarea[id$="free"]').removeClass('active');
            parent.find('textarea[id$="free"]').val('');
            parent.find('label[for$="pricefr"]').removeClass('active');
            parent.find('input[id$="pricefr"]').removeClass('active');
            parent.find('input[id$="pricefr"]').val('');
            parent.find('input[id$="pricefr"]').attr('value', '');
            parent[j].find('input[id$="pricefrAction"]').val(false);
        }

    });
});