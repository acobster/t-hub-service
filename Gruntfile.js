/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    watch: {
      lib_test: {
        files: '**/*.php',
        tasks: ['phpunit']
      }
    },
    phpunit: {
      classes: {
        dir: 'test'
      },
      options: {
        bin: '/usr/bin/env phpunit',
        bootstrap: 'test/integration/bootstrap.php',
        colors: true
      }
    }
  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-phpunit');

  grunt.task.registerTask( 'default', 'watch' );

};
