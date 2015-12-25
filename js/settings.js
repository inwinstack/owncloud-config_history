(function (OC, window, $, undefined) {
'use strict';

$(document).ready(function() {
    var OCConfigurationHistory = {};

    OCConfigurationHistory.init = function() {
        OCConfigurationHistory.View.lessBtn.hide();
        OCConfigurationHistory.View.noMoreMsg.hide();
        OCConfigurationHistory.View.noMsg.hide();
        OCConfigurationHistory.View.loading.hide();
        OCConfigurationHistory.Operation.getActivities();
    };

    OCConfigurationHistory.Filter = {
        filter: 'configuration_history',
        currentPage: 0,
        pageSize: 5,
    };

    OCConfigurationHistory.Operation = {
        getActivities: function() {
            OCConfigurationHistory.Filter.currentPage++;

            OCConfigurationHistory.View.loading.show();
            OCConfigurationHistory.View.moreBtn.attr({disabled: 'disabled'});
            
            $.ajax({
                url:OC.generateUrl('/apps/config_history/fetch'),
                method:'GET',
                data: {
                    filter: OCConfigurationHistory.Filter.filter,
                    page: OCConfigurationHistory.Filter.currentPage,
                },
            })
            .done(function(data) {
                if(data.length == 0 && OCConfigurationHistory.Filter.currentPage == 1) {
                    OCConfigurationHistory.View.noMsg.show();
                    OCConfigurationHistory.View.moreBtn.hide();
                }
                else if(data.length < OCConfigurationHistory.Filter.pageSize && OCConfigurationHistory.Filter.currentPage == 1) {
                    OCConfigurationHistory.View.moreBtn.hide();
                    OCConfigurationHistory.View.noMoreMsg.hide();
                }
                else if(data.length < OCConfigurationHistory.Filter.pageSize) {
                    OCConfigurationHistory.View.moreBtn.hide();
                    OCConfigurationHistory.View.noMoreMsg.show();
                }

                if(data.length < OCConfigurationHistory.Filter.pageSize && OCConfigurationHistory.Filter.currentPage == 2) {
                    OCConfigurationHistory.View.lessBtn.hide();
                }

                OCConfigurationHistory.Operation.appendContent(data);
            });

            if(OCConfigurationHistory.Filter.currentPage > 1) {
                OCConfigurationHistory.View.lessBtn.show();
            }
        },

        appendContent: function(activities) {
            $.each(activities, function(key, activity) {
                var date = new Date(activity.timestamp*1000);
                var row = $('<tr>');
                var historyTd = $('<td>');
                var dateTd = $('<td>');

                date = date.toLocaleDateString() + ' ' + date.toString().match(/\d\d:\d\d:\d\d/);
                historyTd.html(activity.subjectformatted.full);
                historyTd.addClass('config-message');
                dateTd.text(date);
                dateTd.addClass('date');

                row.append(historyTd);
                row.append(dateTd);
                OCConfigurationHistory.View.content.append(row);
            });

            OCConfigurationHistory.View.loading.hide();
            OCConfigurationHistory.View.moreBtn.removeAttr('disabled');
        },

        getMore: function() {
            OCConfigurationHistory.Operation.getActivities();
        },

        showLess: function() {
            OCConfigurationHistory.Filter.currentPage--;
            OCConfigurationHistory.View.content.find('tr').slice(OCConfigurationHistory.Filter.pageSize * OCConfigurationHistory.Filter.currentPage).remove();
            if(OCConfigurationHistory.Filter.currentPage === 1) {
                OCConfigurationHistory.View.lessBtn.hide();
            }

            OCConfigurationHistory.View.moreBtn.show();
            OCConfigurationHistory.View.noMoreMsg.hide();
        },
    };

    OCConfigurationHistory.View = {
        content: $('#configuration_history'),
        loading: $('#loading_configuration'),
        moreBtn: $('#morehistory'),
        lessBtn: $('#lesshistory'),
        noMoreMsg: $('#nomoremsg'),
        noMsg: $('#nomsg'),
    };


    OCConfigurationHistory.init();

    OCConfigurationHistory.View.moreBtn.on('click', function() {
        OCConfigurationHistory.Operation.getMore();
    });

    OCConfigurationHistory.View.lessBtn.on('click', function() {
        OCConfigurationHistory.Operation.showLess();
    });
});
})(OC, window, jQuery);
