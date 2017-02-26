<article class="box post post-excerpt">
    <header>
        <h2><a href="#">{{$post['title']}}</a></h2>
    </header>
    <div class="info">
                <span class="date">
                    <span class="month">
                        {{$post['time']->format('M')}}
                        <span>y</span>
                    </span>
                    <span class="day">{{$post['time']->format('d')}}</span>
                    <span class="year">, {{$post['time']->format('Y')}}</span>
                </span>
        <ul class="stats">
            <li><a href="http://vk.com/foxbot_project?w=wall-100968862_{{$post['id']}}"
                   class="icon fa-comment">{{$post['comments']}}</a></li>
            <li><a class="icon fa-heart">{{$post['likes']}}</a></li>
            <li><a class="icon fa-retweet">{{$post['reposts']}}</a></li>
        </ul>
    </div>
    <p>{!! $post['text'] !!}</p>
    <div class="row">
        @foreach($post['images'] as $image)
            <img src="{{$image}}" alt=""/>
        @endforeach
    </div>
</article>