<?php

    namespace App\Core\Plugins;

    class PluginHelper
        {
            public static function isEnabled(string $pluginDir)
            : bool
                {
                    $jsonFile = base_path("plugins/{$pluginDir}/plugins.json");

                    if (!file_exists($jsonFile))
                        {
                            return false;
                        }

                    $plugin = json_decode(file_get_contents($jsonFile), true);

                    return $plugin['enabled'] ?? false;
                }
        }
