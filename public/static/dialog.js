dialog = {
    /**
     *  展示成功信息
     *
     * @param msg
     */
    showSuccess: function (msg) {
        layer.open({
            title: '操作提示',
            icom: 1,
            content: msg
        });
    },

    /**
     *  显示正确提示，然后跳转到指定的地址
     *
     * @param msg
     * @param url
     */
    successAuto: function (msg, url) {
        layer.open({
            title: '操作提示',
            icom: 1,
            content: msg,
            yes: function () {
                window.location.href = url;
            }
        });
    },

    /**
     *  展示错误信息
     *
     * @param msg
     */
    showError: function (msg) {
        layer.open({
            title: '操作提示',
            icon: 2,
            content: msg,
            time: 3000
        });
    },

    /**
     *
     *  展示成功信息，然后跳转到目标地址
     *
     * @param msg
     * @param url
     */
    successTo: function (msg, url) {
        layer.open({
            title: '操作提示',
            icon: 1,
            content: msg,
            yes: function () {
                window.location.href = url;
            }
        });
    },

    /**
     *  展示错误信息，然后跳转到指定之地
     *
     * @param msg
     * @param url
     */
    errorTo: function (msg, url) {
        layer.open({
            title: '操作提示',
            icon: 2,
            content: msg,
            yes: function () {
                window.location.href = url;
            }
        });
    }
};