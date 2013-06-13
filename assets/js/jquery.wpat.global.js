jQuery(document).ready(function($) {
  
  var wpat = {
    el: {
      transactionsTable: $('.wpat-transactions table'),
      switches: $('.wpat-transactions .aux-toolbar .switches button')
    },
    initDataTables: function() {
      this.el.transactionsTable.dataTable({
        "aaSorting": [[ 0, "desc" ]],
        "iDisplayLength": 50,
        "sScrollX": "100%"
      });
    },
    fnShowHide: function(iCol) {
      var oTable = wpat.el.transactionsTable.dataTable();
      var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
      oTable.fnSetColumnVis( iCol, bVis ? false : true );
    },
    transactionsTableColumnToggle: function() {
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
  wpat.transactionsTableColumnToggle();

});