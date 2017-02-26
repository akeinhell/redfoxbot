@extends('layouts.main')

@section('title')
	Календарь игр
@endsection

@section('content')
	<div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
   				 <iframe src="https://calendar.google.com/calendar/embed?showPrint=0&amp;showTabs=0&amp;showCalendars=0&amp;height=600&amp;wkst=2&amp;bgcolor=%23FFFFFF&amp;src=c7v4er5p5ciemje60n559jugfk%40group.calendar.google.com&amp;color=%23865A5A&amp;src=ru.russian%23holiday%40group.v.calendar.google.com&amp;color=%2329527A&amp;ctz=Asia%2FOmsk" style="border-width:0" width="100%" height="600" frameborder="0" scrolling="no"></iframe>
    		</div>
    	</div>
    </div>
@endsection
