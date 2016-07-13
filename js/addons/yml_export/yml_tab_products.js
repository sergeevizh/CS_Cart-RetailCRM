(function(_, $){

    $(document).ready(function(){

        $(_.doc).on('change', '#yml2_offer_type', function(event){
            var model_type = $(this).val();

            var is_parent = $('#yml2_parent_offer_val').val() == 'vendor' || $('#yml2_parent_offer_val').val() == 'apparel';

            if (model_type == "vendor" || model_type == "apparel" || (model_type == '' && is_parent)) {
                $('#yml2_model_div').removeAttr('disabled').show();
                $('#yml2_type_prefix_div').removeAttr('disabled').show();

            } else {
                $('#yml2_model_div').attr('disabled', 'disabled').hide();
                $('#yml2_type_prefix_div').attr('disabled', 'disabled').hide();
            }
        });

        $('#yml2_offer_type').trigger('change');
    });

})(Tygh, Tygh.$);