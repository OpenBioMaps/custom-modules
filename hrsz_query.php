<?php
class hrsz_query_Class  {

    private $query_table = 'hrsz_terkepek.kuvet_bevet_2018';
    private $filter_column = 'telepules';
    private $nested_column = 'hrsz';
    private $function_name = 'hrsz_query';
    private $id = 'hrsz';
    private $box_title = '"Helyrajziszám lekérdezés';


    function print_box ($params='') {
        global $ID;
        $t = new table_row_template();


        $iv = base64_decode($_SESSION['openssl_ivs']['text_filter_table']);
        $encrypted_table_reference = base64_encode(openssl_encrypt($this->query_table, "AES-128-CBC", $_SESSION['private_key'], OPENSSL_RAW_DATA,$iv));

        $t->cell(mb_convert_case($this->box_title, MB_CASE_UPPER, "UTF-8"),2,'title center');
        $t->row();
        
        $s = new select_input_template();
        $s->autocomplete = true;
        $s->access_class = 'qfc nested';
        $s->destination_table = $this->query_table;
        $s->tdata = array('nested_column'=>$this->nested_column,'nested_element'=> $this->nested_column,'etr'=>$encrypted_table_reference,'all_options'=>'on');
        $s->new_menu($this->filter_column,''); 

        $t->cell("<input id='select_with_custom_polygon' data-etr='$encrypted_table_reference' class='custom_box' value='$this->function_name' type='hidden'><input type='hidden' class='speedup_filter' value='on'><input type='hidden' id='spatial_function' name='spatial_function' value='within'>$s->menu",2,'content');
        $t->row();
        $t->cell("<select id=$this->id data='custom_polygon_id' class='qfc custom_polygon_id' style='width:100%' multiple='multiple' size=6></select>",2,'content');
        $t->row();
        //$t->cell("<button id='select_with_custom_polygon' class='button-gray button-xlarge pure-button' data-etr='$encrypted_table_reference'><i class='fa-search fa'></i> ".str_query."</button>",2,'title');
        $t->row();
        return sprintf("<table class='mapfb'>%s</table>",$t->printOut());

    }

    function print_js ($params='') {
        echo ' 

    $(document).ready(function() {
        $("#mapfilters").on("change","#'.$this->id.'",function(){
            drawLayer.destroyFeatures();

            var etr = $("#select_with_custom_polygon").data("etr");

            var cpv_id = $("#select_with_custom_polygon").closest(".mapfb").find(".custom_polygon_id").attr("id");
            $("#" + cpv_id + " option:selected").each(function () {
                $.post("ajax", {getWktGeometry:$(this).data("id"),custom_table:etr},
                function(data){
                    if (data!="0") {
                        // draw polygon on map
                        drawPolygonFromWKT(data,1);
                        skip_loadQueryMap_customBox = 1;
                    }
                });
            });
        });
    });
    
    // this function name will be automaticall processed by its  name
    function custom_'.$this->function_name.'() {
        var etr = $("#select_with_custom_polygon").data("etr");
        var qids = new Array();
        var qval = new Array();

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

        var myVar = { geom_selection:"custom_polygons",spatial_function:$("#spatial_function").val(),custom_table:etr }
        
        for (i=0;i<qids.length;i++) {
            myVar["qids_" + qids[i]] = qval[i];
        }

        return myVar;
    }
';

    }
}
?>
