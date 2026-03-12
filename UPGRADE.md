# 从 Dcat Admin 迁移到 Appsolute AIO 升级指南

本文档适用于已有项目使用 `dcat/laravel-admin` 的开发者，帮助你平滑迁移到 `appsolutely/aio`。

---

## 目录

1. [Composer 依赖更新](#1-composer-依赖更新)
2. [命名空间替换](#2-命名空间替换)
3. [配置文件更新](#3-配置文件更新)
4. [前端资源更新](#4-前端资源更新)
5. [JavaScript 全局对象迁移](#5-javascript-全局对象迁移)
6. [CSS 类名迁移](#6-css-类名迁移)
7. [路由变更](#7-路由变更)
8. [Blade 视图更新](#8-blade-视图更新)
9. [扩展兼容性](#9-扩展兼容性)
10. [完整迁移清单](#10-完整迁移清单)

---

## 1. Composer 依赖更新

### 移除旧包，安装新包

```bash
composer remove dcat/laravel-admin
composer require appsolutely/aio
```

### 发布资源文件

```bash
# 发布所有资源（配置、迁移、静态资源、语言文件）
php artisan admin:publish --force

# 或分开发布
php artisan admin:publish --config    # 配置文件
php artisan admin:publish --assets    # 静态资源
php artisan admin:publish --lang      # 语言包
php artisan admin:publish --migrations # 数据库迁移
```

> 发布标签已从 `dcat-admin-*` 更名为 `aio-*`（如 `aio-config`、`aio-assets`、`aio-lang`、`aio-migrations`）。

---

## 2. 命名空间替换

这是迁移中最主要的改动。所有 PHP 命名空间从 `Dcat\Admin` 变更为 `Appsolutely\AIO`。

### 全局查找替换

在项目根目录中对 `app/Admin/` 及所有引用 Dcat 的文件执行：

```
Dcat\Admin\  →  Appsolutely\AIO\
```

### 常见需要修改的位置

| 旧写法 | 新写法 |
|--------|--------|
| `use Dcat\Admin\Admin;` | `use Appsolutely\AIO\Admin;` |
| `use Dcat\Admin\Form;` | `use Appsolutely\AIO\Form;` |
| `use Dcat\Admin\Grid;` | `use Appsolutely\AIO\Grid;` |
| `use Dcat\Admin\Show;` | `use Appsolutely\AIO\Show;` |
| `use Dcat\Admin\Layout\Content;` | `use Appsolutely\AIO\Layout\Content;` |
| `use Dcat\Admin\Layout\Row;` | `use Appsolutely\AIO\Layout\Row;` |
| `use Dcat\Admin\Layout\Column;` | `use Appsolutely\AIO\Layout\Column;` |
| `use Dcat\Admin\Controllers\AuthController;` | `use Appsolutely\AIO\Controllers\AuthController;` |
| `use Dcat\Admin\Models\Administrator;` | `use Appsolutely\AIO\Models\Administrator;` |
| `use Dcat\Admin\Traits\HasPermissions;` | `use Appsolutely\AIO\Traits\HasPermissions;` |
| `use Dcat\Admin\Widgets\*;` | `use Appsolutely\AIO\Widgets\*;` |
| `use Dcat\Admin\Actions\Action;` | `use Appsolutely\AIO\Actions\Action;` |
| `use Dcat\Admin\Grid\Displayers\*;` | `use Appsolutely\AIO\Grid\Displayers\*;` |
| `use Dcat\Admin\Form\Field\*;` | `use Appsolutely\AIO\Form\Field\*;` |

### 批量替换命令

```bash
# 在项目的 app/ 目录中批量替换
find app/ -name "*.php" -exec sed -i 's/Dcat\\Admin\\/Appsolutely\\AIO\\/g' {} +

# 检查是否有遗漏
grep -r "Dcat\\\\Admin" app/ --include="*.php"
```

> **注意**：不要替换外部包的命名空间，如 `Dcat\EasyExcel` 和 `Dcat\Laravel\Database`，它们是独立的第三方包。

---

## 3. 配置文件更新

配置文件名和键名基本保持不变（仍然是 `config/admin.php`，使用 `admin.*` 前缀），但需注意以下变更：

### ServiceProvider 引用

如果你的项目中有手动注册 ServiceProvider 的地方（如 `config/app.php`）：

```php
// 旧
Dcat\Admin\AdminServiceProvider::class,

// 新
Appsolutely\AIO\AdminServiceProvider::class,
```

### 应用名称（可选）

```php
// config/admin.php
'name' => 'Appsolutely AIO',
'title' => 'Appsolutely AIO',
```

---

## 4. 前端资源更新

### 资源发布路径变更

静态资源的发布目录已更改：

```
旧：public/vendor/dcat-admin/
新：public/vendor/aio/
```

运行以下命令更新资源：

```bash
# 强制重新发布所有静态资源
php artisan admin:publish --assets --force

# 清除旧的资源目录（确认无自定义资源后）
rm -rf public/vendor/dcat-admin/
```

### 自定义资源路径

如果你在代码中手动引用了资源路径，需要更新别名：

```php
// 旧
Admin::css('@admin/dcat/css/custom.css');
Admin::js('@admin/dcat/js/custom.js');

// 新
Admin::css('@admin/aio/css/custom.css');
Admin::js('@admin/aio/js/custom.js');
```

资源别名变更：

| 旧别名 | 新别名 |
|--------|--------|
| `@dcat` | `@aio` |
| `@admin/dcat/` | `@admin/aio/` |

---

## 5. JavaScript 全局对象迁移

JavaScript 全局对象已从 `Dcat` 更名为 `AIO`。

### 代码中的 JS 引用

```javascript
// 旧
Dcat.ready(function () { ... });
Dcat.init('.selector', function () { ... });
Dcat.confirm('确认?', function () { ... });
Dcat.success('操作成功');
Dcat.error('操作失败');
Dcat.handleJson(response);
Dcat.NP.start();

// 新
AIO.ready(function () { ... });
AIO.init('.selector', function () { ... });
AIO.confirm('确认?', function () { ... });
AIO.success('操作成功');
AIO.error('操作失败');
AIO.handleJson(response);
AIO.NP.start();
```

### PHP 中内联 JS 的引用

```php
// 旧
Admin::script("Dcat.ready(function() { ... })");

// 新
Admin::script("AIO.ready(function() { ... })");
```

### 全局构造函数

```javascript
// 旧
var Dcat = CreateDcat({...});

// 新
var AIO = CreateAIO({...});
```

### 事件命名空间

```javascript
// 旧
$(document).on('dcat:init', function() { ... });
$(document).on('dcat:waiting', function() { ... });

// 新
$(document).on('aio:init', function() { ... });
$(document).on('aio:waiting', function() { ... });
```

### 批量替换

```bash
# 在 app/Admin/ 和 resources/views/ 中替换 JS 引用
find app/ resources/views/ -type f \( -name "*.php" -o -name "*.blade.php" -o -name "*.js" \) \
  -exec sed -i 's/\bDcat\./AIO./g' {} + \
  -exec sed -i 's/\bDcat\.ready/AIO.ready/g' {} + \
  -exec sed -i 's/CreateDcat/CreateAIO/g' {} +
```

---

## 6. CSS 类名迁移

CSS 类名前缀从 `dcat-` 更改为 `aio-`。

### 常见类名变更

| 旧类名 | 新类名 |
|--------|--------|
| `.dcat-box` | `.aio-box` |
| `.dcat-loading` | `.aio-loading` |
| `.dcat-body` | `.aio-body` |
| `.dcat-step` | `.aio-step` |
| `.dcat-tooltip` | `.aio-tooltip` |
| `.dcat-slider-*` | `.aio-slider-*` |
| `.dcat-exception-trace` | `.aio-exception-trace` |

### 如果你有自定义样式

检查自定义 CSS/SCSS 中是否引用了 `.dcat-` 开头的类名：

```bash
grep -r "dcat-" resources/views/ --include="*.blade.php"
grep -r "dcat-" public/css/ --include="*.css"
grep -r "\.dcat-" resources/sass/ --include="*.scss"
```

---

## 7. 路由变更

### API 路由前缀

内部 API 路由前缀已更改：

```
旧：{admin_prefix}/dcat-api/*
新：{admin_prefix}/aio-api/*
```

如果你的前端代码中有硬编码的 API 路径，需要更新：

```javascript
// 旧
$.post('/admin/dcat-api/action', data);

// 新
$.post('/admin/aio-api/action', data);
```

> **提示**：推荐使用 `AIO.helpers.action` 或路由生成方法，而非硬编码路径。

### 路由名称前缀

```php
// 旧
route('dcat-api.action');

// 新
route('aio-api.action');
```

---

## 8. Blade 视图更新

如果你覆盖了（override）任何 admin 视图，需要更新其中的引用：

```blade
{{-- 旧 --}}
var Dcat = CreateDcat({!! Dcat\Admin\Admin::jsVariables() !!});
Dcat.boot();

{{-- 新 --}}
var AIO = CreateAIO({!! Appsolutely\AIO\Admin::jsVariables() !!});
AIO.boot();
```

检查你的自定义视图：

```bash
grep -r "Dcat" resources/views/admin/ --include="*.blade.php"
grep -r "dcat" resources/views/admin/ --include="*.blade.php"
```

---

## 9. 扩展兼容性

### Dcat Admin 扩展

现有的 Dcat Admin 扩展 **不能直接使用**，需要：

1. 更新扩展中的命名空间引用
2. 更新 JS 全局对象引用（`Dcat` → `AIO`）
3. 更新 CSS 类名（`dcat-` → `aio-`）
4. 更新资源路径别名

### 自定义扩展 bootstrap

```php
// 旧
use Dcat\Admin\Admin;
use Dcat\Admin\Extend\ServiceProvider;

// 新
use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Extend\ServiceProvider;
```

### 外部 Composer 包（无需修改）

以下包仍然独立存在，**不需要**修改引用：

- `dcat/easy-excel` — Excel 导入导出
- `dcat/laravel-database` — 数据库增强（WhereHasIn、SoftDeletes 等）

---

## 10. 完整迁移清单

按顺序执行以下步骤：

- [ ] **备份项目**（数据库 + 代码）
- [ ] `composer remove dcat/laravel-admin`
- [ ] `composer require appsolutely/aio`
- [ ] 替换 PHP 命名空间：`Dcat\Admin\` → `Appsolutely\AIO\`
- [ ] 更新 ServiceProvider 注册（如有手动注册）
- [ ] `php artisan admin:publish --force`
- [ ] 删除旧资源目录：`rm -rf public/vendor/dcat-admin/`
- [ ] 替换 JS 全局对象：`Dcat.` → `AIO.`、`CreateDcat` → `CreateAIO`
- [ ] 替换 CSS 类名：`.dcat-` → `.aio-`
- [ ] 替换资源路径：`@admin/dcat/` → `@admin/aio/`、`@dcat` → `@aio`
- [ ] 替换 API 路由：`dcat-api` → `aio-api`（如有硬编码）
- [ ] 更新自定义 Blade 视图中的引用
- [ ] 更新扩展中的引用
- [ ] `php artisan view:clear`
- [ ] `php artisan cache:clear`
- [ ] 测试所有功能

---

## 快速验证

迁移完成后，运行以下命令确认没有遗漏（排除第三方包）：

```bash
# 检查 PHP 文件中是否还有 Dcat\Admin 引用
grep -r "Dcat\\\\Admin" app/ config/ routes/ --include="*.php"

# 检查 Blade 视图中是否还有 Dcat 引用
grep -r "\bDcat\b" resources/views/ --include="*.blade.php"

# 检查 JS 中是否还有 Dcat 引用
grep -r "\bDcat\b" public/js/ --include="*.js"

# 检查 CSS 中是否还有 dcat- 类名
grep -r "\.dcat-" resources/ public/ --include="*.css" --include="*.scss"
```

如果以上命令没有输出结果（排除 `dcat/easy-excel` 和 `dcat/laravel-database` 的引用），说明迁移已完成。
