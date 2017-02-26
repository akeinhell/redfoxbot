App = require '../Application'

settingsCtl = ($scope, cfpLoadingBar)->
  "ngInject"
  $scope.engines = [
    {'engine': 'RedfoxSafari', 'title': 'Redfox штурм/сафари', showLogin: true},
    {'engine': 'RedfoxAvangard', 'title': 'Redfox Авангард', showLogin: true},
    {'engine': 'Encounter', 'title': 'Encounter', showLogin: true},
    {'engine': 'Encounter', 'title': 'Quest.ua', showLogin: true},
#    {'engine': 'Lampa', 'title': 'Lampa'},
    {'engine': 'DozorLite', 'title': 'Dozor.Lite', showPin: true},
    {'engine': 'DozorLite', 'title': 'Экипаж', showPin: true},
    {'engine': 'Lampa', 'title': 'Лампа (Ведется тестирование!)', showLogin: true},
    {'engine':'DozorClassic', 'title':'Dozor.Classic', showLogin: true},
  ]

  $scope.showLogin = -> ['RedfoxSafari', 'RedfoxAvangard', 'Encounter', 'QuestUa', 'Lampa', 'DozorClassic'].indexOf($scope.config.project) >= 0
  $scope.showPin = -> ['DozorLite'].indexOf($scope.config.project) >= 0

  $scope.config = {
    format: "a-z0-9"
    auto: true
  }

  $scope.checkConfig = ->
    c = $scope.config
    pass = (c.login && c.password) || c.pin
    enCheck = (if c.project == 'Encounter' then c.gameId else true)
    return c.project && c.url && pass && enCheck

module.exports = App.controller 'settingsCtl', settingsCtl