# AIO 前端开发调试指南

## 背景

AIO 作为 Laravel 包，PHP 代码通过 composer path repository（symlink）与 site 项目联动，改动即时生效。但前端资源（JS/CSS/插件）需要经过 build → publish 才能在 site 中看到效果，开发体验较差。

本文档介绍几种方案来改善前端调试流程。

## 当前架构

```
aio/
├── resources/
│   ├── assets/          # 源文件（SCSS、TS、插件）
│   ├── pre-dist/        # dev 构建产物
│   ├── dist/            # production 构建产物
│   └── views/           # Blade 模板（symlink 直接生效）
├── build.ts             # Vite 构建脚本
└── package.json

site/
├── public/
│   └── vendors/aio/     # publish 后的前端资源（从 dist 复制过来）
└── vendor/
    └── appsolutely/aio  # symlink → ../../aio
```

**构建命令：**

| 命令 | 输出目录 | 用途 |
|------|----------|------|
| `npm run dev` | `resources/pre-dist` | 开发环境，含 sourcemap |
| `npm run build` | `resources/dist` | 生产环境 |

**发布命令：**

```bash
# 在 site 项目中执行
php artisan vendor:publish --tag=aio-assets --force
```

这会把 `aio/resources/dist` 复制到 site 的 `public/` 对应目录。

## 方案一：Symlink 替代 Publish（推荐，最快生效）

核心思路：把 site 中 publish 出来的静态资源目录替换为 symlink，直接指向 AIO 的构建产物。

### 步骤

1. 确认 site 中 publish 的目标路径：

```bash
# 在 site 项目中查看 Asset 类解析的路径
php artisan tinker --execute="echo public_path(\Admin::asset()->getRealPath('@admin'))"
```

假设输出为 `public/vendors/dcat-admin`（以实际为准，下文用 `$ASSET_PATH` 代替）。

2. 删除已 publish 的资源，替换为 symlink：

```bash
cd /Volumes/Data/Projects/appsolutely/site

# 备份（可选）
mv $ASSET_PATH ${ASSET_PATH}.bak

# 开发环境 symlink 到 pre-dist
ln -s /Volumes/Data/Projects/appsolutely/aio/resources/pre-dist $ASSET_PATH
```

3. 在 AIO 项目中构建：

```bash
cd /Volumes/Data/Projects/appsolutely/aio
npm run dev
```

4. 刷新浏览器即可看到最新效果。

### 切回生产模式

```bash
rm $ASSET_PATH
php artisan vendor:publish --tag=aio-assets --force
```

## 方案二：添加 Watch 模式（配合方案一使用）

在 `build.ts` 中增加 `--watch` 支持，文件变动时自动重新编译。

### 实现思路

在 `package.json` 中添加：

```json
{
  "scripts": {
    "watch": "NODE_ENV=development tsx build.ts --watch"
  }
}
```

在 `build.ts` 中检测 `--watch` 参数，对 JS/TS entry 使用 Vite 的 watch 模式：

```typescript
import { build, type InlineConfig } from 'vite';

const isWatch = process.argv.includes('--watch');

// 在 buildAll() 中，对 JS entry 的 build 调用增加 watch 配置：
await build({
    // ...existing config
    build: {
        // ...existing build config
        watch: isWatch ? {} : null,
    },
});
```

### 使用

```bash
cd /Volumes/Data/Projects/appsolutely/aio
npm run watch
```

配合方案一的 symlink，改 TS/SCSS → 自动编译 → 刷新浏览器即可。

## 方案三：ServiceProvider 开发环境自动切换路径

让 AIO 在开发环境下自动从包目录读取前端资源，无需 publish 或手动 symlink。

### 实现思路

在 `AdminServiceProvider::registerPublishing()` 中添加开发环境判断：

```php
protected function registerPublishing(): void
{
    if ($this->app->runningInConsole()) {
        $this->publishes(
            [__DIR__.'/../resources/dist' => public_path(Admin::asset()->getRealPath('@admin'))],
            'aio-assets'
        );
    }
}

protected function bootDevAssets(): void
{
    // 开发环境下，将 asset 基础路径指向包的 pre-dist 目录
    if ($this->app->isLocal() && file_exists(__DIR__.'/../resources/pre-dist')) {
        Admin::asset()->basePath(__DIR__.'/../resources/pre-dist');
    }
}
```

这需要 `Asset` 类支持动态切换基础路径，改动较大，但一劳永逸。

## 各方案对比

| | 方案一：Symlink | 方案二：Watch | 方案三：Provider 切换 |
|---|---|---|---|
| 改动量 | 零代码改动 | 改 build.ts + package.json | 改 ServiceProvider + Asset 类 |
| 生效速度 | 手动 build 后即时 | 自动 build 后即时 | 自动 build 后即时 |
| 团队协作 | 每人手动设置一次 | 统一命令 | 零配置 |
| 推荐场景 | 立刻开始用 | 日常开发 | 长期维护 |

## 推荐组合

**日常开发：方案一 + 方案二**

1. Site 端建好 symlink（一次性）
2. AIO 端跑 `npm run watch`
3. 改代码 → 自动编译 → 刷新浏览器

后续如果团队扩大，再投入方案三做到零配置。

## 其他注意事项

- **新增 helper 文件**：如果 AIO 的 `composer.json` 中 `autoload.files` 新增了条目，site 需要执行 `composer dump-autoload`
- **新增 composer 依赖**：AIO 加了新的 `require`，site 需要执行 `composer update appsolutely/aio`
- **Blade views**：通过 `loadViewsFrom` 注册，symlink 下直接生效，无需额外处理
- **Config/Migration 变更**：需要重新 `vendor:publish` 对应的 tag
