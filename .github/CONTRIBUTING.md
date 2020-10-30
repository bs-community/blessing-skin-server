# 贡献指南

欢迎您为 Blessing Skin 作出贡献！

## 分支约定

不管是直接 push 代码还是提交 Pull Request，都必须使 commit 指向 `dev` 分支。

## 开发

### 环境设置

首先确保您安装好以下工具：

- [Git](https://git-scm.org)
- [Node.js](https://nodejs.org)
- [Yarn](https://yarnpkg.com)
- [Composer](https://getcomposer.org)
- [PowerShell Core](https://github.com/PowerShell/PowerShell#get-powershell)

然后执行以下命令：

```bash
git clone https://github.com/bs-community/blessing-skin-server.git
cd blessing-skin-server
composer install
cp .env.example .env
php artisan key:generate
yarn
```

然后在 `.env` 中配置好您的环境信息，务必设置好 `ASSET_URL`，否则无法编译前端资源。

### 进行开发

运行 Blessing Skin 前，前端代码需要并构建。

当 `.env` 中的 `APP_ENV` 为 `development` 时，您需要先执行 `yarn dev` 并保持此进程的运行。这样 Blessing Skin 的前端资源才能被正确加载，同时使页面带有热重载功能。（有时热重载可能会失效，此时需要您手动刷新页面）

另外，在运行 `yarn dev` 即运行 `webpack-dev-server` 时，由于 `webpack-dev-server` 的端口往往与 Blessing Skin 的端口不同，因此有可能导致热重载失败。此时可以在 Nginx 中添加以下配置：

```
location ~* \w+\.hot-update\.json$ {
    rewrite (\w+\.hot-update\.json)$ /$1 break;
    proxy_pass http://$host:8080;
}
```

当 `APP_ENV` 为其它值时，您需要事先执行 `pwsh ./tools/build.ps1`。此命令将构建并压缩前端资源。通常用于生产环境。

> 如果传递 `-Simple` 参数给 `build.ps1` 脚本，则只会运行 webpack 来编译代码，而不会复制首页背景以及生成 commit 信息。

### 测试

进行前端测试：

```bash
yarn test
```

请尽量保证前端测试的覆盖率为 100%。

进行 PHP 代码测试：

```bash
./vendor/bin/phpunit
```

## 代码规范

请确保您的编辑器或 IDE 安装好 EditorConfig 插件。如果进行前端开发，推荐安装上 ESLint 插件。（您也可以通过执行 `yarn lint` 进行检查）

## 发布

> 本节仅针对本项目的维护成员。

首先请确保您当前处于 `dev` 分支。然后，运行 `yarn new-version <action>` 即可发布新版本，不需要其它人工操作。

其中 `action` 参数是必需的，且只能为 `patch`、`minor`、`major` 中的其中一个。

另外，可以不定期地将 `dev` 上的 commits 合并到 `master` 分支，以满足一些想尝鲜的用户。但尽管如此，这不意味着 `dev` 分支是随意的—— `dev` 分支上的功能、特性可以是未完成的，但不应该影响用户的使用，因为也允许用户使用 `dev` 分支上的代码去体验新特性。
