/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.3.0
 * @copyright chajr/bluetree
 */
$(document).ready(function()
{
    var selectedDate    = 0;
    var step            = 0;
    var selectedRooms   = {};

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
                var from = $('#from').val();
                var to   = $('#to').val();
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
                    if (difference < (3600 * 24)) {
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
            roomId: id,
            roomSpace: space
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
});
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
    var target = document.getElementById('spiner');
    var spinner = new Spinner(opts).spin(target);
}