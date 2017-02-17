//jQuery = jQuery.noConflict();

jQuery(document).ready(function () {
    // sorting reviews data
    jQuery('.ekomi_reviews_sort').on('change', function (e) {
        prc_filter = this.value;
        prc_page_offset = 1;

        var data = {
            product_id: prc_product_id,
            limit: prc_limit,
            offset_page: prc_page_offset,
            filter_type: prc_filter
        };

        jQuery.ajax({
            type: "POST",
            url: prc_ajax_url + 'load',
            data: data,
            cache: false,
            success: function (data) {
                var json = jQuery.parseJSON(data);
                jQuery('#ekomi_reviews_container').html(json.reviews_data.result);

                // reset the page offset
                product_review_count = json.reviews_data.count;
                jQuery('.loads_more_reviews').show();
            }
        });
    });

    // Loading reviews on paginatin
    jQuery('body').on('click', '.loads_more_reviews', function (e) {
        prc_page_offset = (product_review_count/prc_limit) +1;

        if (total_product_review_count / product_review_count > 1) {
            var data = {
                product_id: prc_product_id,
                limit: prc_limit,
                offset_page: prc_page_offset,
                filter_type: prc_filter
            };

            jQuery.ajax({
                type: "POST",
                url: prc_ajax_url + 'load',
                data: data,
                cache: false,
                success: function (data) {
                    var json = jQuery.parseJSON(data);

                    product_review_count = product_review_count + json.reviews_data.count;
                    jQuery('#ekomi_reviews_container').append(json.reviews_data.result);
                    jQuery('.current_review_batch').text(product_review_count);

                    if (total_product_review_count / product_review_count <= 1) {
                        jQuery('.loads_more_reviews').hide();
                    }
                }
            });
        } else {
            jQuery('.loads_more_reviews').hide();
        }
    });

    // saving users feedback on reviews
    jQuery('body').on('click', '.ekomi_review_helpful_button', function () {
        var current = jQuery(this);

        var data = {
            product_id: prc_product_id,
            review_id: jQuery(this).data('review-id'),
            helpfulness: jQuery(this).data('review-helpfulness')
        };

        jQuery.ajax({
            type: "POST",
            url: prc_ajax_url + 'save',
            data: data,
            cache: false,
            success: function (data) {
                var json = jQuery.parseJSON(data);

                current.parent('.ekomi_review_helpful_question').hide();
                current.parent().prev('.ekomi_review_helpful_thankyou').show();
                current.parent().prev().prev('.ekomi_review_helpful_info').html(json.message);
                current.parent().prev().prev('.ekomi_review_helpful_info').show();
            }
        });
    });
});
