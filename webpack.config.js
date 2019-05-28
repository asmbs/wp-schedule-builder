const webpack = require('webpack');
const path = require('path');

const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const LiveReloadPlugin = require('webpack-livereload-plugin');

module.exports = {
    mode: 'production',
    entry: {
        main: './assets/index.js'
    },
    output: {
        filename: 'scripts/[name].bundle.js',
        path: path.resolve(__dirname, 'dist')
    },
    module: {
        rules: [
            {
                test: /\.less$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            publicPath: '../'
                        }
                    },
                    'css-loader',
                    'postcss-loader',
                    'less-loader'
                ]
            },
            {
                test: /\.css/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            publicPath: '../'
                        }
                    },
                    'css-loader',
                    'postcss-loader'
                ]
            },
            {
                test: /\.(ttf|otf|eot|woff|woff2|svg)$/,
                use: {
                    loader: "file-loader",
                    options: {
                        name: "fonts/[name].[ext]",
                    },
                },
            },
            {
                test: /\.(jpg|png|gif)$/,
                use: {
                    loader: "file-loader",
                    options: {
                        name: "images/[name].[ext]",
                    },
                },
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: "styles/[name].css",
            chunkFilename: "[id].css"
        }),
        new ImageminPlugin({
            jpegtran: {
                progressive: true
            },
            gifsicle: {
                interlaced: true
            },
            svgo: {
                removeUnknownsandDefaults: false,
                cleanupIDs: false
            },
        }),
        new LiveReloadPlugin({
            port: 35729
        })
    ],
    optimization: {
        minimizer: [
            new UglifyJSPlugin({
                cache: true,
                parallel: true,
                sourceMap: true // set to true if you want JS source maps
            }),
            new OptimizeCSSAssetsPlugin({})
        ]
    },
    externals: {
        jquery: 'jQuery'
    },
    resolve: {
        alias: {
            "./dependencyLibs/inputmask.dependencyLib": "./dependencyLibs/inputmask.dependencyLib.jquery"
        }
    },
};
