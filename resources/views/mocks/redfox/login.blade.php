@extends('mocks.redfox.layout')

@section('content')
    <style type="text/css">
        form {
            margin: auto;
            padding: 50px;
            text-align: center;
            max-width: 500px;
        }

        form div {
            text-align: right;
            padding: 20px;
        }

        form h1 {
            font-family: 'Times New Roman', serif;
        }

        form input {
            width: 50%;
        }

        form input.submit {
            width: auto;
        }
    </style>

    <form action='/user/login/' method="post">

        <h1>Авторизация</h1>


        <div style="max-width:500px">
            <p>Ваш логин <input name="email" type="text" value=""></p>
            <br/>
            <p>Пароль <input name="pass" type="password" value=""></p>
            <br/>
            <p style="float: left;"><a href="/user/register/">Регистрация</a></p>
            <p class="centered"><a href="/user/password_reset/">Забыли пароль?</a></p>
        </div>
        <input type="submit" class="submit" value=" Войти в систему ">
    </form>
@endsection