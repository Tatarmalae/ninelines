$(function () {

    let year = $('.tabs .tab.selected').data('year');

    $(document).on('click', '.tabs .tab', function (event) {
        event.preventDefault();
        let selectedClass = 'selected';
        year = $(this).data('year');
        let data = {
            year: year
        };

        $('.tab.selected').removeClass(selectedClass);
        $(this).addClass(selectedClass);

        BX.ajax.runComponentAction(componentName, 'getNewsList', {
            mode: 'class',
            signedParameters: signedParameters,
            data: data,
        }).then(function (response) {
            $('.news-list').replaceWith(response.data);
        });
    });

    $(document).on('click', '.modern-page-navigation a', function (event) {
        event.preventDefault();
        let url = $(this).attr('href');
        let url_params = new URLSearchParams(url);
        let page = '1';

        if (url_params.get('more-news')) {
            page = url_params.get('more-news').replace('page-', '');
        }

        BX.ajax.runComponentAction(componentName, 'getNewsList', {
            mode: 'class',
            signedParameters: signedParameters,
            data: {
                year: year,
            },
            navigation: {
                page: page
            }
        }).then(function (response) {
            $('.news-list').replaceWith(response.data);
        });
    });
});