/*!
    * Start Bootstrap - SB Admin v6.0.0 (https://startbootstrap.com/templates/sb-admin)
    * Copyright 2013-2020 Start Bootstrap
    * Licensed under MIT (https://github.com/BlackrockDigital/startbootstrap-sb-admin/blob/master/LICENSE)
    */

    // create redips container
let redips = {};

    (function($) {

    // Add active state to sidbar nav links
    var path = window.location.href; // because the 'href' property of the DOM element is the absolute path
        $("#layoutSidenav_nav .sb-sidenav a.nav-link").each(function() {
            if (this.href === path) {
                $(this).addClass("active");
            }
        });

    // Toggle the side navigation
    $("#sidebarToggle").on("click", function(e) {
        e.preventDefault();
        $("body").toggleClass("sb-sidenav-toggled");
    });

    // style file input field upon file upload
    $('input[id="file"]').change(
        function(e){
            if ($(this).val()) {
                $('input[id="import"]').removeAttr('hidden');
                $('input[id="ImportButton"]').attr('hidden','true');
                $('input[id="import"').attr('value',"Import '" + e.target.files[0].name + "'");
            }
        }
    );

    // read grid data and pass it to hidden input field on index
    $("#gridSave").on("click", function() {
        $(this).attr('hidden','true');
        $("#gridPush").removeAttr('disabled');
        $("#gridVals").attr('value',REDIPS.drag.saveContent('gridTable'));
        $("#holdVals").attr('value',REDIPS.drag.saveContent('holdingTank'))
    });

})(jQuery);

