/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.5.3
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
                    var fromSplit = from.split('-');
                    var toSplit   = to  .split('-');
                    var newFromDate = new Date(
                        fromSplit[2],
                        fromSplit[1],
                        fromSplit[0]
                    ).getTime();
                    var newToDate = new Date(
                        toSplit[2],
                        toSplit[1],
                        toSplit[0]
                    ).getTime();
                    var difference = newToDate - newFromDate;

                    if (Math.abs(difference) < (3600 * 24)) {
                        alert('mala roznica');
                        $('#splash_screen').hide();
                    } else {
                        $.post('',
                            {
                                page: 'rooms',
                                from: $('#from')  .val(),
                                to:   $('#to')    .val()
                            },
                            function (data)
                            {
                                $('#result_rooms')          .html(data);
                                $('#splash_screen')         .hide();
                                $('#breadcrumbs li:eq(0)')  .removeClass('selected');
                                $('#breadcrumbs li:eq(0)')  .addClass('visited');
                                $('#breadcrumbs li:eq(1)')  .addClass('selected');
                                $('#calendar')              .hide();
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
                            selectedRooms:  selectedRooms
                        },
                        function (data)
                        {
                            $('#result_payment')        .html(data);
                            $('#splash_screen')         .hide();
                            $('#breadcrumbs li:eq(1)')  .removeClass('selected');
                            $('#breadcrumbs li:eq(1)')  .addClass('visited');
                            $('#breadcrumbs li:eq(2)')  .addClass('selected');
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
                        $('#splash_screen')         .hide();
                        $('#breadcrumbs li:eq(2)')  .removeClass('selected');
                        $('#breadcrumbs li:eq(2)')  .addClass('visited');
                        $('#breadcrumbs li:eq(3)')  .addClass('selected');
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
                            $('#splash_screen')         .hide();
                            $('#breadcrumbs li:eq(3)')  .removeClass('selected');
                            $('#breadcrumbs li:eq(3)')  .addClass('visited');
                            $('#breadcrumbs li:eq(4)')  .addClass('selected');
                            $('#result_contact')        .hide();
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
        fullPrice   = $('#price_summary i').html();
        price       = $(this).parent().find('span i').html();
        isChecked   = $(this).is(':checked');
        basePrice   = $(this).parent().parent().find('.price i').html();
        price      -= basePrice;

        if (isChecked) {
            finalPrice = parseFloat(fullPrice) + parseFloat(price);
        } else {
            finalPrice = fullPrice - price;
        }

        $('#price_summary i').html(finalPrice);
    });

    $('body').on('click', '.dostawka_price', function()
    {
        fullPrice       = $('#price_summary i').html();
        name            = $(this).attr('name');

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

        $('#price_summary i').html(finalPrice);
    });
});

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