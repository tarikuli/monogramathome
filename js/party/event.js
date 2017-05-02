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
        $(".diff_loc").click(function() {
            if ($(".self_host:checked").length > 0) {
                $.get("/monogramathome/party/event/address", function(result) {
                    var html = jsonToHtml(result);
                    $("#addresses").html(html);
                })
            }
            else if ($(".customer_host:checked").length > 0) {
                if ($("#customers").val() != "") {
                    $.get("/monogramathome/party/event/address",{'id':$("#customers").val()},function(result) {
                        var html = jsonToHtml(result);
                        $("#addresses").html(html);
                    });
                }
                else {
                    $("#customers").focus();
                    $(".customer_host").checked = false;
                    $(this).attr("checked",false);
                    return false;
                }
            }
            $("#address_block").slideDown("slow");
        });

        $("#customers").change(function() {
            if ($(this).val() != "" &&  $(".diff_loc:checked").length > 0) {
                $.get("/monogramathome/party/event/address",{'id':$(this).val()},function(result) {
                    var html = jsonToHtml(result);
                    $("#addresses").html(html);
                });
            }
        });

        function jsonToHtml(json) {
            var html = ""
            $.each(json, function(key, val){
                html += "<option value='"+key+"'>"+val+"</option>";
            });
            return html;
        }

        //$('#datetimepicker1').datetimepicker();
        //$('#datetimepicker2').datetimepicker();
    });
})(jQuery);
