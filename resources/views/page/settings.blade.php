@extends('layouts.main')

@section('title', 'Настройки бота')

@section('content')
    <div class="container" ng-controller="settingsCtl">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Настройки бота
                    </div>

                    <div class="panel-body">
                        <form class="form-horizontal">
                            <div class="form-group">
                                <div class="col-md-2">
                                    <label for="engine">Проект</label>
                                </div>
                                <div class="col-md-10">
                                    <select ng-model="config.project"
                                            ng-options="engine.engine as engine.title for engine in engines"
                                            class="form-control"
                                            id="engine">
                                        <option value="" disabled>-- Выберите движок --</option>
                                    </select>
                                </div>
                            </div>

                            <redfox-view
                                    ng-if="config.project == 'RedfoxSafari'  || config.project == 'RedfoxAvangard'"
                                    domain="config.domain"
                                    url="config.url"></redfox-view>
                            <dozor-lite ng-if="config.project == 'DozorLite' || config.project == 'Ekipazh'"
                                        project="config.project"
                                        domain="config.domain"
                                        url="config.url"></dozor-lite>
                            <encounter-view
                                    ng-if="config.project == 'Encounter'"
                                    url="config.url"
                                    id="config.gameId"
                                    base="en.cx"></encounter-view>

                            <encounter-view
                                    ng-if="config.project == 'QuestUa'"
                                    url="config.url"
                                    id="config.gameId"
                                    base="quest.ua"></encounter-view>

                            <lampa-view
                                    ng-if="config.project == 'Lampa'"
                                    url="config.url"
                                    domain="config.domain"
                                    id="config.gameId"
                                    teamid="config.teamId"
                                    teampass="config.teamPass"></lampa-view>

                            <dozor-classic
                                    ng-if="config.project == 'DozorClassic'"
                                    url="config.url"
                                    domain="config.domain"
                                    login="config.teamLogin"
                                    pass="config.teamPassword"></dozor-classic>

                            <login-view login="config.login" password="config.password"
                                        ng-if="showLogin()"></login-view>


                            <pin-view pin="config.pin" ng-if="showPin()"></pin-view>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" ng-model="config.auto" id="auto"/>
                                    Автоматическая отправка кодов
                                </label>
                            </div>

                            {{--<div class="row">--}}
                            {{--<div class="col-md-4"><label>Формат кода</label></div>--}}
                            {{--<div class="col-md-8">--}}
                            {{--<input type="text" class="form-control" ng-model="config.format" disabled   >--}}
                            {{--</div>--}}
                            {{--</div>--}}

                            {{--<div class="row">--}}
                            {{--<div class="col-md-12">--}}
                            {{--<input--}}
                            {{--type="checkbox"--}}
                            {{--ng-model="config.auto"--}}
                            {{--id="format">--}}
                            {{--<label for="format">Не отправлять автоматически</label>--}}
                            {{--</div>--}}
                            {{--</div>--}}

                            <pin-generate config="config" ng-if="checkConfig()"></pin-generate>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            @include('partials.donate')
        </div>
    </div>

@endsection

