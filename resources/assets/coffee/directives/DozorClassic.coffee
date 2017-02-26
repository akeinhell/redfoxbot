App = require '../Application'

DozorClassic = (templatesDir, $http)->
  "ngInject"
  {
    templateUrl: "#{templatesDir}DozorClassic.html"
    scope:
      url: "="
      pass: "="
      login: "="
      domain: "="
    link: (scope) ->
      scope.domain = null
      scope.$watch('domain', (domain)->
          scope.domain = domain.toLowerCase().replace(/\s+/g,'');
          scope.url = "http://classic.dzzzr.ru/#{scope.domain}/go"
      )
  }

module.exports = App.directive 'dozorClassic', DozorClassic
