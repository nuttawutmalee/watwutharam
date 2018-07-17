<ul class="site__social">
    @foreach($template_item['data_social'] as $i => $k)
    <li>
        <a href="{{ $k['social_url'] }}" target="{{ $k['social_target'] }}" title="{{ $k['social_title'] }}">
            <i class="icon-{{ $k['social_class'] }}"></i>
        </a>
    </li>
    @endforeach
</ul>