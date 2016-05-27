@extends('layouts.app')

@section('content')

            <div class="panel panel-default">
                <div class="panel-heading">マイページ</div>

                <div class="panel-body">
                    <ul class="list-unstyled">
                    <li><a href="{{ url('/register') }}">ユーザ新規登録</a>(admin用)</li>
                    <li><a href="{{ url('/users') }}">ユーザ一覧</a>(admin用)</li>
                    <li><a href="{{ url('/account') }}">アカウント</a></li>
                    <li><a href="{{ url('/passwordsetting') }}">パスワード再設定</a></li>
                    <li><a href="{{ url('/password/reset') }}">パス再設定メール</a></li>
                    <li><a href="{{ url('/new') }}">リポジトリ新規作成</a></li>
                    <li><i class="fa fa-folder fa-fw"></i>リポジトリ一覧&リンク</li>
                        <ul class="list-unstyled">
                        @foreach($repos as $repo)
                            <li>
                            {!! link_to("$repo->name", "$repo->name", $attributes = array(), $secure = null) !!}
                            </li>
                        @endforeach
                        </ul>
                    </ul>

                    <p>{{ $user->name }}</p>
                </div>

        </div>
@endsection
