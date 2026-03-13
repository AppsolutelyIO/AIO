# Appsolute AIO 测试方案

## 现状分析

当前测试覆盖非常有限：
- `tests/Feature/InstallTest.php` — 仅验证安装目录是否存在
- `tests/Feature/SectionTest.php` — 仅验证 Section 注入功能
- `tests/Browser/` — Dusk 浏览器测试（依赖完整 Laravel 应用 + Chrome）

**缺失**：没有任何 Unit 测试，Feature 测试仅覆盖 2 个功能点。

---

## 测试架构设计

```
tests/
├── TestCase.php                    # 基础测试类（已有，需改造）
├── Unit/                           # 单元测试（新增）
│   ├── Support/
│   │   ├── HelperTest.php          # 工具函数测试
│   │   └── HelpersFunctionTest.php # 全局 helper 函数测试
│   ├── Layout/
│   │   ├── AssetTest.php           # 资源管理测试
│   │   ├── MenuTest.php            # 菜单构建测试
│   │   └── SectionManagerTest.php  # Section 管理测试
│   ├── Grid/
│   │   ├── ColumnTest.php          # 列定义与显示器
│   │   ├── FilterTest.php          # 过滤器测试
│   │   ├── ModelTest.php           # Grid Model 查询构建
│   │   └── RowTest.php             # 行数据测试
│   ├── Form/
│   │   ├── FieldTest.php           # 表单字段测试
│   │   ├── BuilderTest.php         # 表单构建测试
│   │   ├── NestedFormTest.php      # 嵌套表单测试
│   │   └── ValidationTest.php      # 表单验证测试
│   ├── Show/
│   │   ├── FieldTest.php           # Show 字段测试
│   │   └── PanelTest.php           # 面板测试
│   ├── Actions/
│   │   ├── ActionTest.php          # Action 基类测试
│   │   └── ResponseTest.php        # Action 响应测试
│   ├── Widgets/
│   │   ├── CardTest.php            # 卡片组件
│   │   ├── TableTest.php           # 表格组件
│   │   ├── ModalTest.php           # 弹窗组件
│   │   ├── AlertTest.php           # 提示组件
│   │   └── FormWidgetTest.php      # 表单组件
│   ├── Repositories/
│   │   ├── EloquentRepositoryTest.php
│   │   └── QueryBuilderRepositoryTest.php
│   ├── ColorTest.php               # 颜色工具测试
│   └── TreeTest.php                # 树形组件测试
├── Feature/                        # 功能测试（扩展）
│   ├── InstallTest.php             # 已有
│   ├── SectionTest.php             # 已有
│   ├── AdminTest.php               # Admin 核心功能
│   ├── AuthTest.php                # 认证 & 权限
│   ├── GridCrudTest.php            # Grid CRUD 操作
│   ├── FormCrudTest.php            # Form CRUD 操作
│   ├── RouteTest.php               # 路由注册 & API 路由
│   ├── AssetPublishTest.php        # 资源发布测试
│   ├── ExtensionTest.php           # 扩展管理测试
│   ├── MenuTest.php                # 菜单管理测试
│   └── ExportTest.php              # 数据导出测试
└── Browser/                        # 浏览器测试（已有）
```

---

## 分阶段实施计划

### 第一阶段：基础设施 + 纯单元测试（无需数据库）

**目标**：覆盖不依赖 Laravel 容器的纯逻辑代码。

**优先级最高**，因为这些测试运行快、无依赖、容易维护。

#### 1.1 添加 phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./src</directory>
        </include>
    </source>
</phpunit>
```

#### 1.2 创建独立的 Unit TestCase

```php
// tests/Unit/TestCase.php
namespace Appsolutely\AIO\Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // 纯 PHPUnit，无 Laravel 依赖
}
```

#### 1.3 测试文件清单

| 测试文件 | 被测类 | 测试内容 |
|---------|--------|---------|
| `Unit/Support/HelperTest.php` | `Support\Helper` | `slug()`, `buildHtmlAttributes()`, `buildNestedArray()`, `colorLighten()`, `colorDarken()`, `colorAlpha()`, `deleteByValue()`, `strLimit()`, `equal()`, `inArray()`, `basename()`, `urlWithQuery()`, `urlWithoutQuery()` |
| `Unit/ColorTest.php` | `Color` | 颜色转换、主题色生成 |
| `Unit/Actions/ResponseTest.php` | `Actions\Response` | 响应构建（success/error/redirect/script） |
| `Unit/Grid/RowTest.php` | `Grid\Row` | 行数据访问与操作 |

**预计测试数量**：30-40 个 test case

---

### 第二阶段：需要 Laravel 容器的单元测试

**目标**：使用 `orchestra/testbench` 提供 Laravel 测试环境，无需完整应用。

#### 2.1 安装 testbench

```bash
composer require --dev orchestra/testbench
```

#### 2.2 创建 Integration TestCase

```php
// tests/Integration/TestCase.php
namespace Appsolutely\AIO\Tests\Integration;

use Appsolutely\AIO\AdminServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [AdminServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
```

#### 2.3 测试文件清单

| 测试文件 | 被测类 | 测试内容 |
|---------|--------|---------|
| `Unit/Layout/AssetTest.php` | `Layout\Asset` | 资源路径注册、别名解析 `@aio` → 实际路径、JS/CSS 集合管理、`scriptToHtml()` 输出包含 `AIO.ready` |
| `Unit/Layout/SectionManagerTest.php` | `Layout\SectionManager` | 注入/覆盖/追加/排序逻辑（独立于 helper 函数） |
| `Unit/Grid/ColumnTest.php` | `Grid\Column` | 列定义、displayer 注册、`aio-tooltip` class 输出 |
| `Unit/Grid/FilterTest.php` | `Grid\Filter` | 各种 Filter 类型（Equal, Like, Between, In 等） |
| `Unit/Form/FieldTest.php` | `Form\Field` | 字段渲染、属性设置、默认值、placeholder |
| `Unit/Form/ValidationTest.php` | `Form\Field` | 验证规则绑定与执行 |
| `Unit/Widgets/CardTest.php` | `Widgets\Card` | 卡片组件 HTML 输出 |
| `Unit/Widgets/TableTest.php` | `Widgets\Table` | 表格组件数据渲染 |
| `Unit/Widgets/AlertTest.php` | `Widgets\Alert` | 各级别提示信息输出 |
| `Unit/Widgets/ModalTest.php` | `Widgets\Modal` | 弹窗渲染与按钮生成 |
| `Unit/Repositories/EloquentRepositoryTest.php` | `Repositories\EloquentRepository` | 基本 CRUD 查询构建 |
| `Unit/TreeTest.php` | `Tree` | 树形数据构建 |

**预计测试数量**：60-80 个 test case

---

### 第三阶段：Feature 测试（HTTP 请求级别）

**目标**：测试完整的 HTTP 请求/响应流程。

#### 3.1 测试文件清单

| 测试文件 | 测试内容 |
|---------|---------|
| `Feature/RouteTest.php` | API 路由 `api/*` 是否正确注册、路由名称前缀 `api.` 是否正确 |
| `Feature/AdminTest.php` | `Admin::css()`, `Admin::js()`, `Admin::script()` 输出、`jsVariables()` 包含正确配置 |
| `Feature/AuthTest.php` | 登录/登出、未登录重定向、权限中间件拦截 |
| `Feature/GridCrudTest.php` | Grid 列表页渲染、分页、搜索、排序、导出、批量删除 |
| `Feature/FormCrudTest.php` | 创建/编辑表单渲染、提交保存、验证失败回显 |
| `Feature/MenuTest.php` | 菜单 CRUD、菜单排序、菜单缓存 |
| `Feature/AssetPublishTest.php` | `admin:publish` 命令执行后文件正确生成 |
| `Feature/ExtensionTest.php` | 扩展启用/禁用、扩展配置读写 |
| `Feature/ExportTest.php` | Excel 导出（需 mock `dcat/easy-excel`） |

**预计测试数量**：40-60 个 test case

---

### 第四阶段：迁移专项回归测试

**目标**：确保 dcat → aio 重命名没有遗漏，防止回归。

```php
// tests/Unit/MigrationRegressionTest.php

class MigrationRegressionTest extends TestCase
{
    /**
     * 确保项目自身代码中不再有 dcat 引用
     * (排除第三方包引用和 README)
     */
    public function test_no_dcat_references_in_js_output()
    {
        // 验证 Asset 类输出的路径全部使用 @aio
        $asset = new Asset();
        $html = $asset->scriptToHtml();
        $this->assertStringNotContainsString('Dcat.', $html);
        $this->assertStringContainsString('AIO.ready', $html);
    }

    public function test_api_routes_use_aio_prefix()
    {
        // 验证 API 路由使用 api 前缀
        $this->assertStringContainsString('api', admin_api_route_name('action'));
    }

    public function test_css_classes_use_aio_prefix()
    {
        // 验证 Grid 输出的 CSS class 使用 aio- 前缀
        $grid = new Grid(new TestModel());
        $html = $grid->render();
        $this->assertStringNotContainsString('dcat-box', $html);
    }

    public function test_js_global_object_is_aio()
    {
        // 验证 layout 输出 CreateAIO 而非 CreateDcat
        $jsVars = Admin::jsVariables();
        // 渲染 layout 验证全局对象名
    }

    public function test_asset_aliases_use_aio()
    {
        $asset = Admin::asset();
        // 验证 @aio 别名存在且指向正确
    }

    public function test_dist_files_no_dcat_references()
    {
        $distPath = __DIR__.'/../../resources/dist/aio/';
        $files = ['js/aio-app.js', 'extra/action.js', 'extra/grid-extend.js'];

        foreach ($files as $file) {
            $content = file_get_contents($distPath . $file);
            // 排除 sourcemap 注释
            $this->assertStringNotContainsString('Dcat', $content,
                "Found 'Dcat' in dist file: {$file}");
            $this->assertStringNotContainsString('dcat-', $content,
                "Found 'dcat-' in dist file: {$file}");
        }
    }
}
```

**预计测试数量**：10-15 个 test case

---

## 推荐实施顺序与优先级

```
优先级    阶段              工作量    价值
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
★★★★★   第一阶段（纯单元）   1-2 天   高（运行快，无依赖）
★★★★☆   第四阶段（回归）     0.5 天   高（防止迁移遗漏）
★★★★☆   第二阶段（集成）     2-3 天   高（覆盖核心组件）
★★★☆☆   第三阶段（功能）     3-5 天   中（需要完整环境）
```

**建议先做第一阶段 + 第四阶段**，这两个加起来 1.5-2.5 天可以完成，能立刻获得：
- 纯函数逻辑的安全保障
- 迁移完整性的自动化验证

---

## 运行测试

```bash
# 运行所有测试
composer test

# 仅运行单元测试
vendor/bin/phpunit --testsuite Unit

# 仅运行功能测试
vendor/bin/phpunit --testsuite Feature

# 运行特定测试组
vendor/bin/phpunit --group migration-regression

# 生成覆盖率报告
vendor/bin/phpunit --coverage-html coverage/
```

---

## 关键依赖

| 包 | 用途 | 安装命令 |
|----|------|---------|
| `phpunit/phpunit` | 测试框架 | 已安装 |
| `orchestra/testbench` | Laravel 包测试环境 | `composer require --dev orchestra/testbench` |
| `mockery/mockery` | Mock 工具 | 已安装 |
| `fakerphp/faker` | 测试数据生成 | 已安装 |
