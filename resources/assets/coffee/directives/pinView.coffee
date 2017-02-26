App = require '../Application'
pinView = (templatesDir)->
  "ngInject"
  {
    templateUrl: "#{templatesDir}pinInput.html"
    scope:
      pin: '='
  }

module.exports = App.directive 'pinView', pinView