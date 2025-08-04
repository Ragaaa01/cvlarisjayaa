@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        <ul class="pagination">
            <!-- Previous Page Link -->
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&laquo;</a>
                </li>
            @endif

            <!-- Pagination Elements -->
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $window = 1; // Jarak maksimum dari halaman saat ini
                $start = max(1, $currentPage - $window);
                $end = min($lastPage, $currentPage + $window);

                // Pastikan hanya menampilkan 3 nomor halaman
                if ($end - $start < 2) {
                    $start = max(1, $end - 2);
                } elseif ($end - $start > 2) {
                    $start = $currentPage - 1;
                    $end = $currentPage + 1;
                }

                // Pastikan batas bawah dan atas
                $start = max(1, $start);
                $end = min($lastPage, $end);
            @endphp

            @for ($i = $start; $i <= $end; $i++)
                @if ($i <= $lastPage)
                    @if ($i == $currentPage)
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{ $i }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endif
                @endif
            @endfor

            <!-- Next Page Link -->
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">&raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif