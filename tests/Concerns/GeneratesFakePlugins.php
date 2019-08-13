<?php

namespace Tests\Concerns;

use ZipArchive;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait GeneratesFakePlugins
{
    /**
     * Generate fake content of a plugin's package.json.
     *
     * @param array $info
     * @return array
     */
    protected function generateFakePlguinInfo($info = [])
    {
        return array_replace([
            'name' => Str::random(10),
            'version' => '0.0.'.rand(1, 9),
            'title' => Str::random(20),
            'description' => Str::random(60),
            'author' => Arr::get($info, 'author', Str::random(10)),
            'url' => 'https://'.Str::random(10).'.test',
            'namespace' => Str::random(10),
            'require' => [
                'blessing-skin-server' => '^3.4.0 || ^4.0.0',
            ],
        ], $info);
    }

    /**
     * Generate plugin information for plugins registry (with "dist" field).
     *
     * @param array $info
     * @return array
     */
    protected function generateFakePluginsRegistryPackage($info = [])
    {
        return $this->generateFakePlguinInfo(array_replace([
            'dist' => [
                'type' => 'zip',
                'url' => 'https://plugins-registry.test/'.Str::random(10).'.zip',
                'shasum' => strtolower(Str::random(40)),
            ],
        ], $info));
    }

    /**
     * Generate fake content of a plugins registry.
     * You can also pass two arguments (name and version) as a shortcut.
     * If no argument is passed, we will randomly generate 10 fake plugins.
     *
     * @param array $plugins An array of plugin information.
     * @return string        JSON encoded content.
     */
    protected function generateFakePluginsRegistry($plugins = [])
    {
        if (func_num_args() == 2) {
            $plugins = [
                [
                    'name' => func_get_arg(0),
                    'version' => func_get_arg(1),
                ],
            ];
        }

        $packages = [];

        if (count($plugins) == 0) {
            // Randomly generate 10 fake plugins
            for ($i = 0; $i < 10; $i++) {
                $packages[] = $this->generateFakePluginsRegistryPackage();
            }
        } else {
            foreach ($plugins as $info) {
                $packages[] = $this->generateFakePluginsRegistryPackage($info);
            }
        }

        return json_encode([
            'packages' => $packages,
        ]);
    }

    /**
     * Generate a fake plugin in plugins directory with given information.
     *
     * @param array $info The "name" field is required.
     * @return void
     */
    protected function generateFakePlugin($info)
    {
        $plugin_dir = config('plugins.directory').DIRECTORY_SEPARATOR.$info['name'];

        if (! is_dir($plugin_dir)) {
            mkdir($plugin_dir);
        }

        // Generate fake config view
        if ($config = Arr::get($info, 'config')) {
            $views_path = "$plugin_dir/views";

            if (! is_dir($views_path)) {
                mkdir($views_path);
            }

            file_put_contents("$views_path/$config", Str::random(64));
        }

        file_put_contents("$plugin_dir/package.json", json_encode(
            $this->generateFakePlguinInfo($info)
        ));

        file_put_contents("$plugin_dir/bootstrap.php", "<?php return function () { return '{$info['name']}'; };");
    }
}
