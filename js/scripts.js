/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.2.0
 * @copyright chajr/bluetree
 */
$(document).ready(function()
{
    var selectedDate    = 0;
    var step            = 0;
    var selectedRooms   = new Array();

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
                                $('#result').html(data);
                                $('#splash_screen').hide();
                                $('#breadcrumbs li:eq(0)').removeClass('selected');
                                $('#breadcrumbs li:eq(0)').addClass('visited');
                                $('#breadcrumbs li:eq(1)').addClass('selected');
                                $('#calendar').hide();
                            });
                        break;
                    }
                }
                break;
        }
    });

    $('#steps .previous').click(function()
    {
        
    });

    $('body').on('click', '.room.unselected .select', function()
    {
        id = $(this).parent().data('id');
        $(this).parent().addClass('selected');
        $(this).parent().removeClass('unselected');
        $(this).text('Anuluj');
        selectedRooms.push(id);
    });

    $('body').on('click', '.room.selected .select', function()
    {
        id = $(this).parent().data('id');
        $(this).parent().removeClass('selected');
        $(this).parent().addClass('unselected');
        $(this).text('Wybierz');
        index = selectedRooms.lastIndexOf(id);
        delete selectedRooms[index];
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