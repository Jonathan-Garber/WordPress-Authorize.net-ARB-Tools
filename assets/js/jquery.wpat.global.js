jQuery(document).ready(function($) {
  
  var wpat = {
    el: {
      wpatDataTable: $('.wpat-wrap table.data'),
      switches: $('.wpat-wrap .aux-toolbar .switches button'),
      loadingMessage: $('.loading-message')
    },
    initDataTables: function() {
      this.el.wpatDataTable.dataTable({
        "aaSorting": [[ 0, "desc" ]],
        "iDisplayLength": 50,
        "sScrollX": "100%"
      });
      $(window).on('load',function() {
        wpat.el.loadingMessage.remove();
        $('.not-loaded').removeClass('not-loaded');
      });
    },
    fnShowHide: function(iCol) {
      var oTable = wpat.el.wpatDataTable.dataTable();
      var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
      oTable.fnSetColumnVis( iCol, bVis ? false : true );
    },
    initColumnToggle: function() {
      this.el.switches.each(function() {
        if ( $(this).hasClass('active') == false ) {
          var dCol = $(this).attr('id').replace('column-','');
          wpat.fnShowHide(dCol);
        }
      });
      this.el.switches.click(function() {
        $(this).toggleClass('active');
        var iCol = $(this).attr('id').replace('column-','');
        wpat.fnShowHide(iCol);
        return false;
      });
    }
  };

  wpat.initDataTables();
  wpat.initColumnToggle();

});