jQuery(document).ready(function($) {
  'use strict';

  var wpat = {}
    , el = {}
    ;
  
  el = {
    wpatDataTable: $('.wpat-wrap table.data'),
    switches: $('.wpat-wrap .aux-toolbar .switches button'),
    loadingMessage: $('.loading-message')
  };
   
  wpat.initDataTables = function() {
    el.wpatDataTable.dataTable({
      "aaSorting": [[ 0, "desc" ]],
      "iDisplayLength": 50,
      "sScrollX": "100%"
    });
    $(window).on('load',function() {
      el.loadingMessage.remove();
      $('.not-loaded').removeClass('not-loaded');
    });
  };

  wpat.subsGetMore = function () {
    var key
      ;

    $.ajax({
      url: window.location.pathname,
      type: "GET",
      data: {
        "wpat_jqdt_sub_total": "1"
      },
      dataType: "json",
      success: function(total) {

        total = (Math.ceil(total / 50));
        //console.log(total);

        for ( key = 1 ; key < total ; key++ ) {
          (function (i) {
            $.ajax({
              url: window.location.pathname,
              type: "GET",
              data: {
                "wpat_jqdt_sub_offset": String(i * 50)
              },
              async: true,
              dataType: "json",
              success: function(data) {
                el.wpatDataTable.dataTable().fnAddData(data);
              }
            });
          })(key);
        }

      }
    });
  };

  wpat.transGetMore = function () {
    var key
      ;

    $.ajax({
      url: window.location.pathname,
      type: "GET",
      data: {
        "wpat_jqdt_trans_total": "1"
      },
      dataType: "json",
      success: function(total) {

        total = (Math.ceil(total / 50));
        //console.log(total);

        for ( key = 1 ; key < total ; key++ ) {
          (function (i) {
            $.ajax({
              url: window.location.pathname,
              type: "GET",
              data: {
                "wpat_jqdt_trans_offset": String(i * 50)
              },
              async: true,
              dataType: "json",
              success: function(data) {
                el.wpatDataTable.dataTable().fnAddData(data);
              }
            });
          })(key);
        }

      }
    });
  };

  wpat.fnShowHide = function(iCol) {
    var oTable = el.wpatDataTable.dataTable();
    var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
    oTable.fnSetColumnVis( iCol, bVis ? false : true );
  };

  wpat.initColumnToggle = function() {
    el.switches.each(function() {
      if ( $(this).hasClass('active') === false ) {
        var dCol = $(this).attr('id').replace('column-','');
        wpat.fnShowHide(dCol);
      }
    });
    el.switches.click(function() {
      $(this).toggleClass('active');
      var iCol = $(this).attr('id').replace('column-','');
      wpat.fnShowHide(iCol);
      return false;
    });
  };

  wpat.initDataTables();
  wpat.initColumnToggle();
  if ( $('body').hasClass('arb_page_wpat-subscriptions') ) {
    wpat.subsGetMore();
  }
  if ( $('body').hasClass('arb_page_wpat-transactions') ) {
    wpat.transGetMore();
  }

  $(window).bind('load', function () {
    
  });

});