$('.tron').hover(
    function () {
        $(this).find('td').css('backgroundColor','#eee');
    },function () {
        $(this).find('td').css('backgroundColor','');
    }
);