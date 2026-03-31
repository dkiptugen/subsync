<?php

    namespace App\Helpers;

    function app_installed()
    : bool
        {
            return file_exists(storage_path('installed'));
        }
