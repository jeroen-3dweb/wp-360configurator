jQuery(function ($) {
    window.DWEB_PS_ADMIN = [];
    window.DWEB_PS_ADMIN.sync = function (endPoint, values, method) {
        if (typeof jsvUpload === 'undefined') {
            return Promise.reject('DWEB_PS_ADMIN: jsvUpload is not defined');
        }
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

    $('body').on('click', '#dweb_ps-save-settings', function (e) {
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
            $(e.target).parent().find('#dweb_ps__save-settings-error').html(errorText);
        }

        const handleSuccess = (data) => {
            $(e.target).html('saved!');
            setTimeout(() => {
                $(e.target).html('save');
            }, 2000)
            $(e.target).parent().find('#dweb_ps__save-settings-error').html('');
        }

        window.DWEB_PS_ADMIN.sync(endPoint, data).then((data) => {

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
    $('body').on('click', '#dweb_ps-test-auth', function (e) {
        e.preventDefault();
        const $btn = $(e.target);
        const $result = $('#dweb_ps__check-auth-result');
        $btn.html('testing...');
        $result.removeClass('dweb_ps__error').removeClass('dweb_ps__success').html('');

        window.DWEB_PS_ADMIN
            .sync('dweb_ps-check-auth', {}, 'get')
            .then((res) => {
                console.log(res);
                $btn.html('Test credentials');
                const teamName = (res && res.data  && res.data.team && res.data.team.name)
                    ? res.data.team.name
                    : null;
                const msg = teamName
                    ? `successfully connected to ${teamName}`
                    : (res.data && res.data.message ? res.data.message : res.data.message);
                $result.addClass('dweb_ps__success').html(msg);
            })
            .catch((err) => {
                $btn.html('Try again');
                const msg = (err && err.data && err.data.message) ? err.data.message : (err.message || 'Authentication failed.');
                $result.addClass('dweb_ps__error').html(msg);
                console.warn(err);
            });
    });
});