/*=========================================================================================
  File Name: app.ts
  Description: Appsolutely AIO JS脚本.
  ----------------------------------------------------------------------------------------
  Item Name: Appsolutely AIO
  Author: Jqh
  Author URL: https://github.com/jqhph
==========================================================================================*/

import AIO from './AIO';

import NProgress from './shims/nprogress';
import Ajax from './extensions/Ajax';
import Toastr from './extensions/Toastr';
import SweetAlert2 from './extensions/SweetAlert2';
import RowSelector from './extensions/RowSelector';
import Grid from './extensions/Grid';
import Form from './extensions/Form';
import DialogForm from './extensions/DialogForm';
import Loading from './extensions/Loading';
import AssetsLoader from './extensions/AssetsLoader';
import Slider from './extensions/Slider';
import Color from './extensions/Color';
import Validator from './extensions/Validator';
import DarkMode from './extensions/DarkMode';

import Menu from './bootstrappers/Menu';
import Footer from './bootstrappers/Footer';
import Pjax from './bootstrappers/Pjax';
import DataActions from './bootstrappers/DataActions';

let win = window as any,
    $ = jQuery;

// 扩展AIO对象
function extend(aio: AIO): void {
    let inst = aio as unknown as AIOInstance;
    // ajax处理相关扩展函数
    new Ajax(inst);
    // Toastr简化使用函数
    new Toastr(inst);
    // SweetAlert2简化使用函数
    new SweetAlert2(inst);
    // Grid相关功能函数
    new Grid(inst);
    // loading效果
    new Loading(inst);
    // 静态资源加载器
    new AssetsLoader(inst);
    // 颜色管理
    new Color(inst);
    // 表单验证器
    new Validator(inst);
    // 黑色主题切换
    new DarkMode(inst);

    // 加载进度条
    (aio as any).NP = NProgress;

    // 行选择器
    (aio as any).RowSelector = function (options: Record<string, unknown>) {
        return new RowSelector(options);
    };

    // ajax表单提交
    (aio as any).Form = function (options: Record<string, unknown>) {
        return new Form(options);
    };

    // 弹窗表单
    (aio as any).DialogForm = function (options: Record<string, unknown>) {
        return new DialogForm(inst, options);
    };

    // 滑动面板
    (aio as any).Slider = function (options: Record<string, unknown>) {
        return new Slider(inst, options);
    };
}

// 初始化
function listen(aio: AIO): void {
    // 只初始化一次
    aio.booting(() => {
        (aio as any).NP.configure({ parent: '.app-content' });

        // layer弹窗设置
        layer.config({ maxmin: true, moveOut: true, shade: false });

        //////////////////////////////////////////////////////////

        // 菜单点击选中效果
        new Menu(aio);
        // 返回顶部按钮
        new Footer(aio);
        // data-action 动作绑定(包括删除、批量删除等操作)
        new DataActions(aio);
    });

    // 每个请求都初始化
    aio.bootingEveryRequest(() => {
        // ajax全局设置
        $.ajaxSetup({
            cache: true,
            error: (aio as any).handleAjaxError,
            headers: {
                'X-CSRF-TOKEN': aio.token as string,
            },
        });
        // pjax初始化功能
        new Pjax(aio);
    });
}

function prepare(aio: AIO): AIO {
    extend(aio);
    listen(aio);

    return aio;
}

/**
 * @returns {AIO}
 */
win.CreateAIO = function (config: Record<string, unknown>): AIO {
    return prepare(new AIO(config));
};
