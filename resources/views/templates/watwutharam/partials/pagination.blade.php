@if(isset($currentPage) && is_numeric($currentPage) && isset($totalPages) && is_numeric($totalPages))
    <?php
    $totalPages = intval($totalPages);
    $currentPage = intval($currentPage);
    ?>
    @if($totalPages > 0)
        <div class="pagination">
            <ul>
                @if($currentPage === 1)
                    <!-- <li class="prev" disable>
                        <a href="#">Prev</a>
                    </li> -->
                @else
                    <li class="prev">
                        <a href="?p={{ $currentPage - 1 }}">Prev</a>
                    </li>
                @endif

                @if($totalPages > 1)
                    @if($currentPage <= 5 && $totalPages <= 5)
                        @foreach(range(1, $totalPages) as $page)
                            <li @if($page === $currentPage) class="is--active" @endif>
                                <a href="?p={{ $page }}">{{ $page }}</a>
                            </li>
                        @endforeach
                    @elseif($currentPage >= $totalPages - 5)
                        @foreach(range($totalPages - 5, $totalPages) as $page)
                            <li @if($page === $currentPage) class="is--active" @endif>
                                <a href="?p={{ $page }}">{{ $page }}</a>
                            </li>
                        @endforeach
                    @else
                        @foreach(range($currentPage - 2, $currentPage + 2) as $page)
                            <li @if($page === $currentPage) class="is--active" @endif>
                                <a href="?p={{ $page }}">{{ $page }}</a>
                            </li>
                        @endforeach
                    @endif
                @endif

                @if($currentPage === $totalPages)
                    <!-- <li class="next" disable>
                        <a href="#">Next</a>
                    </li> -->
                @else
                    <li class="next">
                        <a href="?p={{ $currentPage + 1}}">Next</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
@endif