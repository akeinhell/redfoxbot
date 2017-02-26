@extends('layouts.main')

@section('title', 'Настройки бота')

@section('content')
    <div class="container" ng-controller="settingsCotroller">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Настройки бота</div>

                    <div class="panel-body">
                        <div class="row form-group">
                            <div class="col-md-3">
                                <label>Движок</label>
                            </div>
                            <div class="col-md-9">
                                <div class="btn-group">
                                    <label class="btn btn-success" ng-model="engine" uib-btn-radio="'Redfox'">
                                        RedFox
                                    </label>
                                    <label class="btn btn-success" ng-model="engine" uib-btn-radio="'Encounter'">
                                        Encounter
                                    </label>
                                    <label class="btn btn-success" ng-model="engine" uib-btn-radio="'DozorLite'">
                                        Dozor.lite
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row form-group" ng-if="engineSubtype" ng-include="engineSubtype+'.html'"></div>

                        <div class="row form-group" ng-if="settings.project">
                            <div class="col-md-3"><label>url</label></div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" ng-model="settings.url">
                            </div>
                        </div>
                        <div class="form-group" ng-if="settings.project == 'DozorLite'" ng-include="'DozorLite.html'"></div>
                        <div class="row form-group" ng-if="settings.project && settings.project != 'DozorLite'" ng-include="'login.html'"></div>
                        <div class="row form-group" ng-if="settings.project && settings.project != 'DozorLite'" ng-include="'pass.html'"></div>
                        <div class="row form-group" ng-if="settings.project" ng-include="'format.html'"></div>
                        <div class="row form-group" ng-if="needGameId" ng-include="'gameId.html'"></div>

                        <div class="row" ng-if="error.length">
                            <div class="col-md-12">
                                <uib-alert ng-repeat="alert in error" type="@{{alert.type}}" close="closeAlert($index)">
                                    @{{alert.msg}}
                                </uib-alert>
                            </div>
                        </div>
                        <div class="row form-group" id="links-group" ng-if="error.length == 0">
                            <div class='col-md-9 col-md-push-3'>
                                <a class="btn btn-default" ng-click="getLink()">Получить ссылку</a>
                            </div>
                        </div>
                        <div class="row form-group" id="links-group" ng-if="hash">
                            <div class='col-md-3'></div>
                            <div class='col-md-3'>
                                <a ng-href="https://telegram.me/redfoxbot?start=@{{hash}}" target="_blank" class="btn btn-default">Закинуть
                                    в личку</a>
                            </div>
                            <div class='col-md-3'>
                                <a ng-href="https://telegram.me/redfoxbot?startgroup=@{{hash}}" target="_blank"
                                   class="btn btn-default">Закинуть в чат</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">


    </script>
@endsection


<script type="text/ng-template" id="Redfox.html">
    <div class="col-md-3"><label>Тип игры</label></div>
    <div class="col-md-9">
        <div class="btn-group">
            <label class="btn btn-success" ng-model="settings.project" uib-btn-radio="'RedfoxAvangard'">
                Авангард
            </label>
            <label class="btn btn-success" ng-model="settings.project" uib-btn-radio="'RedfoxSafari'">
                Сафари/Штурм
            </label>
        </div>
    </div>
</script>

<script type="text/ng-template" id="DozorLite.html">
    <div class="row">
        <div class="col-md-3"><label>Пин-код</label></div>
        <div class="col-md-9">
            <input type="text" ng-model="settings.pin" placeholder="Пин-код" class="form-control">
        </div>
    </div>
    <div class="row">
        <div class="col-md-3"><label>Домен</label></div>
        <div class="col-md-9" class="form-control">
            <input type="text" ng-model="settings.domain" placeholder="домен. к примеру moscow" class="form-control">
        </div>
    </div>
</script>

<script type="text/ng-template" id="login.html">
    <div class="col-md-3"><label>Логин</label></div>
    <div class="col-md-9">
        <input type="text" class="form-control" ng-model="settings.login">
    </div>
</script>

<script type="text/ng-template" id="pass.html">
    <div class="col-md-3"><label>Пароль</label></div>
    <div class="col-md-9">
        <input type="text" class="form-control" ng-model="settings.pass">
    </div>
</script>

<script type="text/ng-template" id="gameId.html">
    <div class="col-md-3"><label>ID игры</label></div>
    <div class="col-md-9">
        <input type="text" class="form-control" ng-model="settings.gameId">
    </div>
</script>

<script type="text/ng-template" id="format.html">
    <div class="col-md-3"><label>Формат кода</label></div>
    <div class="col-md-9">
        <input type="text" class="form-control" ng-model="settings.format" disabled>
    </div>
</script>