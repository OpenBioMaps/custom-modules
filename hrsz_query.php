<?php
class hrsz_query_Class  {
    function print_box ($params='') {
        global $ID;
        $t = new table_row_template();

        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted_table_reference = base64_encode(openssl_encrypt('hrsz_terkepek.kuvet_bevet_2018', $cipher, $_SESSION['private_key'], OPENSSL_RAW_DATA,'wohJaom9'));

        $t->cell(mb_convert_case("Helyrajziszám lekérdezés", MB_CASE_UPPER, "UTF-8"),2,'title center');
        $t->row();
        
        $s = new select_input_template();
        $s->autocomplete = true;
        $s->access_class = 'qfc nested';
        $s->destination_table = 'hrsz_terkepek.kuvet_bevet_2018';
        $s->tdata = array('nested_column'=>'hrsz','nested_element'=>'hrsz','etr'=>$encrypted_table_reference,'all_options'=>'on');
        $s->new_menu('telepules',''); 

        $t->cell("<input type='hidden' class='speedup_filter' value='on'><input type='hidden' id='spatial_function' name='spatial_function' value='within'>$s->menu",2,'content');
        $t->row();
        $t->cell("<select id='hrsz' data='custom_polygon_id' class='qfc custom_polygon_id' style='width:100%' multiple='multiple' size=4></select>",2,'content');
        $t->row();
        $t->cell("<button id='select_with_custom_polygon' class='button-gray button-xlarge pure-button' data-etr='$encrypted_table_reference'><i class='fa-search fa'></i> ".str_query."</button>",2,'title');
        $t->row();
        return sprintf("<table class='mapfb'>%s</table>",$t->printOut());

    }

    function print_js ($params='') {
        echo '

$(document).ready(function() {
        $("#mapfilters").on("click","#select_with_custom_polygon",function(){
        drawLayer.destroyFeatures();

        var qids = new Array();
        var qval = new Array();
        var etr = $(this).data("etr");

        $(".qfc").each(function(){
            var stri = "";
            var stra = new Array();
            var qid=$(this).attr("id");
            if ( $(this).prop("type") == "text" ) {
                stra.push($(this).val());
            } else if ($(this).prop("type") == "button" ) {
                stra.push(($(this).find("i").hasClass("fa-toggle-on")) ? "on" : "");
            } else {
                $("#" + qid + " option:selected").each(function () {
                    stra.push($(this).val());
                });
            }
            if (stra.length) {
                qids.push(qid);
                qval.push(JSON.stringify(stra));
            }
        });

        var cpv_id = $(this).closest(".mapfb").find(".custom_polygon_id").attr("id");
        $("#" + cpv_id + " option:selected").each(function () {
            $.post("ajax", {getWktGeometry:$(this).data("id"),custom_table:etr},
            function(data){
                if (data!="0") {
                    // draw polygon on map
                    drawPolygonFromWKT(data,1);
                }
            });
        });
        var myVar = {}
        var myVar = { geom_selection:"custom_polygons",spatial_function:$("#spatial_function").val(),custom_table:etr }
        
        for (i=0;i<qids.length;i++) {
            myVar["qids_" + qids[i]] = qval[i];
        }
        $("#navigator").hide();
        loadQueryMap(myVar);
    });
})';

    }
}
?>
