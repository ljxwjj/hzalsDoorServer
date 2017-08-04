/**
 * 世纪奥通对外开放脚本
 */
;
! function(a) {
    var AOTDEVELOP = {
        VERSION: "1.0.0",
        FILE_SIZE_ERROR: "200",
        DEVICE: browerVersion().ios ? "IOS" : browerVersion().android ? "ANDROID" : "PC",
        ERROR_NOT_FINED: { "code": 404, "describe": "function undefined." },
        APILIST: ['getAppVersion', 'getToken', 'getUrlWithKey', 'getUserInfo', 'showUserInfo', 'chooseUsers', 'navigateTo', 'redirectTo',
            'navigateBySystem', 'addFriend', 'addTribe', 'createChat', 'showTelePhone', 'callNumber', 'previewImage', 'closeWindow', 'getEnterInfo',
            'getMyTribeList', 'getTribeMemberList', 'alert', 'confirm', 'actionSheet', 'showLoadingView', 'hideLoadingView', 'prompt',
            'downFile', 'uploadFile', 'uploadLocalFile', 'chooseImage', 'takePhoto', 'takeVideo', 'audioRecord', 'showLocation', 'getLocation',
            'getCurrentLocation', 'setNavigationBarTitle', 'setNavigationBarLeft', 'setNavigationBarRight', 'showTeleconference', 'showMyFiles',
            'showTeleconferenceRecord', 'sendSms', 'getNetworkType', 'OpenSimbaPlusAppHandler'
        ]
    }
    if (AOTDEVELOP.DEVICE === "IOS") {
        document.addEventListener("WebViewJavascriptBridgeReady", function() {
            _setNewVersion();
            aot.ready();
            aot.checkJsApi({
                "data": {
                    "jsApiList": AOTDEVELOP.APILIST
                },
                "error": function(err) {
                    AOTDEVELOP.APILIST = [];
                    //alert('error: ' + JSON.stringify(AOTDEVELOP.APILIST))
                },
                "success": function(data) {
                    AOTDEVELOP.APILIST = data.checkResult;
                    //alert('success: ' + JSON.stringify(AOTDEVELOP.APILIST))
                }
            })
        })
    } else if (AOTDEVELOP.DEVICE === "ANDROID") {
        document.addEventListener("WebViewJavascriptBridgeReady", function() {
            aot.ready();
            aot.checkJsApi({
                "data": {
                    "jsApiList": AOTDEVELOP.APILIST
                },
                "error": function(err) {
                    AOTDEVELOP.APILIST = [];
                    //alert('error: ' + JSON.stringify(AOTDEVELOP.APILIST))
                },
                "success": function(data) {
                    AOTDEVELOP.APILIST = data.checkResult;
                    //alert('success: ' + JSON.stringify(AOTDEVELOP.APILIST))
                }
            })
        })
    } else {
        window.onload = function() {
            aot.ready();
            aot.checkJsApi({
                "data": {
                    "jsApiList": AOTDEVELOP.APILIST
                },
                "error": function(err) {
                    AOTDEVELOP.APILIST = [];
                    //alert('error: ' + JSON.stringify(AOTDEVELOP.APILIST))
                },
                "success": function(data) {
                    AOTDEVELOP.APILIST = data.checkResult;
                    //alert('success: ' + JSON.stringify(AOTDEVELOP.APILIST))
                }
            })
        }
    }

    /**
     * -----------------------------------[全局接口]--------------------------------------------
     */
    /**
     * 设置IOS接口取最新版本的，仅限IOS
     * @private
     */
    function _setNewVersion() {
        if (AOTDEVELOP.DEVICE === "IOS") {
            WebViewJavascriptBridge.callHandler("setNewVersion", "", "")
        }
    }
    /**
     * -----------------------------------[全局接口]--------------------------------------------
     */

    a.aot = {
        /**
         * -----------------------------------[通用接口∨]--------------------------------------------
         */
        "ready": function() {},
        /**
         * 获取当前客户端版本号
         */
        "getAppVersion": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getAppVersion) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getAppVersion", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "version": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getAppVersion function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getAppVersion", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "version": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getAppVersion function failed."
                            });
                        }
                    })
                } else {
                    window.external.getAppVersion("", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "version": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getAppVersion function failed."
                            });
                        }
                    })
                }
            }
        },
        /**
         * 判断当前客户端版本是否支持指定JS接口
         */
        "checkJsApi": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            try {
                if (callback) {
                    if (AOTDEVELOP.DEVICE === "IOS") {
                        WebViewJavascriptBridge.callHandler("checkJsApi", callback.data, function(data) {
                            data = JSON.parse(data);
                            if (data.code == "200") {
                                data = { "code": 200, "checkResult": data.responseData };
                                callback.success(data);
                            } else {
                                callback.error({
                                    "code": data.code,
                                    "describe": data.descript || "checkJsApi function failed."
                                });
                            }
                        })
                    } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                        window.WebViewJavascriptBridge.callHandler("checkJsApi", callback.data, function(data) {
                            data = JSON.parse(data);
                            if (data.code == "200") {
                                data = { "code": 200, "checkResult": data.responseData };
                                callback.success(data);
                            } else {
                                callback.error({
                                    "code": data.code,
                                    "describe": data.descript || "checkJsApi function failed."
                                });
                            }
                        })
                    } else {
                        window.external.checkJsApi(JSON.stringify(callback.data), function(data) {
                            data = JSON.parse(data);
                            if (data.code == "200") {
                                data = { "code": 200, "checkResult": data.responseData };
                                callback.success(data);
                            } else {
                                callback.error({
                                    "code": data.code,
                                    "describe": data.descript || "checkJsApi function failed."
                                });
                            }
                        })
                    }
                }
            } catch (e) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
            }
        },
        /**
         * 获取Token
         */
        "getToken": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getToken) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getToken", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "token": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getToken function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getToken", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "token": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getToken function failed."
                            });
                        }
                    })
                } else {
                    window.external.getTokenJsSdk("", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "token": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getToken function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 获取配置在服务端的URL
         * callback.data = {
         * "entId": 10010 非必填， 有填就优先获取企业对应的url
         * "key": "keyname"
         * }
         */
        "getUrlWithKey": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getUrlWithKey) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data && !callback.data.entId) {
                    callback.data.entId = -1;
                }
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getUrlWithKey", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "url": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getUrlWithKey function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getUrlWithKey", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "url": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getUrlWithKey function failed."
                            });
                        }
                    })
                } else {
                    window.external.getUrlWithKey(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "url": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getUrlWithKey function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 获取用户信息
         */
        "getUserInfo": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getUserInfo) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getUserInfo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "userInfo": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getUserInfo function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getUserInfo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "userInfo": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getUserInfo function failed."
                            });
                        }
                    })
                } else {
                    window.external.getUserInfo(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "userInfo": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getUserInfo function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 查看用户信息
         */
        "showUserInfo": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.showUserInfo) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("showUserInfo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showUserInfo function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("showUserInfo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showUserInfo function failed."
                            });
                        }
                    })
                } else {
                    window.external.showUserInfo(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showUserInfo function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 选择联系人 支持 IOS\PC
         */
        "chooseUsers": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.chooseUsers) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("chooseUsers", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = {
                                "code": 200,
                                "userList": data.responseData.selectedUsers === undefined ? data.responseData : data.responseData.selectedUsers,
                                "departList": data.responseData.selectedDeparts === undefined ? "" : data.responseData.selectedDeparts,
                                "userCount": data.responseData.selectedUserCount === undefined ? "" : data.responseData.selectedUserCount
                            };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "chooseUsers function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("chooseUsers", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = {
                                "code": 200,
                                "userList": data.responseData.selectedUsers === undefined ? data.responseData : data.responseData.selectedUsers,
                                "departList": data.responseData.selectedDeparts === undefined ? "" : data.responseData.selectedDeparts,
                                "userCount": data.responseData.selectedUserCount === undefined ? "" : data.responseData.selectedUserCount
                            };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "chooseUsers function failed."
                            });
                        }
                    })
                } else {
                    callback.data.retInit = 1; //是否要返回传人的初始化成员，1:要 0:不要
                    window.external.chooseUsers(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "userList": data.responseData };
                            callback.success(data);
                        } else if (data.code == "202") {
                            //点击了取消按钮
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "chooseUsers function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 打开一个新窗口
         * 通过key，从urltype表取url地址，url和key都存在的时候，以url为准
         */
        "navigateTo": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.navigateTo) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data && !callback.data.url) {
                    callback.data.url = "";
                }
                if (callback.data && !callback.data.key) {
                    callback.data.key = "";
                }
                if (!callback.data.url && !callback.data.key) {
                    callback.error({
                        "code": 301,
                        "describe": "navigateTo params error."
                    });
                    return;
                }
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("navigateTo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "navigateTo function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("navigateTo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "navigateTo function failed."
                            });
                        }
                    })
                } else {
                    window.external.navigateTo(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "navigateTo function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 关闭当前页面，打开新页面
         */
        "redirectTo": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.redirectTo) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data && !callback.data.url) {
                    callback.data.url = "";
                }
                if (callback.data && !callback.data.key) {
                    callback.data.key = "";
                }
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("redirectTo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "redirectTo function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("redirectTo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "redirectTo function failed."
                            });
                        }
                    })
                } else {
                    var _this = this;
                    _this.navigateTo({
                        "data": callback.data,
                        "error": function(err) {
                            callback.error({
                                "code": err.code,
                                "describe": err.describe || "redirectTo function failed."
                            });
                        },
                        "success": function(data) {
                            _this.closeWindow({
                                "error": function(err) {
                                    callback.error({
                                        "code": err.code,
                                        "describe": err.describe || "redirectTo function failed."
                                    });
                                },
                                "success": function(data) {
                                    data = { "code": 200 };
                                    callback.success(data);
                                }
                            });
                        }
                    });
                }
            }

        },
        /**
         * 通过系统默认浏览器打开链接
         * 通过key，从urltype表取url地址，url和key都存在的时候，以url为准
         */
        "navigateBySystem": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.navigateBySystem) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data && !callback.data.url) {
                    callback.data.url = "";
                }
                if (callback.data && !callback.data.key) {
                    callback.data.key = "";
                }
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("navigateBySystem", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "navigateBySystem function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("navigateBySystem", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "navigateBySystem function failed."
                            });
                        }
                    })
                } else {
                    window.external.navigateBySystem(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "navigateBySystem function failed."
                            });
                        }
                    })
                }
            }
        },
        /**
         * 打开查找好友界面
         */
        "addFriend": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.addFriend) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data && !callback.data.key) {
                    callback.data.key = "";
                }
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("addFriend", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "addFriend function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("addFriend", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "addFriend function failed."
                            });
                        }
                    })
                } else {
                    window.external.addFriend(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "addFriend function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 打开查找群组界面
         */
        "addTribe": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.addTribe) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data && !callback.data.key) {
                    callback.data.key = "";
                }
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("addTribe", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "addTribe function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("addTribe", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "addTribe function failed."
                            });
                        }
                    })
                } else {
                    window.external.addTribe(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "addTribe function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 打开一个聊天窗口
         * 参数只有一个人，打开1对1聊天窗口
         * 参数有多人，打开讨论组
         */
        "createChat": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.createChat) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data && !callback.data.title) {
                    callback.data.title = "";
                }
                if (callback.data && !callback.data.content) {
                    callback.data.content = "";
                }
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("createChat", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "createChat function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("createChat", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "createChat function failed."
                            });
                        }
                    })
                } else {
                    window.external.createChat(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "createChat function failed."
                            });
                        }
                    })
                }
            }
        },
        /**
         * 打开内置电话拨号界面
         */
        "showTelePhone": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.showTelePhone) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("showTelePhone", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showTelePhone function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("showTelePhone", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showTelePhone function failed."
                            });
                        }
                    })
                } else {
                    window.external.showTelePhone(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showTelePhone function failed."
                            });
                        }
                    })
                }
            }
        },
        /**
         * 语音呼叫 支持IOS\PC
         */
        "callNumber": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.callNumber) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("callNumber", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "callNumber function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("callNumber", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "callNumber function failed."
                            });
                        }
                    })
                } else {
                    window.external.callNumber(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "callNumber function failed."
                            });
                        }
                    })
                }
            }
        },
        /**
         * 预览图片接口
         */
        "previewImage": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.previewImage) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("previewImage", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "previewImage function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("previewImage", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "previewImage function failed."
                            });
                        }
                    })
                } else {
                    window.external.previewImage(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "previewImage function failed."
                            });
                        }
                    })
                }
            }
        },
        /**
         * 关闭当前窗口 支持IOS\PC
         */
        "closeWindow": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.closeWindow) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("closeWindow", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "closeWindow function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("closeWindow", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "closeWindow function failed."
                            });
                        }
                    })
                } else {
                    window.external.closeWindow("", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "closeWindow function failed."
                            });
                        }
                    })
                }
            }
        },
        /**
         * 获取当前组织信息
         */
        "getEnterInfo": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getEnterInfo) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getEnterInfo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "enterInfo": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getEnterInfo function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getEnterInfo", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "enterInfo": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getEnterInfo function failed."
                            });
                        }
                    })
                } else {
                    window.external.getEnterInfo(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "enterInfo": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getEnterInfo function failed."
                            });
                        }
                    })
                }
            }
        },
        /**
         * 获取所有群信息
         */
        "getMyTribeList": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getMyTribeList) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getMyTribeList", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "tribeList": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getMyTribeList function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getMyTribeList", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "tribeList": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getMyTribeList function failed."
                            });
                        }
                    })
                } else {
                    window.external.getMyTribeList(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "tribeList": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getMyTribeList function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * 获取群成员信息
         */
        "getTribeMemberList": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getTribeMemberList) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getTribeMemberList", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "tribeMemberList": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getTribeMemberList function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getTribeMemberList", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "tribeMemberList": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getTribeMemberList function failed."
                            });
                        }
                    })
                } else {
                    window.external.getTribeMemberList(JSON.stringify(callback.data), function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "tribeMemberList": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getTribeMemberList function failed."
                            });
                        }
                    })
                }
            }

        },
        /**
         * -----------------------------------[通用接口∧]--------------------------------------------
         */
        /**
         * -----------------------------------[移动端接口∨]--------------------------------------------
         */
        /**
         * 提示框
         */
        "alert": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.alert) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("alert", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "alert function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    callback.data.isCancelableOnTouchOutside = false;
                    callback.data.isCancelable = false;
                    window.WebViewJavascriptBridge.callHandler("alert", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "alert function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 确认框
         */
        "confirm": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.confirm) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("confirm", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "index": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "confirm function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("confirm", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "index": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "confirm function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 选择框
         */
        "actionSheet": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.actionSheet) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("actionSheet", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "index": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "actionSheet function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("actionSheet", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "index": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "actionSheet function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 显示等待加载界面
         */
        "showLoadingView": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.showLoadingView) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("showLoadingView", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showPreloader function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("showLoadingView", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showPreloader function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 隐藏等待加载界面
         */
        "hideLoadingView": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.hideLoadingView) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("hideLoadingView", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "hidePreloader function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("hideLoadingView", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "hidePreloader function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 提示框（几秒后自动消失）
         */
        "prompt": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.prompt) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("prompt", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "prompt function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("prompt", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "prompt function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 下载文件 支持IOS
         */
        "downFile": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.downFile) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data.fileSize === "") delete callback.data.fileSize
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("previewFile", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "downFile function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("downFile", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "downFile function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }

        },
        /**
         * 上传文件 支持IOS
         */
        "uploadFile": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.uploadFile) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("uploadFile", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { 
                                    "code": 200, 
                                    "url": data.responseData.downUrl === undefined ? data.responseData : data.responseData.downUrl,
                                    "flag": data.responseData.flag === undefined ? "" : data.responseData.flag,
                                };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "uploadFile function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    WebViewJavascriptBridge.callHandler("uploadFile", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { 
                                    "code": 200, 
                                    "url": data.responseData.downUrl === undefined ? data.responseData : data.responseData.downUrl,
                                    "flag": data.responseData.flag === undefined ? "" : data.responseData.flag,
                                };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "uploadFile function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }

        },
        /**
         * 从本地文件或者图库中选择文件上传（是否需要）
         */
        "uploadLocalFile": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.uploadLocalFile) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("uploadLocalFile", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "url": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "uploadLocalFile function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    WebViewJavascriptBridge.callHandler("uploadLocalFile", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "url": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "uploadLocalFile function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 从手机相册中选择图片接口
         */
        "chooseImage": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.chooseImage) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("chooseImage", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "imageList": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "chooseImage function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("chooseImage", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "imageList": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "chooseImage function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 拍摄照片
         */
        "takePhoto": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.takePhoto) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("takePhoto", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "imagePath": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "takePhoto function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("takePhoto", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "imagePath": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "takePhoto function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }

        },
        /**
         * 拍摄视频
         */
        "takeVideo": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.takeVideo) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("takeVideo", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "vedioPath": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "takeVideo function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("takeVideo", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "vedioPath": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "takeVideo function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }

        },
        /**
         * 打开录音界面，录制声音
         */
        "audioRecord": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.audioRecord) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("audioRecord", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "audioPath": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "audioRecord function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("audioRecord", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "audioPath": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "audioRecord function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 使用内置地图查看指定位置接口
         */
        "showLocation": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.showLocation) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("showLocation", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showLocation function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("showLocation", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showLocation function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }

        },
        /**
         * 使用内置地图选取位置接口
         */
        "getLocation": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getLocation) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getLocation", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "location": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getLocation function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getLocation", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "location": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getLocation function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }

        },
        /**
         * 获取当前所在位置地理位置接口
         */
        "getCurrentLocation": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getCurrentLocation) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getCurrentLocation", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "location": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getCurrentLocation function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getCurrentLocation", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "location": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getCurrentLocation function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 设置导航栏标题 支持IOS
         */
        "setNavigationBarTitle": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.setNavigationBarTitle) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("setNavigationBarTitle", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "setNavigationBarTitle function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("setNavigationBarTitle", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "setNavigationBarTitle function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }

        },
        /**
         * 设置导航栏左侧标题 支持IOS
         */
        "setNavigationBarLeft": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.setNavigationBarLeft) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("setNavigationBarLeft", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "setNavigationBarLeft function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("setNavigationBarLeft", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "setNavigationBarLeft function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }

        },
        /**
         * 设置导航栏右侧 支持IOS
         */
        "setNavigationBarRight": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.setNavigationBarRight) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (callback.data.control == false) {
                    callback.data.title = "";
                }
                delete callback.data.control;
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("setNavigationBarRight", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "setNavigationBarRight function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("setNavigationBarRight", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "setNavigationBarRight function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 打开电话会议界面 支持IOS
         */
        "showTeleconference": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.showTeleconference) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("showTeleconference", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showTeleconference function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("showTeleconference", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showTeleconference function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 打开我的文件界面 支持IOS
         */
        "showMyFiles": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.showMyFiles) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("showMyFiles", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showMyFiles function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("showMyFiles", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showMyFiles function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 打开电话会议记录页面 支持IOS
         */
        "showTeleconferenceRecord": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.showTeleconferenceRecord) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("showTeleconferenceRecord", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showTeleconferenceRecord function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("showTeleconferenceRecord", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "showTeleconferenceRecord function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 打开手机短信界面发送短信 支持IOS
         */
        "sendSms": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.sendSms) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("sendSms", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "sendSms function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("sendSms", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200 };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "sendSms function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 获取网络状态 支持IOS
         */
        "getNetworkType": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.getNetworkType) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    WebViewJavascriptBridge.callHandler("getNetworkType", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "network": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getNetworkType function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("getNetworkType", "", function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "network": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "getNetworkType function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * 开启dcloud应用
         */
        "openWebApp": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
            if (!AOTDEVELOP.APILIST.OpenSimbaPlusAppHandler) {
                callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                return;
            }
            if (callback) {
                if (AOTDEVELOP.DEVICE === "IOS") {
                    window.WebViewJavascriptBridge.callHandler("OpenSimbaPlusAppHandler", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "network": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "openWebApp function failed."
                            });
                        }
                    })
                } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                    window.WebViewJavascriptBridge.callHandler("OpenSimbaPlusAppHandler", callback.data, function(data) {
                        data = JSON.parse(data);
                        if (data.code == "200") {
                            data = { "code": 200, "network": data.responseData };
                            callback.success(data);
                        } else {
                            callback.error({
                                "code": data.code,
                                "describe": data.descript || "openWebApp function failed."
                            });
                        }
                    })
                } else {
                    callback.error({
                        "code": 301,
                        "describe": "PC 暂不支持"
                    });
                }
            }
        },
        /**
         * -----------------------------------[移动端接口∧]--------------------------------------------
         */
        /**
         * -----------------------------------[仅供内部使用接口∨]--------------------------------------------
         */
        /**
         * 更新、下载组织架构
         */
        "downloadEntInfo": function() {
            var callback = checkCallBack(arguments[arguments.length - 1]);
                if (!AOTDEVELOP.APILIST.downloadEntInfo) {
                    callback.error(AOTDEVELOP.ERROR_NOT_FINED);
                    return;
                }
                if (callback) {
                    if (AOTDEVELOP.DEVICE === "IOS") {
                        WebViewJavascriptBridge.callHandler("downloadEntInfo", callback.data, function(data) {
                            data = JSON.parse(data);
                            if (data.code == "200") {
                                data = { "code": 200 };
                                callback.success(data);
                            } else {
                                callback.error({
                                    "code": data.code,
                                    "describe": data.descript || "downloadEntInfo function failed."
                                });
                            }
                        })
                    } else if (AOTDEVELOP.DEVICE === "ANDROID") {
                        window.WebViewJavascriptBridge.callHandler("downloadEntInfo", callback.data, function(data) {
                            data = JSON.parse(data);
                            if (data.code == "200") {
                                data = { "code": 200 };
                                callback.success(data);
                            } else {
                                callback.error({
                                    "code": data.code,
                                    "describe": data.descript || "downloadEntInfo function failed."
                                });
                            }
                        })
                    } else {
                        try {
                            external.ent_update(callback.data.entId);
                            data = { "code": 200 };
                            callback.success(data);
                        } catch (e) {
                            callback.error({
                                "code": 201,
                                "describe": "downloadEntInfo function failed."
                            });
                        }
                    }
                }
            }
            /**
             * -----------------------------------[仅供内部使用接口∧]--------------------------------------------
             */
    }

    /**
     * 判断设置类型
     */
    function browerVersion() {
        var u = navigator.userAgent,
            app = navigator.appVersion;
        return {
            trident: u.indexOf('Trident') > -1, //IE内核
            presto: u.indexOf('Presto') > -1, //opera内核
            webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
            gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
            mobile: !!u.match(/AppleWebKit.*Mobile.*/) || !!u.match(/AppleWebKit/), //是否为移动终端
            ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
            android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
            iPhone: u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, //是否为iPhone或者QQHD浏览器
            iPad: u.indexOf('iPad') > -1, //是否iPad
            webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部
            linux: u.indexOf('linux') > -1, //加mobile和这个属性一起，可以判断uc浏览器
            wp7: (u.indexOf('WP7') > -1) || (u.indexOf('Windows Phone OS') > -1) //trident IE内核 并且包含WP7标示 windows phone7手机
        }
    }

    /**
     * 校验回调参数
     */
    function checkCallBack(obj) {
        if (obj instanceof Object) {
            if (!(obj.success instanceof Function)) {
                console.warn("aot error: not found success callback function.")
                obj.success = function() {};
            }
            if (!(obj.error instanceof Function)) {
                console.warn("aot warn: not found error callback function.")
                obj.error = function() {};
            }
            return obj;
        } else {
            console.error("aot error: bad request params.")
            return "";
        }
    }
}(window);
