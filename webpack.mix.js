require('dotenv').config();

const templateName = process.env.APP_NAME || 'watwutaram';
const mix = require('laravel-mix');
const path = require('path');
const webpack = require('webpack');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const CopyWebpackPlugin = require('copy-webpack-plugin');
const imageminMozjpeg = require('imagemin-mozjpeg');
const ExtractTextPlugin = require("extract-text-webpack-plugin");

// Configuration area
const templatePath = `templates/${templateName}`;
const srcPath = path.join(__dirname, 'src');
const jsVendorsPath = `${srcPath}/vendors`;
const assetsPath = `${srcPath}/assets`;
const entryFile = `${srcPath}/js/scripts.js`;
const entryFileCritical = `${srcPath}/sass/critical.scss`;

const extractSass = new ExtractTextPlugin({
	filename: "[name].[contenthash].css",
	// disable: process.env.NODE_ENV === "development"
});

// Set custom config to mix webpack
mix.webpackConfig({
	resolve: {
		extensions: ['.js'],
		modules: ['node_modules', 'bower_components', jsVendorsPath],
		alias: {
			modernizr: `imports-loader?this=>window!exports-loader?${jsVendorsPath}/modernizr/modernizr-custom.js`,
			detectizr: 'imports-loader?this=>window!exports-loader?node_modules/detectizr/dist/detectizr.js',
		}
	},
	module: {
		loaders: [{
				test: /\.js$/,
				loader: 'babel',
				exclude: [
					'node_modules',
					'bower_components',
					jsVendorsPath
				],
				query: {
					presets: ['es2015', 'stage-0'],
					plugins: [
						['transform-object-rest-spread']
					],
				},
			},
			{
				test: /\.(scss|sass|css)$/i,
				use: extractSass.extract({
					use: [{
							loader: "css-loader"
						},
						{
							loader: 'postcss-loader',
							options: {
								config: {
									path: './postcss.config.js',
								},
							},
						},
						{
							loader: 'resolve-url-loader'
						},
						{
							loader: "sass-loader",
							options: {
								importLoaders: 1
							}
						}
					],
					// use style-loader in development
					fallback: "style-loader"
				})
			}
		]
	},
	plugins: [
		new CopyWebpackPlugin([{
				context: `${assetsPath}/images`,
				from: '**/*',
				to: `${templatePath}/assets/images`
			},
			{
				context: `${assetsPath}/fonts`,
				from: '**/*',
				to: `${templatePath}/assets/fonts`
			},
			{
				context: `${assetsPath}/favicons`,
				from: '**/*',
				to: `${templatePath}/assets/favicons`
			},
			{
				context: `${assetsPath}/videos`,
				from: '**/*',
				to: `${templatePath}/assets/videos`
			},
			{
				context: `${assetsPath}/map`,
				from: '**/*',
				to: `${templatePath}/js`
			}
		]),
		new ImageminPlugin({
			test: /\.(jpe?g|png|gif|svg)$/i,
			pngquant: {
				quality: '95-100',
			},
			plugins: [
				imageminMozjpeg({
					quality: 80,
				})
			]
		}),
		new webpack.ProvidePlugin({
			$: 'jquery',
			jQuery: 'jquery',
			'window.jQuery': 'jquery'
		}),
		extractSass
	]
});

if (mix.inProduction()) {
	// Set output version if production
	mix.version();
}

// Set entry point file and output
mix.js(entryFile, `${templatePath}/js`)
	.sass(entryFileCritical, `${templatePath}/css`)
	.options({
		processCssUrls: false,
		postCss: [
			require('postcss-discard-comments')({
				removeAll: true
			})
		],
		uglify: {
			uglifyOptions: {
				comments: false
			}
		}
	});