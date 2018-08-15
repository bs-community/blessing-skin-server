<?php

trait GenerateFakePlugins
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
            'name' => str_random(10),
            'version' => '0.0.'.rand(1, 9),
            'title' => str_random(20),
            'description' => str_random(60),
            'author' => array_get($info, 'author', str_random(10)),
            'url' => 'https://'.str_random(10).'.test',
            'namespace' => str_random(10),
            'require' => [
                'blessing-skin-server' => '^3.4.0'
            ]
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
                'url' => 'https://plugins-registry.test/'.str_random(10).'.zip',
                'shasum' => strtolower(str_random(40))
            ]
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
                    'version' => func_get_arg(1)
                ]
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
            'packages' => $packages
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
        $plugin_dir = base_path("plugins/{$info['name']}");

        if (! is_dir($plugin_dir)) {
            mkdir($plugin_dir);
        }

        file_put_contents("$plugin_dir/package.json", json_encode(
            $this->generateFakePlguinInfo($info)
        ));
    }

    /**
     * Generate a fake zip archive of given plugin.
     *
     * @param array $info Plugin information.
     * @return string     File path of generated zip archive.
     */
    protected function generateFakePluginArchive($info)
    {
        $name = array_get($info, 'name');
        $version = array_get($info, 'version');
        $zipPath = storage_path("testing/{$name}_{$version}.zip");

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addEmptyDir($name);
        $zip->addFromString("$name/package.json", json_encode(
            $this->generateFakePlguinInfo($info)
        ));
        $zip->close();

        return $zipPath;
    }
}
