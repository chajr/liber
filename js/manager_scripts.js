$(document).ready(function()
{
    var termToRemove;
    var reservationToRemove;
    var promotionToRemove;

    $('.remove_term').click(function()
    {
        termToRemove = $(this).data('id');
    });
    $('.remove_term_modal').click(function()
    {
        window.location.href = '?page=remove_term&id_remove=' + termToRemove;
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

    //add new promotion
    $('.add_promotion').click(function()
    {
        var newBlock = $('#default_promotion_block .promotion_box').clone();
        $('#promotion_list').append(newBlock);
    });

    //remove promotion
    $('body').on('click', '.remove_promotion', function()
    {
        var promotionId = $(this).parent().attr('id');
        if (promotionId === undefined) {
            $(this).parent().remove();
        } else {
            promotionToRemove = $(this).data('id');
        }
    });
    $('.remove_promotion_modal').click(function()
    {
        window.location.href = '?page=remove_promotion&id=' + promotionToRemove;
    });
    $('.remove_term_cancel').click(function()
    {
        promotionToRemove = null;
    });

    //saving promotions
    $('.save_promotions').click(function()
    {
        $('#promotion_list').submit();
    });

    $('.remove_reservation').click(function()
    {
        reservationToRemove = $(this).data('id');
    });
    $('.remove_reservation_modal').click(function()
    {
        window.location.href = '?page=remove_reservation&id_remove=' + reservationToRemove;
    });
    $('.remove_term_cancel').click(function()
    {
        reservationToRemove = null;
    });
});