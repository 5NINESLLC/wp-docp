(function ($) {
    'use strict';

    $(document).ready(function () {
        $(".data").hide();
        $('.toggle').click(function () {
            $(this).next().toggle();
        });

    });

})(jQuery);