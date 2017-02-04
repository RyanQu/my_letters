@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading title">Game: Player1 VS Player2 </div>
                <div class="panel-heading sub-title">Status:  Red-{{$counter[0]}}  Blue-{{$counter[1]}}, {{$status}}</div>
                <div class="panel-heading">Played Words
                        @for ($i = 0; $i < count($gamelog_color); $i++)
                            @if($gamelog_word[$i]=="0") <span class="{{$gamelog_color[$i]}}">False Word!</span>
                            @elseif($gamelog_word[$i]=="1") <span class="{{$gamelog_color[$i]}}">Repeat!</span>
                            @else<span class="{{$gamelog_color[$i]}}">{{$gamelog_word[$i]}}</span>
                            @endif
                        @endfor
                </div>
                {{--@foreach ($boards as $board)--}}
                    {{--<div class="title"><h4>{{ $board->letters }}</h4></div>--}}
                {{--@endforeach--}}
                <div class="panel-body text-center">
                    @for ($i = 0; $i < 5; $i++)
                        <div class="btn-group board" id="row{{ $i }}">
                            @for ($j = 0; $j < 5; $j++)
                                <button type="button" class="btn btn-default block {{ $color[$i][$j] }}">{{ $char[$i][$j] }}</button>
                            @endfor
                        </div>
                    @endfor
                </div>
                <div class="panel-heading">
                    <form class="bs-example bs-example-form" role="form" action="{{ URL('home/order') }}" method="POST">
                        <div class="input-group">
                            <span class="input-group-addon">Input Order</span>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input name="user" type="hidden" value="{{ Auth::user()->id }}">
                            <input type="text" class="form-control" name="order" placeholder="E.g. (1,1),(1,2),(2,3)">
                            <span class="input-group-btn">
                                <button class="btn red" name="player" value="1" type="submit">Player1</button>
								<button class="btn blu" name="player" value="2" type="submit">Player2</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
