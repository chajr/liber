$(document).ready(function()
{
    var termToRemove;
    $('.remove_reservation').click(function()
    {
        termToRemove = $(this).data('id');
    });
    $('.remove_reservation_modal').click(function()
    {
        window.location.href = '?page=remove_term&id=' + termToRemove;
    });
    $('.remove_reservation_cancel').click(function()
    {
        termToRemove = null;
    });
});