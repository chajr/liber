/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 1.3.0
 * @copyright chajr/bluetree
 */
var validatorErrorList = new Array();

$(document).ready(function()
{
    var selectedDate    = 0;
    var step            = 0;
    var selectedRooms   = {};
    var dostawki        = {};
    var from            = '';
    var to              = '';

    $('#calendar').datepick({
        defaultDate: "+1w",
        changeMonth: true,
        rangeSelect: true,
        monthsToShow: [1, 3],
        onSelect: function(dates)
        {
            if (selectedDate > 1) {
                selectedDate = 0;
                $('#from')  .val('');
                $('#to')    .val('');
            }

            if (selectedDate === 0) {
                $('#from').val($.datepick.formatDate(
                    'yyyy-mm-dd',
                    $('#calendar').datepick('getDate')[0])
                );
            }

            if (selectedDate === 1) {
                $('#to').val($.datepick.formatDate(
                    'yyyy-mm-dd',
                    $('#calendar').datepick('getDate')[1])
                );
            }
            selectedDate++;
        }
    });

    $('#steps .next').click(function()
    {
        showSpiner();
        $('#splash_screen').show();

        switch (step) {
            case 0:
                from = $('#from')   .val();
                to   = $('#to')     .val();

                if (from === '' || to === '') {
                    alert('brak dat');
                } else {
                    var newFromDate = convertDate(from);
                    var newToDate   = convertDate(to);
                    var difference  = newToDate - newFromDate;

                    if (Math.abs(difference) < (3600 * 24)) {
                        alert('mala roznica');
                        $('#splash_screen').hide();
                    } else {
                        $.post('',
                            {
                                page: 'rooms',
                                from: from,
                                to:   to
                            },
                            function (data)
                            {
                                $('#result_rooms')          .html(data);
                                $('#result_rooms')          .show();
                                $('#splash_screen')         .hide();
                                $('#breadcrumbs li:eq(0)')  .removeClass('active');
                                $('#breadcrumbs li:eq(0)')  .addClass('visited');
                                $('#breadcrumbs li:eq(1)')  .addClass('active');
                                $('#calendar')              .hide();
                                $('#steps .previous')       .removeClass('disabled');
                                step++;
                            }
                        );
                    }
                }
                break;

            case 1:
                if (selectedRooms.length < 1) {
                    alert('brak wybranych pokoi');
                    $('#splash_screen').hide();
                } else {
                    $('.rooms').find('.unlock.selected').each(function()
                    {
                        id      = $(this).data('id');
                        rooms   = $(this).find('.space option:selected').val();
                        selectedRooms[id]['roomSpace'] = rooms;
                    });
                    $.post('',
                        {
                            page:           'payment',
                            selectedRooms:  selectedRooms,
                            from:           from,
                            to:             to
                        },
                        function (data)
                        {
                            $('#result_payment')        .html(data);
                            $('#result_payment')        .show();
                            $('#splash_screen')         .hide();
                            $('#breadcrumbs li:eq(1)')  .removeClass('active');
                            $('#breadcrumbs li:eq(1)')  .addClass('visited');
                            $('#breadcrumbs li:eq(2)')  .addClass('active');
                            $('#result_rooms')          .hide();
                            step++;
                        }
                    );

                }
                break;

            case 2:
                $('#rooms_prices .room_price').each(function()
                {
                    inputs      = {};
                    elementId   = $(this).attr('id');
                    id          = elementId.replace(/room_/, '');

                    $(this).find('input.spa_price:checked').each(function()
                    {
                        selectedRooms[id]['spa'] = 1;
                    });

                    $(this).find('input.dostawka_price:checked').each(function()
                    {
                        selectedRooms[id]['dostawka'] = $(this).attr('value');
                    });
                });
                $.post('',
                    {
                        page:           'personal'
                    },
                    function (data)
                    {
                        $('#result_contact')        .html(data);
                        $('#result_contact')        .show();
                        $('#splash_screen')         .hide();
                        $('#breadcrumbs li:eq(2)')  .removeClass('active');
                        $('#breadcrumbs li:eq(2)')  .addClass('visited');
                        $('#breadcrumbs li:eq(3)')  .addClass('active');
                        $('#result_payment')        .hide();
                        step++;

                        useValidator();
                    }
                );
                break;

            case 3:
                if (
                       validatorErrorList['email']      != false
                    && validatorErrorList['imie']       != false
                    && validatorErrorList['kod']        != false
                    && validatorErrorList['miasto']     != false
                    && validatorErrorList['nazwisko']   != false
                    && validatorErrorList['numer']      != false
                    && validatorErrorList['regulamin']  != false
                    && validatorErrorList['telefon']    != false
                    && validatorErrorList['ulica']      != false
                ) {
                    $.post('',
                        {
                            page:           'submit',
                            rooms:          selectedRooms,
                            data:           $('#user_data').serializeArray(),
                            from:           from,
                            to:             to
                        },

                        function (data)
                        {
                            $('#result_end')            .html(data);
                            $('#result_end')            .show();
                            $('#splash_screen')         .hide();
                            $('#breadcrumbs li:eq(3)')  .removeClass('active');
                            $('#breadcrumbs li:eq(3)')  .addClass('visited');
                            $('#breadcrumbs li:eq(4)')  .addClass('active');
                            $('#result_contact')        .hide();
                            $('#steps')        .hide();
                            step++;
                        }
                    );
                } else {
                    alert('Formularz jest błędnie wypełniony:(');
                    $('#splash_screen').hide();
                }
                break;
        }
    });

    $('#steps .previous').click(function()
    {
        if (step > 0) {
            showSpiner();
            $('#splash_screen').show();

            switch (step) {
                case 1:
                    $('#breadcrumbs li:eq(0)')  .addClass('active');
                    $('#breadcrumbs li:eq(0)')  .removeClass('visited');
                    $('#breadcrumbs li:eq(1)')  .removeClass('active');
                    $('#calendar')              .show();
                    $('#result_rooms')          .hide();
                    $('#splash_screen')         .hide();
                    $('#steps .previous')       .addClass('disabled');
                    step--;
                    selectedRooms = {};
                    break;

                case 2:
                    $('#result_payment')        .hide();
                    $('#splash_screen')         .hide();
                    $('#breadcrumbs li:eq(1)')  .addClass('active');
                    $('#breadcrumbs li:eq(1)')  .removeClass('visited');
                    $('#breadcrumbs li:eq(2)')  .removeClass('active');
                    $('#result_rooms')          .show();
                    step--;
                    break;

                case 3:
                    $('#breadcrumbs li:eq(2)')  .addClass('active');
                    $('#breadcrumbs li:eq(2)')  .removeClass('visited');
                    $('#breadcrumbs li:eq(3)')  .removeClass('active');
                    $('#result_contact')        .hide();
                    $('#result_payment')        .show();
                    $('#splash_screen').hide();
                    step--;
                    break;
            }
        }
    });

    $('body').on('click', '.room.unselected .select', function()
    {
        $(this).parent().addClass('selected');
        $(this).parent().removeClass('unselected');
        $(this).text('Anuluj');

        id          = $(this).parent().data('id');
        space       = $(this).parent().find('.space option:selected').val();
        roomsArray  = {
            roomId:     id,
            roomSpace:  space,
            spa:        '',
            dostawka:   ''
        };
        selectedRooms[id] = roomsArray;
    });

    $('body').on('click', '.room.selected .select', function()
    {
        $(this).parent().removeClass('selected');
        $(this).parent().addClass('unselected');
        $(this).text('Wybierz');
        id = $(this).parent().data('id');
        delete selectedRooms[id];
    });

    $('body').on('click', '.spa_price', function()
    {
        fullPrice   = $('#price_summary').data('price');
        promotion   = $('#price_summary').data('promotion');
        price       = $(this).parent().find('span i').html();
        isChecked   = $(this).is(':checked');
        basePrice   = $(this).parent().parent().find('.price i').html();
        price      -= basePrice;

        if (isChecked) {
            finalPrice = parseFloat(fullPrice) + parseFloat(price);
        } else {
            finalPrice = fullPrice - price;
        }

        $('#price_summary i strike').html(finalPrice);
        $('#price_summary i span').html(calculatePromotion(finalPrice, promotion));
        $('#price_summary').data('price', finalPrice);
    });

    $('body').on('click', '.dostawka_price', function()
    {
        fullPrice   = $('#price_summary').data('price');
        promotion   = $('#price_summary').data('promotion');
        name        = $(this).attr('name');

        if (!dostawki[name]) {
            previousPrice   = 0;
        } else {
            previousPrice   = dostawki[name];
        }

        finalPrice      = parseFloat(fullPrice) - parseFloat(previousPrice);
        fullPrice       = finalPrice;
        price           = $(this).parent().find('span i').html();
        dostawki[name]  = price;
        finalPrice      = parseFloat(fullPrice) + parseFloat(price);

        $('#price_summary i strike').html(finalPrice);
        $('#price_summary i span').html(calculatePromotion(finalPrice, promotion));
        $('#price_summary').data('price', finalPrice);
    });
});

function calculatePromotion(basePrice, promotion)
{
    percent = (promotion / 100) * basePrice;
    basePrice -= percent;

    return basePrice;
}

function useValidator()
{
    jQuery('#user_data').validVal({
        fields: {
            onInvalid: function(form, language)
            {
                var element             = jQuery(this).parent();
                id                      = jQuery(this).attr('id');
                validatorErrorList[id]  = false;
                element.find('.icon-error').show();
                element.find('.icon-ok').hide();
            },

            onValid: function(form, language)
            {
                var element             = jQuery(this).parent();
                id                      = jQuery(this).attr('id');
                validatorErrorList[id]  = true;
                element.find('.icon-ok').show();
                element.find('.icon-error').hide();
            }
        }
    });
}

function convertDate(date)
{
    var splitDate = date.split('-');
    var newDate = new Date(
        splitDate[2],
        splitDate[1],
        splitDate[0]
    ).getTime();

    return newDate;
}

function showSpiner()
{
    var opts = {
        lines:      13,
        length:     20,
        width:      10,
        radius:     30,
        corners:    1,
        rotate:     0,
        direction:  1,
        color:      '#fff',
        speed:      1,
        trail:      60,
        shadow:     false,
        hwaccel:    false,
        className:  'spinner',
        zIndex:     2e9,
        top:        'auto',
        left:       'auto'
    };
    var target  = document.getElementById('spiner');
    var spinner = new Spinner(opts).spin(target);
}