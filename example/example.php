<?php
header("Content-Type: text/html;charset=utf-8");
?>
<html>
<head>
  <title>XML Schema Description visualizer</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" >
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
    }

    .xsd_element_block {
      background-color: #ededed;
      border: 1px solid #a1a1a1;
      border-radius: 2px 2px;
      font-size: 13px;
      padding: 4px 14px;
      margin-bottom: 6px;
    }

    .xsd_element_block.xsd_element_block_0 { background-color: #ffe3e4; }
    .xsd_element_block.xsd_element_block_1 { background-color: #ffe6da; }
    .xsd_element_block.xsd_element_block_2 { background-color: #fff7db; }
    .xsd_element_block.xsd_element_block_3 { background-color: #edffcd; }
    .xsd_element_block.xsd_element_block_4 { background-color: #cfffd7; }
    .xsd_element_block.xsd_element_block_5 { background-color: #d1fffd; }
    .xsd_element_block.xsd_element_block_6 { background-color: #ced7ff; }
    .xsd_element_block.xsd_element_block_7 { background-color: #e4d2ed; }
    .xsd_element_block.xsd_element_block_8 { background-color: #edd6e5; }


    .xsd_element_block > .xsd_element_name {
      color: #222222;
      font-size: 15px;
      font-weight: bold;
    }
    .xsd_element_block > .xsd_element_type {
      border-bottom: 1px dashed #565656;
      color: #323232;
      cursor: help;
      font-size: 14px;
      font-weight: normal;
      margin-left: 3px;
    }

    .xsd_element_block > .xsd_element_required {
      color: #692025;
      font-size: 14px;
      font-weight: bold;
      margin-left: 5px;
    }

    .xsd_element_block > .xsd_element_infoblock {
      background-color: rgba(0,0,0,0.07);
      border-radius: 4px 4px;
      margin: 8px 2px 3px 2px;
      padding: 4px 6px;
    }
    .xsd_element_block > .xsd_element_infoblock > .xsd_element_infoblock_title {
      color: #323232;
      float: right;
      font-weight: bold;
    }

    .xsd_element_block > .xsd_element_children {
      margin: 11px 2px 0 2px;
    }

    .xsd_element_block > .xsd_element_children > .xsd_element_children_toggler {
      background-color: rgba(0, 0, 0, 0.2);
      border-radius: 2px 2px;
      color: #363636;
      cursor: pointer;
      height: 14px;
      margin-bottom: 3px;
      padding: 2px 5px;
      text-align: center;
    }

    .global_expander {
      cursor: pointer;
      font-size: 13px;
      font-weight: bold;
    }
  </style>
  <!--[if lte IE 8]>
  <style>
    .xsd_element_block > .xsd_element_infoblock {
      background-color: #e2dbd4;
      border: 1px solid #a3a3a3;
    }
    .xsd_element_block > .xsd_element_children > .xsd_element_children_toggler {
      background-color: #d3d2c0;
      border: 1px solid #a3a3a3;
    }
  </style>
  <![endif]-->
  <script>
    window.onload = function(){
      var textContentName = (typeof document.body.textContent != "undefined" ? "textContent" : "innerText");
      var c = document.querySelectorAll(".xsd_element_children");
      var cis = [];

      var change_state = function(tog, cnt){
        if (tog.getAttribute("ref-state") == "open") {
          tog[textContentName] = "collapse children";
          cnt.style.display = "block";
        } else {
          tog[textContentName] = "show children";
          cnt.style.display = "none";
        }
      };

      for (var i = 0; i < c.length; i++) {
        (function(ci){
          var
            tog = ci.querySelector(".xsd_element_children_toggler"),
            cnt = ci.querySelector(".xsd_element_children_container");

          tog.onclick = function(){
            if (tog.getAttribute("ref-state") == "open") {
              tog.setAttribute("ref-state", "closed");
            } else {
              tog.setAttribute("ref-state", "open");
            }
            change_state(tog, cnt);
          };
          change_state(tog, cnt);
          cis.push(ci);
        })(c[i]);
      }

      var ge = document.querySelectorAll(".global_expander");
      for (var i = 0; i < ge.length; i++) {
        (function(gei){
          gei.onclick = function(){
            var s = gei.getAttribute("ref-state");
            for (var j = 0; j < cis.length; j++) {
              var
                tog = cis[j].querySelector(".xsd_element_children_toggler"),
                cnt = cis[j].querySelector(".xsd_element_children_container");
              tog.setAttribute("ref-state", s);
              change_state(tog, cnt);
            }
          };
        })(ge[i]);
      }
    };
  </script>
</head>
<body>
  <div style="text-align: right;margin: 2px 6px;">
    <span class="global_expander" ref-state="open">Show all children</span> | <span class="global_expander" ref-state="closed">Collapse all children</span>
  </div>
  <div style="margin-left:20px;width:1000px;">
  <?php

    chdir("..");
    include_once "visualizer.class.php";
    $v = new XSDVis\Visualizer('example/schema.xsd');
    echo $v->draw();

  ?>
  </div>
</body>
</html>