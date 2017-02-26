App = require '../Application'

Lampa = (templatesDir, $http)->
  "ngInject"
  {
    restrict: "E"
    templateUrl: "#{templatesDir}lampa.html"
    scope:
      url: "="
      pin: "="
      project: "="
      domain: "="
      id: "="
      teamid: "="
      teampass: "="
    link: (scope) ->
      scope.games = [];
      scope.teams = [];
      scope.$watch('domain', (domain)->
        if domain
          scope.url = "http://#{domain}.lampagame.ru"
          $http.get("/api/lampa/games/#{domain}")
          .then((response)->
            scope.games = response.data
            scope.teams = [];
          )
      )
      scope.$watch('id', (id)->
        if id
          $http.get("/api/lampa/commands/#{scope.domain}?gameId=#{id}")
          .then((response)-> scope.teams = response.data)
      )
  }

App.directive 'lampaView', Lampa