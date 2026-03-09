# 集成 Vditor Markdown 编辑器

本文档记录将 [Vditor](https://b3log.org/vditor/) 集成进 dcat-admin 的完整步骤，供日后维护或集成其他编辑器时参考。

## 背景

原有的 `markdown` 字段使用的是 [editor.md](https://github.com/pandao/editor.md)（v1.5.0），该库自 2016 年起基本停止维护，存在已知 bug（语言文件在 `window.editormd` 未加载时会抛出 `TypeError`）。Vditor 是国人开发的活跃维护项目，支持分屏预览、所见即所得、即时渲染三种模式，且对中文环境友好。

新的 `vditor` 字段与原 `markdown` 字段**并存**，不破坏现有代码。

---

## 集成步骤

### 1. 安装 npm 包并复制静态资源

```bash
npm install vditor --save-dev
```

将 Vditor 的 dist 文件复制到项目的静态资源目录，**必须放在 `vditor/dist/` 子目录下**：

```bash
cp -r node_modules/vditor/dist resources/dist/dcat/plugins/vditor/dist
cp -r node_modules/vditor/dist resources/assets/dcat/plugins/vditor/dist
```

`resources/dist` 是发布给使用者的静态资源，`resources/assets` 是源文件目录，两者保持同步。

> **路径陷阱**：Vditor 的 `cdn` 选项指定一个**基础目录**，它在内部会自动追加 `/dist/js/...`、`/dist/css/...` 等路径来加载 KaTeX、highlight.js 等子资源。
>
> 如果把 dist 内容直接复制到 `vditor/`（而不是 `vditor/dist/`），`cdn` 指向 `vditor/` 时，子资源请求会落在 `vditor/dist/js/...`，但实际文件只在 `vditor/js/...`，导致所有子资源 404。
>
> 正确结构：
> ```
> resources/dist/dcat/plugins/vditor/          ← cdn 指向这里
> └── dist/
>     ├── index.min.js                          ← 主 JS（别名路径）
>     ├── index.css                             ← 主 CSS（别名路径）
>     ├── js/
>     │   ├── katex/
>     │   ├── highlight.js/
>     │   ├── mermaid/
>     │   └── ...
>     └── css/
> ```

---

### 2. 注册资源别名

**文件**：`src/Layout/Asset.php`

在 `$alias` 数组中添加 `@vditor` 别名：

```php
'@vditor' => [
    'js'  => '@admin/dcat/plugins/vditor/index.min.js',
    'css' => '@admin/dcat/plugins/vditor/index.css',
],
```

别名通过 `Admin::requireAssets('@vditor')` 使用，框架会自动解析为正确的 URL。

---

### 3. 注册图片上传路由

**文件**：`src/Admin.php`，`registerApiRoutes()` 方法

```php
$router->post('vditor/upload', 'VditorController@upload')->name('vditor.upload');
```

路由前缀为 `dcat-api/`，完整路径为 `{admin_prefix}/dcat-api/vditor/upload`。路由名称通过 `admin_api_route_name('vditor.upload')` 获取。

---

### 4. 创建图片上传控制器

**文件**：`src/Http/Controllers/VditorController.php`

Vditor 的上传接口与 editor.md 不同，要求返回固定格式：

```json
{
    "msg": "",
    "code": 0,
    "data": {
        "errFiles": [],
        "succMap": {
            "原始文件名": "https://cdn.example.com/path/to/file.jpg"
        }
    }
}
```

上传字段名为 `file[]`（支持多文件）。控制器从请求中读取 `disk` 和 `dir` 参数，与 `EditorMDController` 的设计保持一致，方便复用存储配置。

---

### 5. 创建字段类

**文件**：`src/Form/Field/Vditor.php`

继承 `Field` 基类，提供以下公共方法：

| 方法 | 说明 |
|------|------|
| `height(int $height)` | 设置编辑器高度，默认 500px |
| `mode(string $mode)` | 编辑模式：`sv`（分屏）、`wysiwyg`（所见即所得）、`ir`（即时渲染），默认 `sv` |
| `disk(string $disk)` | 指定 Laravel Storage disk |
| `imageDirectory(string $dir)` | 图片上传目录，默认 `vditor/images` |
| `imageUrl(string $url)` | 自定义上传接口 URL |

`render()` 方法负责：
1. 将 `cdn` 选项设为本地资源路径（`admin_asset('@admin/dcat/plugins/vditor')`）
2. 根据 `config('app.locale')` 自动映射 Vditor 语言包
3. 注入默认上传配置（URL、字段名、多文件支持）
4. 调用 `Admin::requireAssets('@vditor')` 加载 JS/CSS

支持的语言映射：

```php
$map = [
    'zh_CN' => 'zh_CN',
    'zh_TW' => 'zh_TW',
    'en'    => 'en_US',
    'ja'    => 'ja_JP',
    'ko'    => 'ko_KR',
    'ru'    => 'ru_RU',
];
```

---

### 6. 创建视图

**文件**：`resources/views/form/vditor.blade.php`

editor.md 的视图直接用带 `name` 属性的 `<textarea>` 提交数据，而 Vditor 自行管理 DOM，无法使用同样的方式。解决方案是用一个隐藏的 `<input type="hidden">` 作为表单提交载体，通过 Vditor 的两个回调同步内容：

- `input(val)`：每次编辑时同步到隐藏 input
- `after()`：编辑器初始化完成后，将初始值写入隐藏 input（避免用户未编辑就提交时丢失原始值）

```html
<div id="{{ $id }}"></div>
<input type="hidden" name="{{ $name }}" id="{{ $id }}_val" value="{{ $value ?? '' }}">
```

```js
var _vditor_xxx = new Vditor('{{ $id }}', {
    ...options,
    input: function (val) {
        document.getElementById('{{ $id }}_val').value = val;
    },
    after: function () {
        // 必须用 setValue() 而不是 options.value 来设置初始内容，
        // 否则表单验证失败重回页面后内容会丢失。
        if (initialValue) {
            _vditor_xxx.setValue(initialValue);
        }
        document.getElementById('{{ $id }}_val').value = initialValue;
    },
});
```

> **注意**：不要直接用 `options.value = initialValue` 的方式注入初始内容。Vditor 在某些模式下会忽略这个值，必须在 `after()` 回调中调用实例的 `setValue()` 方法。这也是为什么需要把 `new Vditor(...)` 的返回值赋给一个变量。
>
> 隐藏 input 也需要设置 `value` 属性（服务端渲染），否则用户不编辑直接提交时表单里会是空值。

---

### 7. 注册字段

**文件**：`src/Form.php`

在 `$availableFields` 数组中添加：

```php
'vditor' => Field\Vditor::class,
```

在类顶部的 PHPDoc 中添加方法注解（供 IDE 自动补全）：

```php
 * @method Field\Vditor vditor($column, $label = '')
```

---

## 使用方式

```php
// 基本用法（分屏预览模式）
$form->vditor('content', '内容');

// 所见即所得模式
$form->vditor('content', '内容')->mode('wysiwyg');

// 即时渲染模式
$form->vditor('content', '内容')->mode('ir');

// 自定义高度
$form->vditor('content', '内容')->height(800);

// 自定义图片上传目录
$form->vditor('content', '内容')->imageDirectory('articles/images');

// 指定存储 disk
$form->vditor('content', '内容')->disk('s3');

// 链式调用
$form->vditor('content', '内容')
    ->mode('wysiwyg')
    ->height(600)
    ->disk('public')
    ->imageDirectory('posts/images');
```

---

## 涉及文件清单

| 操作 | 文件 |
|------|------|
| 新增 | `src/Form/Field/Vditor.php` |
| 新增 | `src/Http/Controllers/VditorController.php` |
| 新增 | `resources/views/form/vditor.blade.php` |
| 新增 | `resources/dist/dcat/plugins/vditor/`（整个 dist 目录） |
| 新增 | `resources/assets/dcat/plugins/vditor/`（整个 dist 目录） |
| 修改 | `src/Admin.php`：注册上传路由 |
| 修改 | `src/Form.php`：注册字段类和 PHPDoc |
| 修改 | `src/Layout/Asset.php`：注册 `@vditor` 资源别名 |

---

## 与 editor.md 的对比

| 项目 | editor.md | Vditor |
|------|-----------|--------|
| 最后维护 | ~2016 | 持续活跃 |
| 编辑模式 | 分屏预览 | sv / wysiwyg / ir |
| 图片上传响应格式 | `{success: 1, url: '...'}` | `{code: 0, data: {succMap: {...}}}` |
| 上传字段名 | `editormd-image-file` | `file[]` |
| 表单数据提交 | 直接用 `<textarea name="...">` | 隐藏 `<input>` 同步 |
| 离线支持 | 是 | 是（本地 cdn） |
| 中文支持 | 有语言文件 | 内置，原生支持 |
