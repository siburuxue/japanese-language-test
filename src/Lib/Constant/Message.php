<?php

namespace App\Lib\Constant;

class Message
{
    /** @var string 登陆错误信息 */
    const LOGIN_ERROR_MSG = "用户名或密码不正确";

    /** @var string 用户名已存在 */
    const USERNAME_EXIST_MSG = "用户名已存在";

    /** @var string 登陆成功 */
    const LOGIN_SUCCESS_MSG = "登陆成功";

    /** @var string 更新成功 */
    const INSERT_SUCCESS = "添加成功";

    /** @var string 更新失败 */
    const INSERT_FAILED = "添加失败";

    /** @var string 更新成功 */
    const UPDATE_SUCCESS = "更新成功";

    /** @var string 更新失败 */
    const UPDATE_FAILED = "更新失败";

    /** @var string 更新成功 */
    const SAVE_SUCCESS = "保存成功";

    /** @var string 更新失败 */
    const SAVE_FAILED = "保存失败";

    /** @var string 删除成功 */
    const DELETE_SUCCESS = "删除成功";

    /** @var string 删除失败 */
    const DELETE_FAILED = "删除失败";
}