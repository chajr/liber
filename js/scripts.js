/**
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 0.1.0
 * @copyright chajr/bluetree
 */
$(document).ready(function()
{
    var selectedDate = 0;
    var step         = 0;
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
                    'dd-mm-yyyy', 
                    $('#calendar').datepick('getDate')[0])
                );
            }
            if (selectedDate === 1) {
                $('#to').val($.datepick.formatDate(
                    'dd-mm-yyyy',
                    $('#calendar').datepick('getDate')[1])
                );
            }
            selectedDate++;
        }
    });
    $('#steps .next').click(function()
    {
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
                    alert(difference);
                    if (difference < (3600 * 24)) {
                        alert('mala roznica');
                    } else {
                        alert('ok');
                    }
                }
                break;
        }
    });
    $('#steps .previous').click(function()
    {

    });
});