App = require '../Application'

REDFOX = 'Redfox'
ENCOUNTER = 'Encounter'
DOZOR = 'Dozor'

ENGINES = [REDFOX, ENCOUNTER, DOZOR]

settingsCotroller = ($scope, $http)->
  "ngInject"
  $scope.$watch('settings', ->
    $scope.hash = null
    $scope.error = []
    if !$scope.settings.project
      $scope.error.push {msg: 'Выберите движок'}
      return

    if $scope.settings.project == 'DozorLite'
      if !$scope.settings.pin
        $scope.error.push {msg: 'Введите пин'}
      return

    if !$scope.settings.login
      $scope.error.push {msg: 'Введите login'}
      return

    if !$scope.settings.pass
      $scope.error.push {msg: 'Введите pass'}
      return

    if $scope.needGameId and !$scope.settings.gameId
      $scope.error.push {msg: 'Не выбрана игра'}
      return

    if !$scope.settings.url
      $scope.error.push {msg: 'Введите URL'}
      return
  , true)

  $scope.$watch 'engine', ->
    $scope.settings.project = null
    $scope.engineSubtype = null
    $scope.needGameId = false
    $scope.settings.format = 'a-z0-9'
    if $scope.engine
      if [REDFOX].indexOf($scope.engine) >= 0
        $scope.engineSubtype = REDFOX
      else
        $scope.settings.project = $scope.engine
    $scope.needGameId = [ENCOUNTER].indexOf($scope.engine) >= 0


  init = ->
    console.log 'init'
    $scope.settings = {}
    $scope.error = []
    $scope.engine = null
    $scope.error.push {msg: 'test', type: 'danger'}

  $scope.closeAlert = (index) ->
    $scope.error.splice(index, 1);

  $scope.getLink = ->
    $http.get('/api/generateToken', {params: $scope.settings})
    .then (response)->
      $scope.hash = response.data.token
  do init


App.controller 'settingsCotroller', settingsCotroller