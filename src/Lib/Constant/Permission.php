<?php

namespace App\Lib\Constant;

class Permission
{
    const DEFAULT_PERMISSION_ICON = "<span class='text-warning fa fa-lock'></span> ";

    const UN_LOGIN = [
        Route::LOGIN_INDEX,
        Route::LOGIN_SUBMIT,
        Route::LOG_OUT,
        Route::FOS_JS_ROUTING_JS,
    ];

    /** @var int 默认权限 */
    const DEFAULT_PERMISSION = 1;

    /** @var int 非默认权限 */
    const UN_DEFAULT_PERMISSION = 0;

    /** @var int 菜单树权限 */
    const MENU_PERMISSION = 1;

    /** @var int 非菜单树权限 */
    const UN_MENU_PERMISSION = 0;
}