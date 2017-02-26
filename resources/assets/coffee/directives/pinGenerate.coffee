App = require '../Application'

pinGenerate = (templatesDir, $http)->
  "ngInject"
  {
    restrict: "E"
    templateUrl: "#{templatesDir}pinGenerate.html"
    link: (scope)->
      scope.getLink = ->
        $http.get('/api/generateToken', {params: scope.config})
        .then (response)->
          scope.hash = response.data.token
    scope:
      config: "="
  }

App.directive 'pinGenerate', pinGenerate