/**
 * Created by Flavien on 01/07/2016.
 */


$(document).ready(function () {
    /** Global pre-loader */
    $('.at-global-preloader').hide();
});


/*-------------------*/
/** Global preloader */
/*-------------------*/
$(document).on('mousemove', function(e){
    $('.at-global-preloader').css({
        left:  e.pageX,
        top:   e.pageY
    });
});