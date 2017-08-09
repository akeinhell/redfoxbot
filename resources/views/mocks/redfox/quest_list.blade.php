@extends('mocks.redfox.layout')

@section('content')
    <p class="team_name">{{$teamName}}</p>
    
    <h1>{{$gameTitle}}</h1>
    
    <p class="timer"><span id="timer"></span></p>
    
    <table class="safari-locations" style="float: left;">
        @foreach($quests as $id => $quest)
            <tr @if($quest['completed'])class='complete'@endif>
                <td>
                    <a href="/play/safari/{{ $id }}">{{ $quest['title'] }}</a>
                </td>
                <td>
                    {{ $quest['codes']['done'] }}  /  {{ $quest['codes']['total'] }}
                </td>
            </tr>
        @endforeach

    <!-- insert here -->
            
    <p class="level_time">
        <strong>Время игры:</strong><br>
        <span id="time_spent"></span> / <span id="time_left"></span>
    </p>
    
    <table class="locations">
        <tr>
            <td><a href="#">Default</a></td>
        </tr>
    </table>
    
<style type="text/css">
.safari-locations { border-collapse: collapse;}
.safari-locations td { padding: 4px 10px; border: 1px solid silver; border-right: 0; border-left: 0;}
.safari-locations tr.complete { background-color: #ccffcc; }
.safari-locations a { text-decoration: none; color: #0000EF; }
.safari-locations a:visited { color: #0000EF; }
.locations { display: none }
</style>        
<script type="text/javascript" language="Javascript">

var time_spent = {{ $timeSpent }};
var time_left  = {{ $timeLeft }};

window.onload = function(){
    timer();
    level_time();
}
function timer() {
    var time = new Date();
    document.getElementById('timer').innerHTML = time.toLocaleTimeString();
    setTimeout('timer()', 1000);
}

function level_time() {
    document.getElementById('time_spent').innerHTML = make_time(time_spent);
    document.getElementById('time_left').innerHTML = make_time(time_left);
    time_spent += 1000;
    time_left -= 1000;
    setTimeout('level_time()', 1000);
    
    if(time_spent > {{ $totalTime }}) {
        document.location = "{{$baseUrl}}/play/safari_get_back/";
    }
}

function make_time(time)
{
    var time = new Date(time);
    
    
    var days = time.getUTCDate();
    days--;
    
    var hours = time.getUTCHours();
    if (hours < 10) { 
        hours = "0" + hours; 
    }
    var mins = time.getUTCMinutes();
    if (mins < 10) { 
        mins = "0" + mins; 
    }
    var seconds = time.getUTCSeconds();
    if (seconds < 10) { 
        seconds = "0" + seconds; 
    }
    var str = hours + ":" + mins + ":" + seconds;
    
    if (days)
    {
        str = days + " д. " + str;
    }
    return str;
}

</script>

    </table> <!-- closed tag does not exists really  -->
@endsection