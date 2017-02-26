App = require '../Application'

dozorLite = (templatesDir)->
  "ngInject"
  {
    restrict: "E"
    templateUrl: "#{templatesDir}DozorLite.html"
    link: (scope) ->
      scope.$watch('domain', (domain)->
          scope.domain = domain.toLowerCase().replace(/\s+/g,'');
      )
      if scope.project == 'DozorLite'
        scope.url = 'http://lite.dzzzr.ru/'
      else
        scope.url = 'http://ekipazh.org/'
    scope:
      url: "="
      pin: "="
      project: "="
      domain: "="
  }

App.directive 'dozorLite', dozorLite
