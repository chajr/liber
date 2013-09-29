$(document).ready(function()
{
    var termToRemove;
    $('.remove_term').click(function()
    {
        termToRemove = $(this).data('id');
    });
    $('.remove_term_modal').click(function()
    {
        window.location.href = '?page=remove_term&id=' + termToRemove;
    });
    $('.remove_term_cancel').click(function()
    {
        termToRemove = null;
    });

    //hide information blocks
    $('#ok').delay(3500).slideUp('slow');
    $('#error').delay(3500).slideUp('slow');

    $('.logo_small').hover(function()
    {
        $('.logo_bigger').show();
    },
    function()
    {
        $('.logo_bigger').hide();
    });
    
    //save payment
    $('input.payment').click(function()
    {
        var id          = $(this).data('paymentid');
        var value       = '';
        var elemenrt    = $(this);

        if (elemenrt.is(':checked')) {
            value = 'set_payment';
        } else {
            value = 'unset_payment';
        }

        $.post('?page=set_payment', {value: value, id: id}).done(function(data){
            console.log(data);
            if (elemenrt.is(':checked') && data === 'ok') {
                $('#reservation_detail_' + id + ' .modal-content').addClass('payment_done');
                $('#reservation_detail_' + id + ' .modal-title').append('<i class="icon-check"></i>');
            } else if(data === 'ok') {
                $('#reservation_detail_' + id + ' .modal-content').removeClass('payment_done');
                $('#reservation_detail_' + id + ' .modal-title i').remove();
            } else {
                alert('Coś poszło nie tak, spróbuj jeszcze raz');
            }
        });
    });
});