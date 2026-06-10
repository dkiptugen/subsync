#!/usr/bin/env node

const Module = require('module');
const webpack = require('webpack');

const isProduction = process.argv.includes('--production') || process.argv.includes('-p');

process.env.NODE_ENV = isProduction ? 'production' : (process.env.NODE_ENV || 'development');
process.env.MIX_FILE = process.env.MIX_FILE || 'webpack.mix';

const originalLoad = Module._load;

Module._load = function load(request, parent, isMain) {
    if (request === 'yargs/yargs') {
        return () => ({
            options() {
                return this;
            },
            parseSync() {
                return {
                    hot: process.argv.includes('--hot'),
                    https: process.argv.includes('--https'),
                    hmrPort: '8080',
                    p: isProduction,
                };
            },
        });
    }

    return originalLoad.call(this, request, parent, isMain);
};

require('laravel-mix/setup/webpack.config.js')()
    .then((config) => {
        webpack(config, (error, stats) => {
            if (error) {
                console.error(error);
                process.exitCode = 1;

                return;
            }

            const output = stats.toString({
                all: false,
                assets: true,
                builtAt: true,
                colors: true,
                errors: true,
                timings: true,
                warnings: true,
            });

            if (output) {
                console.log(output);
            }

            if (stats.hasErrors()) {
                process.exitCode = 1;
            }
        });
    })
    .catch((error) => {
        console.error(error);
        process.exitCode = 1;
    });
