'use strict';
module.exports = function (grunt) {
	// Load all tasks
	require('load-grunt-tasks')(grunt);
	// Show elapsed time
	require('time-grunt')(grunt);

	var jsFileList = [
		'assets/vendor/bootstrap/js/transition.js',
		'assets/vendor/bootstrap/js/alert.js',
		'assets/vendor/bootstrap/js/button.js',
		'assets/vendor/bootstrap/js/carousel.js',
		'assets/vendor/bootstrap/js/collapse.js',
		'assets/vendor/bootstrap/js/dropdown.js',
		'assets/vendor/bootstrap/js/modal.js',
		'assets/vendor/bootstrap/js/tooltip.js',
		'assets/vendor/bootstrap/js/popover.js',
		'assets/vendor/bootstrap/js/scrollspy.js',
		'assets/vendor/bootstrap/js/tab.js',
		'assets/vendor/bootstrap/js/affix.js',
		'assets/js/plugins/*.js',
		'assets/js/_*.js'
	];

	grunt.initConfig({
		jshint      : {
			options: {
				jshintrc: '.jshintrc'
			},
			all    : [
				'Gruntfile.js',
				'assets/js/*.js',
				'!assets/js/scripts.js',
				'!assets/**/*.min.*'
			]
		},
		less        : {
			build: {
				files  : {
					'assets/css/main.min.css': [
						'assets/less/main.less'
					]
				},
				options: {
					compress         : true,
					sourceMap        : true,
					sourceMapFilename: 'assets/css/main.min.css.map',
					sourceMapRootpath: '/app/themes/roots/'
				}
			}
		},
		concat      : {
			options: {
				separator: ';'
			},
			dist   : {
				src : [jsFileList],
				dest: 'assets/js/scripts.js'
			}
		},
		uglify      : {
			dist: {
				files: {
					'assets/js/scripts.min.js': [jsFileList]
				}
			}
		},
		autoprefixer: {
			options: {
				browsers: ['last 2 versions', 'ie 8', 'ie 9', 'android 2.3', 'android 4', 'opera 12']
			},
			build  : {
				options: {
					map: {
						prev: 'assets/css/'
					}
				},
				src    : 'assets/css/main.min.css'
			}
		},
		modernizr   : {
			build: {
				devFile   : 'assets/vendor/modernizr/modernizr.js',
				outputFile: 'assets/js/vendor/modernizr.min.js',
				files     : {
					'src': [
						['assets/js/scripts.min.js'],
						['assets/css/main.min.css']
					]
				},
				extra     : {
					shiv: false
				},
				uglify    : true,
				parseFiles: true
			}
		},
		version     : {
			default: {
				options: {
					format     : true,
					length     : 32,
					manifest   : 'assets/manifest.json',
					querystring: {
						style : 'roots_css',
						script: 'roots_js'
					}
				},
				files  : {
					'lib/scripts.php': 'assets/{css,js}/{main,scripts}.min.{css,js}'
				}
			}
		},
		watch       : {
			less      : {
				files: [
					'assets/less/*.less',
					'assets/less/**/*.less'
				],
				tasks: ['less:build', 'autoprefixer:build', 'uglify', 'version']
			},
			js        : {
				files: [
					jsFileList,
					'<%= jshint.all %>'
				],
				tasks: ['jshint', 'uglify', 'modernizr', 'version']
			},
			livereload: {
				// Browser live reloading
				// https://github.com/gruntjs/grunt-contrib-watch#live-reloading
				options: {
					livereload: true
				},
				files  : [
					'assets/css/main.min.css',
					'assets/js/scripts.js',
					'templates/*.php',
					'*.php'
				]
			}
		}
	});

	// Register tasks
	grunt.registerTask('default', [
		'jshint',
		'less:build',
		'autoprefixer:build',
		'uglify',
		'modernizr',
		'version'
	]);
};
