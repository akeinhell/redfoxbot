App = angular.module 'site', ['ui.bootstrap', 'angular-loading-bar', 'ngAnimate']
App.config(['cfpLoadingBarProvider', (cfpLoadingBarProvider)-> (
  cfpLoadingBarProvider.spinnerTemplate = '<div><span class="fa fa-spinner fa-spin"></span>Загружаем список игр</div>';
  cfpLoadingBarProvider.includeSpinner = true;
  cfpLoadingBarProvider.parentSelector = '#loading-bar-container';
)])
App.constant 'templatesDir', '/templates/'

module.exports = App