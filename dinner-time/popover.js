$('#attendance').editable({
    type: 'date',
    url: './date_updater.php',
    title: 'Next attendance',
    format: 'yyyy-mm-dd',    
    viewformat: 'dd/mm/yyyy',
    placement: 'bottom',
    showbuttons: 'bottom',
    clear: false,
    display: false,
    success: function(response, newValue) {
        window.location.reload();
    }
});