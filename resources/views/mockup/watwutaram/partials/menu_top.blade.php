<ul class="site__menu">
    @foreach($template_item['data_top_menu'] as $i => $k)
    <li>
        <a href="{{ $k['nav_url'] }}" target="{{ $k['nav_target'] }}" title="{{ $k['nav_title'] }}" @isset($page) @if($page===$k[ 'nav_url']) class="is--active" @endif @endisset>{{ $k['nav_title'] }}</a>
    </li>
    @endforeach
</ul>