App = require '../Application'
console.log('loaded loginView');
loginView = (templatesDir)->
  "ngInject"
  {
    templateUrl: "#{templatesDir}loginInput.html"
    scope:
      login: "="
      password: "="
  }

module.exports = App.directive 'loginView', loginView