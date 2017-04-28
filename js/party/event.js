/**
 * Created by julfiker on 4/26/17.
 */

(function($){
    $(document).ready(function(){
        $(".customer_host").click(function(){
            $("#customer_block").slideDown("slow");
        });
        $(".self_host").click(function(){
            $("#customer_block").slideUp("slow");
        });
        $(".host_loc").click(function(){
            $("#address_block").slideUp("slow");
        });
        $(".diff_loc").click(function(){
            $("#address_block").slideDown("slow");
        });

        //$('#datetimepicker1').datetimepicker();
        //$('#datetimepicker2').datetimepicker();
    });
})(jQuery);
