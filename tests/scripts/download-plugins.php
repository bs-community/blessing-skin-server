<?php

function success($message) {
    return "\e[32m$message\e[0m\n";
}

function warning($message) {
    return "\e[33m$message\e[0m\n";
}

function error($message) {
    return "\e[31m$message\e[0m\n";
}

function getPlugin($plugin_name, $filename) {
    if (!file_exists('./plugins/'.$plugin_name)) {
        fwrite(STDOUT, warning("Cannot find plugin: $plugin_name"));

        fwrite(STDOUT, "Downloading $plugin_name...\n");
        $timer = microtime(true);
        try {
            file_put_contents(
                './storage/'.$filename,
                file_get_contents("https://github.com/printempw/blessing-skin-plugins/raw/master/dist/$filename")
            );
        } catch (Exception $e) {
            fwrite(STDOUT, error("Failed to download plugin: $plugin_name."));
            exit(1);
        }
        fwrite(STDOUT, success("Download plugin \"$plugin_name\" successfully."));

        fwrite(STDOUT, "Unzipping...\n");
        try {
            $zip = new ZipArchive();
            $zip->open('./storage/'.$filename);
            $zip->extractTo('./plugins/');
            $zip->close();
            unlink('./storage/'.$filename);
        } catch (Exception $e) {
            fwrite(STDOUT, error("Failed to unzip!"));
            exit(1);
        }
        $time_diff = round(microtime(true) - $timer, 3);
        fwrite(STDOUT, success("Finished: \"$plugin_name\" in {$time_diff}s"));
    } else {
        fwrite(STDOUT, success("Plugin \"$plugin_name\" is existed. OK."));
    }
}

$plugins = [
    'example-plugin' => 'example-plugin_v1.0.zip',
    'avatar-api'     => 'avatar-api_v1.1.zip'
];

foreach ($plugins as $plugin_name => $filename) {
    getPlugin($plugin_name, $filename);
}
