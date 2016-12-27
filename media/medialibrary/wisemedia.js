$(document).ready(function() {
        $("#side_nav h2").click(function(){
                if($(this).hasClass("current")){return;}
                else{
                        $("#side_nav h2").removeClass("current");
                        $("#side_nav ul").removeClass("pane");
                        $("#side_nav ul").slideUp("fast");
                        $(this).addClass("current");
                        $(this).next("ul").addClass("pane");
                        $(this).next("ul").slideDown(0);
                }
        }).eq(menu_m).click();
});
