
/*=========================================================================================
  File Name: app.js
  Description: Appsolutely AIO JS脚本.
  ----------------------------------------------------------------------------------------
  Item Name: Appsolutely AIO
  Author: Jqh
  Author URL: https://github.com/jqhph
==========================================================================================*/

import AIO from './AIO'

import NProgress from './NProgress/NProgress.min'
import Ajax from './extensions/Ajax'
import Toastr from './extensions/Toastr'
import SweetAlert2 from './extensions/SweetAlert2'
import RowSelector from './extensions/RowSelector'
import Grid from './extensions/Grid'
import Form from './extensions/Form'
import DialogForm from './extensions/DialogForm'
import Loading from './extensions/Loading'
import AssetsLoader from './extensions/AssetsLoader'
import Slider from './extensions/Slider'
import Color from './extensions/Color'
import Validator from './extensions/Validator'
import DarkMode from './extensions/DarkMode'

import Menu from './bootstrappers/Menu'
import Footer from './bootstrappers/Footer'
import Pjax from './bootstrappers/Pjax'
import DataActions from './bootstrappers/DataActions'

let win = window,
    $ = jQuery;

// 扩展AIO对象
function extend (AIO) {
    // ajax处理相关扩展函数
    new Ajax(AIO);
    // Toastr简化使用函数
    new Toastr(AIO);
    // SweetAlert2简化使用函数
    new SweetAlert2(AIO);
    // Grid相关功能函数
    new Grid(AIO);
    // loading效果
    new Loading(AIO);
    // 静态资源加载器
    new AssetsLoader(AIO);
    // 颜色管理
    new Color(AIO);
    // 表单验证器
    new Validator(AIO);
    // 黑色主题切换
    new DarkMode(AIO);

    // 加载进度条
    AIO.NP = NProgress;

    // 行选择器
    AIO.RowSelector = function (options) {
        return new RowSelector(options)
    };

    // ajax表单提交
    AIO.Form = function (options) {
        return new Form(options)
    };

    // 弹窗表单
    AIO.DialogForm = function (options) {
        return new DialogForm(AIO, options);
    };

    // 滑动面板
    AIO.Slider = function (options) {
        return new Slider(AIO, options)
    };
}

// 初始化
function listen(AIO) {
    // 只初始化一次
    AIO.booting(() => {
        AIO.NP.configure({parent: '.app-content'});

        // layer弹窗设置
        layer.config({maxmin: true, moveOut: true, shade: false});

        //////////////////////////////////////////////////////////

        // 菜单点击选中效果
        new Menu(AIO);
        // 返回顶部按钮
        new Footer(AIO);
        // data-action 动作绑定(包括删除、批量删除等操作)
        new DataActions(AIO);
    });

    // 每个请求都初始化
    AIO.bootingEveryRequest(() => {
        // ajax全局设置
        $.ajaxSetup({
            cache: true,
            error: AIO.handleAjaxError,
            headers: {
                'X-CSRF-TOKEN': AIO.token
            }
        });
        // pjax初始化功能
        new Pjax(AIO);
    });
}

function prepare(AIO) {
    extend(AIO);
    listen(AIO);

    return AIO;
}

/**
 * @returns {AIO}
 */
win.CreateAIO = function(config) {
    return prepare(new AIO(config));
};
