App = require '../Application'

redfoxCities = [
  {domain: 'www', label: 'Красноярск'},
  {domain: 'tomsk', label: 'Томск'},
  {domain: 'kemerovo', label: 'Кемерово'},
  {domain: 'nsk', label: 'Новосибирск'},
  {domain: 'belovo', label: 'Белово'},
  {domain: 'nvkz', label: 'Новокузнецк'},
]

RedfoxView = (templatesDir)->
  "ngInject"
  {
    templateUrl: "#{templatesDir}redfoxengine.html"
    scope:
      url: "="
      domain: "="
      login: "="
      password: "="
    link: (scope) ->
      scope.redfoxCities = redfoxCities
      scope.$watch('domain', (domain)->
        scope.domain = domain.toLowerCase().replace(/\s+/g,'');        
        if domain then scope.url = "http://#{domain}.redfoxkrsk.ru/")
  }

module.exports = App.directive 'redfoxView', RedfoxView
