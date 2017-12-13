require(['jquery'],function($){
    $(document).ready(function () {
        // sorting reviews data
        $('.ekomi_reviews_sort').on('change', function (e) {
            prc_filter = this.value;
            prc_page_offset = 1;

            var data = {
                product_id: prc_product_id,
                limit: prc_limit,
                offset_page: prc_page_offset,
                filter_type: prc_filter
            };

            $.ajax({
                type: "POST",
                url: prc_ajax_url + 'load',
                data: data,
                cache: false,
                success: function (data) {
                    var json = $.parseJSON(data);
                    $('#ekomi_reviews_container').html(json.reviews_data.result);

                    // reset the page offset
                    product_review_count = json.reviews_data.count;
                    $('.loads_more_reviews').show();
                }
            });
        });

        // Loading reviews on paginatin
        $('body').on('click', '.loads_more_reviews', function (e) {
            prc_page_offset = (product_review_count/prc_limit) +1;

            if (total_product_review_count / product_review_count > 1) {
                var data = {
                    product_id: prc_product_id,
                    limit: prc_limit,
                    offset_page: prc_page_offset,
                    filter_type: prc_filter
                };

                $.ajax({
                    type: "POST",
                    url: prc_ajax_url + 'load',
                    data: data,
                    cache: false,
                    success: function (data) {
                        var json = $.parseJSON(data);

                        product_review_count = product_review_count + json.reviews_data.count;
                        $('#ekomi_reviews_container').append(json.reviews_data.result);
                        $('.current_review_batch').text(product_review_count);

                        if (total_product_review_count / product_review_count <= 1) {
                            $('.loads_more_reviews').hide();
                        }
                    }
                });
            } else {
                $('.loads_more_reviews').hide();
            }
        });

        // saving users feedback on reviews
        $('body').on('click', '.ekomi_review_helpful_button', function () {
            var current = $(this);

            var data = {
                product_id: prc_product_id,
                review_id: $(this).data('review-id'),
                helpfulness: $(this).data('review-helpfulness')
            };

            $.ajax({
                type: "POST",
                url: prc_ajax_url + 'save',
                data: data,
                cache: false,
                success: function (data) {
                    var json = $.parseJSON(data);

                    current.parent('.ekomi_review_helpful_question').hide();
                    current.parent().prev('.ekomi_review_helpful_thankyou').show();
                    current.parent().prev().prev('.ekomi_review_helpful_info').html(json.message);
                    current.parent().prev().prev('.ekomi_review_helpful_info').show();
                }
            });
        });
    });
});
