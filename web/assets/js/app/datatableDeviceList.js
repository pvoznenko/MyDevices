define(['jquery', 'datatables', 'bootstrap', 'datatablesBootstrap'], function($){

    return {
        getDatatable: function(sortinColumn, notSortinColumn) {
            $('.datatable').dataTable({
                sPaginationType: "bs_full",
                "aaSorting": [[ sortinColumn, "desc" ]],
                aoColumnDefs: [{ "aTargets": [ notSortinColumn ], "bSortable": false }]
            });

            var dataTable = $('.datatable');

            var searchInput = dataTable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
            searchInput.attr('placeholder', 'Search').addClass('form-control input-sm');

            var lengthSel = dataTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
            lengthSel.addClass('form-control input-sm');
        }
    }
});