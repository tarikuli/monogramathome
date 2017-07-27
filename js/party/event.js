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

        $(document).on('submit', "#form-member", function(e) {
            $.post($(this).attr('action')+"type/json",$(this).serialize(), function(result) {
                if (result.error) {
                    var message = result.message;
                    alert(message);
                }
                else {
                    $('#customer_popup').modal('toggle');
                    $('#customer_popup').find('form').get(0).reset();
                    $('#customers').append($('<option>', {
                        value: result.id,
                        text: result.name
                    }));
                    $('#customers option[value="'+result.id+'"]').attr('selected', 'selected');
                }
            });
            e.preventDefault();
        });

        $(document).on('submit', "#form-address", function(e) {
            var customerId = $('#customers').val();
            if (customerId == ""){
                alert("Please select member");
                return;
            }
            $.post($(this).attr('action')+"id/"+customerId,$(this).serialize(), function(result) {
                if (result.error) {
                    var message = result.message;
                    alert(message);
                }
                else {
                    $('#location_popup').modal('toggle');
                    $('#location_popup').find('form').get(0).reset();
                    $('#addresses').append($('<option>', {
                        value: result.id,
                        text: result.location
                    }));
                    $('#addresses option[value="'+result.id+'"]').attr('selected', 'selected');
                }
            });
            e.preventDefault();
        });


        function jsonToHtml(json) {
            var html = ""
            $.each(json, function(key, val){
                html += "<option value='"+key+"'>"+val+"</option>";
            });
            return html;
        }

        var form = new VarienForm('event_form', true);
        Validation.add('end_time','Please make sure that your start time is before your end time!',function(the_field_value){
            //start time
            var start_time = jQuery(document).find(".start_time").val();

            //end time
            var end_time = the_field_value;

            //convert both time into timestamp
            var start_at = jQuery(document).find("#start_at").val();
            var stt = new Date(start_at + " " + start_time);
            stt = stt.getTime();

            var end_at = jQuery(document).find("#end_at").val();
            var endt = new Date(end_at + " " + end_time);
            endt = endt.getTime();

            if(stt >= endt) {
                return false;
            }

            return true;
        });

        function populate(selector) {
            var select = $(selector);
            var hours, minutes, ampm;
            for(var i = 0; i <= 1440; i += 30){
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
