# Contributing Guideline

Welcome to contributing to Blessing Skin!

## Development

### Environment Setup

Please make sure you have installed the tools below:

- [Git](https://git-scm.org)
- [Node.js](https://nodejs.org)
- [Yarn](https://yarnpkg.com)
- [Composer](https://getcomposer.org)

And run the commands to pull the code:

```bash
git clone https://github.com/bs-community/blessing-skin-server.git
cd blessing-skin-server
composer install
yarn
```

Then run `cp .env.example .env`. Don't forget to configure the `.env` file.

### Developing

Front-end code need to be built before running Blessing Skin.

If the `APP_ENV` is `development` in `.env` file, you need to run `yarn dev` and keep that process running. That will let front-end resource be loaded correctly, and the page will gain the ability of hot reload.

If the `APP_ENV` equals to other value, you need to run `yarn build` first. This operation will build and compress the front-end resource. Generally, this should be used for production.

### Testing

Test front-end code:

```bash
yarn test
```

Test PHP code:

```bash
./vendor/bin/phpunit
```

## Code Convention

Please make sure you have installed EditorConfig extension/plugin in your editor or IDE. It's recommended to install the plugin for ESLint if you are developing front-end. (You can also run `yarn lint` to check the code.)

# 贡献指南

欢迎您为 Blessing Skin 作出贡献！

## 开发

### 环境设置

首先确保您安装好以下工具：

- [Git](https://git-scm.org)
- [Node.js](https://nodejs.org)
- [Yarn](https://yarnpkg.com)
- [Composer](https://getcomposer.org)

然后执行以下命令来拉取代码：

```bash
git clone https://github.com/bs-community/blessing-skin-server.git
cd blessing-skin-server
composer install
yarn
```

然后执行 `cp .env.example .env`，并在 `.env` 中配置好您的环境信息。

### 进行开发

运行 Blessing Skin 前，前端代码需要并构建。

当 `.env` 中的 `APP_ENV` 为 `development` 时，您需要先执行 `yarn dev` 并保持此进程的运行。这样 Blessing Skin 的前端资源才能被正确加载，同时使页面带有热重载功能。

当 `APP_ENV` 为其它值时，您需要事先执行 `yarn build`。此命令将构建并压缩前端资源。通常用于生产环境。

### 测试

进行前端测试：

```bash
yarn test
```

进行 PHP 代码测试：

```bash
./vendor/bin/phpunit
```

## 代码规范

请确保您的编辑器或 IDE 安装好 EditorConfig 插件。如果进行前端开发，推荐安装上 ESLint 插件。（您也可以通过执行 `yarn lint` 进行检查）
