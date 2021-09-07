// Call the dataTables jQuery plugin
$(document).ready(function() {
  $('#dataTable').DataTable( {
    "lengthMenu": [ [-1, 10, 25, 50], ["All", 10, 25, 50] ],
    "scrollX": true,
    "columnDefs": [
      { "orderable": false, "targets": 9 }
    ]
  });

  $('#gridTable').DataTable( {
    "lengthMenu": [ [-1], ['All'] ],
    "dom": 'ltip',
    "scrollX": true,
    "ordering": false
  });
});

