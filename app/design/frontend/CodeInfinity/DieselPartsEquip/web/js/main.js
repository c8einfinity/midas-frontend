require(['jquery', 'jquery.bootstrap'], function($){
    // DOM ready
    $(function(){
        // This function is needed (even if empty) to force RequireJS to load Twitter Bootstrap and its Data API.
        console.log ('Loaded bootstrap');


        // $(".category-menu-toggle").click(function(e) {
        //     $(".category-menu").toggleClass("show");
        //     e.preventDefault();
        // });

        // if ($("body").hasClass("cms-home")) {
        //     $(".category-menu").addClass("show");
        // } else {
        //     $(".category-menu").removeClass("show");
        // }

        $( document ).ready(function() {
            // $(".category-menu-container.desktop .category-item-link.has-children").hover(function(e) {
            //     $(this).next(".category-sub-menu").toggleClass("show");
            //     $(this).toggleClass("active");
            //     e.preventDefault();
            // });


            $(".category-menu-mobile .category-item-link.has-children").click(function(e) {
                $(this).next(".category-sub-menu").toggleClass("show");
                $(this).toggleClass("active");
                e.preventDefault();
            });

            // $(".category-menu-container.desktop .category-sub-menu").hover(function(e) {
            //     $(this).toggleClass("show");
            //     $(this).prev(".category-item-link.has-children").toggleClass("active");
            //     e.preventDefault();
            // });

            $(".mobile-menu-toggle").click(function(e) {
                $(".category-menu-mobile").addClass("show");
                e.preventDefault();
                $("body").addClass("mobile-nav-open");
            });

            $(".mobile-menu-close, .category-menu-mobile-backdrop").click(function(e) {
                $(".category-menu-mobile").removeClass("show");
                e.preventDefault();
                $("body").removeClass("mobile-nav-open");
            });

            $(".mobile-search-toggle").click(function(e) {
                $(".top-search-bar-container").addClass("show");
                e.preventDefault();
            });

            $(".mobile-search-close, .top-search-bar-mobile-backdrop").click(function(e) {
                $(".top-search-bar-container").removeClass("show");
                e.preventDefault();
            });


            $(".sub-link-container").each(function(){
                var numChilds = $("a", $(this)).length;
                var lastItem = $(this).find("a.view-more");

                if (numChilds <= 7) {
                    lastItem.removeClass("show");
                } else {
                    lastItem.addClass("show");
                }
            });
        });
    });
});
