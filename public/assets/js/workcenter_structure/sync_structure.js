function sync() {

    $.ajax({
        type: "GET",
        url: "{{ route('workcenter/sync') }}",
        success: function(result) {

        }
    });

}
