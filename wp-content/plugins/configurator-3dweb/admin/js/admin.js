jQuery(function ($) {
    window.CNF_3DWEB_ADMIN = [];
    window.CNF_3DWEB_ADMIN.sync = function (endPoint, values, method) {
        method = method || 'post';
        const defaultValues = {
            _ajax_nonce: jsvUpload['security'],
            action: endPoint,
        }
        return new Promise((resolve, reject) => {
            if(method === 'post') {
                $.post(jsvUpload['ajaxUrl'], {...defaultValues, ...values}, function (response) {
                    if (response['success'] === true) {
                        resolve(response)
                    } else {
                        reject(response)
                    }
                }, "json");
            }
            else{
                $.get(jsvUpload['ajaxUrl'], {...defaultValues, ...values}, function (response) {
                    if (response['success'] === true) {
                        resolve(response)
                    } else {
                        reject(response)
                    }
                }, "json");
            }
        })
    }

    $('body').on('click', '#cnf-save-settings', function (e) {
        e.preventDefault();
        $(e.target).html('saving..')
        const form = $(this).parent().parent().find('form');
        const endPoint = form.data('source');
        const data = form.serializeArray().reduce(function (obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        const handleError = (data) => {
            $(e.target).html('error when saving');
            setTimeout(() => {
                $(e.target).html('save');
            }, 2000)

            let errorText = '<ul>';
            $.each(data.data, function (key, val) {
                if (val.error) {
                    errorText += `<li>${val.error}</li>`;
                }
            });
            errorText += '</ul>';
            $(e.target).parent().find('#cnf-save-settings-error').html(errorText);
        }

        const handleSuccess = (data) => {
            $(e.target).html('saved!');
            setTimeout(() => {
                $(e.target).html('save');
            }, 2000)
            $(e.target).parent().find('#cnf-save-settings-error').html('');
        }

        window.CNF_3DWEB_ADMIN.sync(endPoint, data).then((data) => {

            if (data.success) {
                handleSuccess(data);
            } else {
                handleError(data)
            }
        }).catch((error) => {
            handleError(error);
            console.warn(error)
        });
    });

    // Test credentials button
    $('body').on('click', '#cnf-test-auth', function (e) {
        e.preventDefault();
        const $btn = $(e.target);
        const $result = $('#cnf-3dweb__check-auth-result');
        $btn.html('testen...');
        $result.removeClass('cnf-3dweb__error').removeClass('cnf-3dweb__success').html('');

        window.CNF_3DWEB_ADMIN
            .sync('cnf-3dweb-check-auth', {}, 'get')
            .then((res) => {
                $btn.html('Test credentials');
                console.log(res);
                const teamName = (res && res.data && res.data.data && res.data.data.team && res.data.data.team.name)
                    ? res.data.data.team.name
                    : null;
                const msg = teamName
                    ? `successfully connected to ${teamName}`
                    : (res.data && res.data.message ? res.data.message : 'Authenticatie gelukt.');
                $result.addClass('cnf-3dweb__success').html(msg);
            })
            .catch((err) => {
                $btn.html('Probeer opnieuw');
                const msg = (err && err.data && err.data.message) ? err.data.message : (err.message || 'Authenticatie mislukt.');
                $result.addClass('cnf-3dweb__error').html(msg);
                console.warn(err);
            });
    });
});