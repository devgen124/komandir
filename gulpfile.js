const isRemote = 1;

import gulp from 'gulp';
import plumber from 'gulp-plumber';
import sourcemap from 'gulp-sourcemaps';
import sassCompiler from 'sass';
import gulpSass from 'gulp-sass';
import sassGlob from 'gulp-sass-glob';
import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';
import bsSync from 'browser-sync';
import cache from 'gulp-cache';
import rename from 'gulp-rename';
import babel from 'gulp-babel';
import webpack from 'webpack-stream';
import imagemin, {gifsicle, mozjpeg, optipng, svgo} from 'gulp-imagemin';
import svgSprite from 'gulp-svg-sprite';
import cheerio from 'gulp-cheerio';
import rsync from 'gulp-rsync';
import clean from 'gulp-clean';
import path, {dirname} from 'path';
import {fileURLToPath} from 'url';
import shell from 'gulp-shell';

const __dirname = dirname(fileURLToPath(import.meta.url));
// env = process.env.NODE_ENV,
// gulpif = require('gulp-if');

const sass = gulpSass(sassCompiler),
	server = bsSync.create();

const setup = {
	prodFolder: 'public_html',
	themeName: 'komandir',
	assetsFolder: 'assets',
	ssl: true,
	proxyLocal: 'komandir.loc',
	proxyRemote: 'komandir124.ru',
	sshHostname: 'pomukk9g@pomukk9g.beget.tech'
};

const downloadBash = `rsync -avz --delete --exclude 'import_files' ${setup.sshHostname}:${setup.proxyRemote}/public_html/ "$PWD/${setup.prodFolder}/"`;

const themeFolder = `${setup.prodFolder}/wp-content/themes/${setup.themeName}`;
const pluginsFolder = `${setup.prodFolder}/wp-content/plugins`;

const rSyncFullUpload = {
	root: setup.prodFolder,
	hostname: setup.sshHostname,
	destination: `${setup.proxyRemote}/public_html`,
	archive: true,
	compress: true,
	recursive: true,
	update: true,
	clean: true,
	command: true
};

const rSyncSetup = Object.assign({},
	rSyncFullUpload,
	{
		exclude: [
			'wp-content/uploads/*',
			'wp-content/plugins/*'
		]
	}
);

const rSyncUploadPlugins = {
	root: `${setup.prodFolder}/wp-content/plugins`,
	hostname: setup.sshHostname,
	destination: `${setup.proxyRemote}/public_html/wp-content/plugins`,
	archive: true,
	compress: true,
	recursive: true,
	update: true,
	clean: true,
	command: true
};

const copy = () => {
	return gulp.src(['source/{fonts,vendor}/**', 'source/images/**/*.{png,jpg}'], {base: 'source'})
		.pipe(gulp.dest(`${themeFolder}/${setup.assetsFolder}`));
};

export const css = () => {
	return gulp.src('source/scss/style.scss')
		.pipe(plumber())
		.pipe(sourcemap.init())
		.pipe(sassGlob())
		.pipe(sass({
			includePaths: ['./node_modules']
		}).on('error', sass.logError))
		.pipe(postcss([autoprefixer({
			grid: true,
		})]))
		.pipe(sourcemap.write('.'))
		.pipe(gulp.dest(`${themeFolder}/${setup.assetsFolder}/css`))
};

export const js = () => {
	return gulp.src('source/js/*.js')
		.pipe(webpack({
			context: path.resolve(__dirname, 'source'),
			mode: 'development',
			devtool: 'source-map',
			entry: './js/main.js',
			output: {
				filename: 'script.js',
				path: path.resolve(__dirname, `${themeFolder}/${setup.assetsFolder}/js`),
			}
		}))
		.pipe(gulp.dest(`${themeFolder}/${setup.assetsFolder}/js`));
};

const optimizeSvg = () => {
	return gulp.src('source/images/**/*.svg')
		.pipe(cache(imagemin([
			svgo({
				plugins: [
					{name: 'removeViewBox', active: false},
					{name: 'removeRasterImages', active: true},
					{name: 'removeUselessStrokeAndFill', active: false},
				]
			})
		], {
			verbose: true
		})))
		.pipe(gulp.dest(`${themeFolder}/${setup.assetsFolder}/images`));
};

// const copySvg = () => {
//   return gulp.src('source/images/!(sprite)/*.svg', { base: 'source' })
//     .pipe(gulp.dest(`${themeFolder}/${setup.assetsFolder}`));
// }

export const sprite = () => {
	return gulp.src(`${themeFolder}/${setup.assetsFolder}/images/sprite/**/*.svg`)
		.pipe(svgSprite({
			mode: {
				symbol: {
					sprite: "../sprite.svg"
				}
			},
			shape: {
				transform: [
					{
						plugins: [
							{
								removeAttrs: {
									attrs: ['class', 'data-name'],
								},
							},
							{
								removeUselessStrokeAndFill: false,
							},
							{
								inlineStyles: true,
							},
						]
					}
				]
			}
		}))
		.pipe(cheerio({
			run: function ($) {
				$('symbol').removeAttr('fill')
			},
			parserOptions: {xmlMode: true}
		}))
		.pipe(gulp.dest(`${themeFolder}/${setup.assetsFolder}/images/`));
}

const optimizeJpgPng = () => {
	return gulp.src(`${themeFolder}/${setup.assetsFolder}/images/**/*.{png,jpg}`)
		.pipe(plumber())
		.pipe(imagemin([
			imagemin.optipng({optimizationLevel: 3}),
			imagemin.mozjpeg({quality: 75, progressive: true}),
		]))
		.pipe(gulp.dest(`${themeFolder}/${setup.assetsFolder}/images`));
}

const serve = () => {
	const httpPrefix = setup.ssl ? 'https://' : 'http://';
	const proxyDomain = isRemote ? setup.proxyRemote : setup.proxyLocal;
	server.init({
		notify: false,
		proxy: httpPrefix + proxyDomain,
		open: false
	});
}

const refresh = (done) => {
	server.reload();
	cache.clearAll();
	done();
};

export const upload = () => {
	return gulp.src(setup.prodFolder)
		.pipe(rsync(rSyncSetup));
};

export const download = shell.task(downloadBash, {verbose: true});

export const fullUp = () => {
	return gulp.src(setup.prodFolder)
		.pipe(rsync(rSyncFullUpload));
};

export const pluginsUp = () => {
	return gulp.src(pluginsFolder)
		.pipe(rsync(rSyncUploadPlugins));
}

const watchCompile = () => {
	gulp.watch(['source/{fonts,vendor}/**', 'source/images/**/*.{png,jpg}'], gulp.series(copy, refresh));
	gulp.watch('source/scss/**/*.scss', gulp.series(css, refresh));
	gulp.watch('source/js/**/*.js', gulp.series(js, refresh));
	gulp.watch('source/images/**/*.svg', gulp.series(optimizeSvg, refresh));
	gulp.watch(`${themeFolder}/${setup.assetsFolder}/images/sprite/**/*.svg`, gulp.series(sprite, refresh));
	gulp.watch(`${setup.prodFolder}/**/*.php`, refresh);
}

const watch = () => {
	if (isRemote) {
		gulp.watch([`${setup.prodFolder}/**/*`, `!${pluginsFolder}/**/*`], upload);
	}
};

const watchPlugins = () => {
	if (isRemote) {
		gulp.watch(`${pluginsFolder}/**/*`, pluginsUp);
	}
}

const cleanAssets = () => {
	return gulp.src(`${themeFolder}/${setup.assetsFolder}/`, {read: false})
		.pipe(clean({force: true}));
};

export default gulp.series(
	cleanAssets,
	gulp.parallel(optimizeSvg, copy, css, js),
	sprite,
	upload,
	gulp.parallel(serve, watchCompile, watch)
);

export const plugins = watchPlugins;

export const prod = gulp.series(gulp.parallel(css, js), upload);



