/**
 * Module for checking and adding new user device
 *
 * Required libraries: jquery.js, jquery.cookie.js, bootstrap.js, fingerprint.js, browser.js
 */
var NewDeviceManager = function(checkUrl, addUrl, showUrl) {
    "use strict";

    var fp = new Fingerprint();
    var deviceFingerPrint = fp.get();
    var cookieKey = 'DEVICE_FINGERPRINT';
    var cookie = $.cookie(cookieKey);

    /**
     * Method checks if device already been used by user
     */
    this.checkDevice = function() {
        if (deviceFingerPrint != cookie) {
            $.post(checkUrl, { fingerprint: deviceFingerPrint }, function( data ) {
                if (data && undefined !== data.assigned) {
                    if (!data.assigned) {
                        // device was not used by user, so lets ask him to add it as new
                        $('#add-new-device-modal').modal('show');
                    } else {
                        setCookie();
                    }
                } else {
                    errorHandler(data);
                }
            });
        }
    };

    /**
     * Event on handle of adding new user's device
     */
    $('#add-new-device').click(function(){
        var btn = $(this);
        btn.button('loading');

        var info = getDeviceInfo();

        $.post(addUrl, {"device": info}, function( data ) {
            if (undefined !== data && !data.error && data.id) {
                setCookie();
                window.location.href = showUrl.replace(0, data.id);
            } else {
                errorHandler(data);
            }
        });

        return false;
    });

    /**
     *
     * Method will return object with information about device
     *
     * @returns {object}
     */
    function getDeviceInfo()
    {
        return {
            'userAgent': browserInfo.userAgent,
            'browserName': browserInfo.browser.name,
            'browserVersionString': browserInfo.browser.versionString,
            'browserWidth': browserInfo.browserFeatures.window.width,
            'browserHeight': browserInfo.browserFeatures.window.height,
            'deviceScreenWidth': browserInfo.device.screen.width,
            'deviceScreenHeight': browserInfo.device.screen.height,
            'device': browserInfo.device.device || 'PC',
            'osName': browserInfo.os.name,
            'fingerprint': deviceFingerPrint
        };
    }

    /**
     * Method for setting cookie
     */
    function setCookie()
    {
        $.cookie(cookieKey, deviceFingerPrint, { expires: 365, path: '/' });
    };

    /**
     * Module error handler
     *
     * @param {object|string|any} data - object should contain error key with error message {error: 'message'}
     */
    function errorHandler(data)
    {
        var errorMessage = undefined !== data && data.error ? data.error : data;

        $('#error-modal .modal-body').text(errorMessage);
        $('#error-modal').modal('show');
    };

    return this;
};