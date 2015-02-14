'use strict';

module.exports = function(grunt) {
 
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-requirejs');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
 
  grunt.registerTask('serve', [ 'watch' ]);
 
  // Configurable paths
  var config = {
      app: 'templates/adminlte/',
      src: 'local/almasaeed2010/adminlte/',
      dist: 'templates/adminlte/',
      root: 'templates/'
  };


  grunt.initConfig({
    config: config,
    less: {
      style: {
        files: {
          "<%= config.app %>css/adminlte.css": ["<%= config.app %>less/custom.less"]
        }
      }
    },
    cssmin: {
      css: {
        src: '<%= config.root %>css/AdminLTE.css',
        dest: '<%= config.root %>css/AdminLTE.min.css'
      }
    },
    watch: {
      css: {
        files: ['<%= config.app %>less/**/*.less', '<%= config.app %>css/*.less'],
        tasks: ['less:style'],
        options: {
          livereload: true,
        }
      }
    }
  });
};
