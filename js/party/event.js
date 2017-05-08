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

        var form = new VarienForm('event_form', true);
        //Validation.add('start_at','You failed to enter baz!',function(the_field_value){
        //    if(the_field_value == 'baz')
        //    {
        //        return true;
        //    }
        //    return false;
        //});

        function populate(selector) {
            var select = $(selector);
            var hours, minutes, ampm;
            for(var i = 420; i <= 1320; i += 15){
                hours = Math.floor(i / 60);
                minutes = i % 60;
                if (minutes < 10){
                    minutes = '0' + minutes; // adding leading zero
                }
                ampm = hours % 24 < 12 ? 'AM' : 'PM';
                hours = hours % 12;
                if (hours === 0){
                    hours = 12;
                }
                select.append($('<option></option>')
                    .attr('value', hours + ':' + minutes + ' ' + ampm)
                    .text(hours + ':' + minutes + ' ' + ampm));
            }
        }

        populate('.timeSelect'); // use selector for your select


    });
})(jQuery);
