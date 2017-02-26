App = require '../Application'

getUrl = (domain, subDomain)->
  return if subDomain then [subDomain, domain].join('.') else domain


encounterView = (templatesDir, $http)->
  "ngInject"
  {
    templateUrl: "#{templatesDir}encounter.html"
    scope:
      url: "="
      id: "="
      base: '@'
    link: (scope) ->
      scope.games = [];
      scope.domain = null
      scope.$watch('domain', (domain)->
        scope.domain = domain.toLowerCase().replace(/\s+/g,'');
        url = getUrl(scope.base, domain)
        scope.url = "http://#{url}"
        $http.get("/api/en/games/#{url}")
        .then((response)-> scope.games = response.data)
      )

  }

module.exports = App.directive 'encounterView', encounterView
