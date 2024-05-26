<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>News Articles</title>
    <style>
      .read-more-content {
    overflow: hidden;
    max-height: 100px;
    transition: max-height 0.2s ease-out;
}

        .read-more-btn {
            cursor: pointer;
            color: blue;
        }
        .expanded {
            max-height: none !important;
        }
    </style>

</head>
<body class="bg-gray-100 text-gray-900">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">News Articles</h1>

        <form action="{{ route('news.index') }}" method="GET" class="mb-4">
            <div class="mb-2">
            <label for="source" class="block font-semibold mb-1">Search:</label>

                <input type="text" name="query" class="border p-2 rounded w-full" placeholder="Search by keyword..." value="{{ request('query') }}">
            </div>
           
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Search</button>
        </form>

        @if($articles->isEmpty())
            <p>No articles found.</p>
        @else
            <div class="mb-4">
                <label for="columnSelector" class="block mb-2 font-semibold">Select Columns to Show:</label>
                <div id="columnSelector" class="border p-2 rounded flex flex-wrap space-x-4">
                    @foreach ($columns as $column)
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="column_{{ $column }}" class="column-checkbox" value="{{ $column }}" checked>
                            <label for="column_{{ $column }}" class="text-sm"><b>{{ ucfirst($column) }}</b></label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="articlesTable" class="table-auto w-full border-collapse border border-gray-300">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 px-4 py-2">Sr No</th>

                            @foreach ($columns as $column)
                                <th id="{{ $column }}Header" class="border border-gray-300 px-4 py-2">
                                    <a href="{{ route('news.index', array_merge(request()->query(), ['sort' => $column, 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}">
                                        {{ ucfirst($column) }}
                                        @if(request('sort') === $column)
                                            @if(request('order') === 'asc')
                                                ▲
                                            @else
                                                ▼
                                            @endif
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($articles as $index => $article)
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">{{ $articles->firstItem() + $index }}</td>
                                @foreach ($columns as $column)
                                    <td id="{{ $column }}Cell" class="border border-gray-300 px-4 py-2">
                                        @if ($column === 'urlToImage' && isset($article[$column]))
                                            <img src="{{ $article[$column] }}" alt="Article Image" class="w-32 h-auto">
                                        @elseif ($column === 'source' && isset($article['source']['name']))
                                            {{ $article['source']['name'] }}
                                            @elseif ($column === 'publishedAt' && isset($article[$column]))
                                            {{ \Carbon\Carbon::parse($article[$column])->format('F j, Y') }}
                                          
                                            @elseif (in_array($column, ['content', 'description']) && isset($article[$column]))
                                            <div class="read-more-content" id="{{ $column }}_{{ $index }}">
                                                {{ $article[$column] }}
                                            </div>
                                            <div class="read-more-btn" onclick="toggleReadMore('{{ $column }}_{{ $index }}')">Read More</div>

                                            @elseif(isset($article[$column]))
                                            {{ $article[$column] }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <nav class="flex justify-center" aria-label="Pagination">
                    <ul class="flex">
                        <li class="mr-2">
                            <span>Current Page: {{ $articles->currentPage() }}</span>
                        </li>
                        <li class="mr-2">
                            <span>Last Page: {{ $articles->lastPage() }}</span>
                        </li>
                        <li class="mr-2">
                            <span>Total Items: {{ $articles->total() }}</span>
                        </li>
                        @if ($articles->onFirstPage())
                            <li class="mr-2 cursor-not-allowed" aria-disabled="true">
                                <span class="relative block px-2 py-1 border border-gray-300 bg-white text-gray-500">Previous</span>
                            </li>
                        @else
                            <li class="mr-2">
                                <a href="{{ $articles->previousPageUrl() }}" class="relative block px-2 py-1 border border-gray-300 bg-white text-blue-500 hover:text-white hover:bg-blue-500">Previous</a>
                            </li>
                        @endif

                        @if ($articles->hasMorePages())
                            <li>
                                <a href="{{ $articles->nextPageUrl() }}" class="relative block px-2 py-1 border border-gray-300 bg-white text-blue-500 hover:text-white hover:bg-blue-500">Next</a>
                            </li>
                        @else
                            <li class="ml-2 cursor-not-allowed" aria-disabled="true">
                                <span class="relative block px-2 py-1 border border-gray-300 bg-white text-gray-500">Next</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    <script src="{{ mix('js/app.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const columnCheckboxes = document.querySelectorAll('.column-checkbox');

        columnCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                toggleColumnVisibility(this.value, this.checked);
            });
            toggleColumnVisibility(checkbox.value, checkbox.checked);
        });

        function toggleColumnVisibility(column, isVisible) {
            const header = document.getElementById(column + 'Header');
            const cells = document.querySelectorAll('#' + column + 'Cell');

            if (header) {
                header.style.display = isVisible ? 'table-cell' : 'none';
            }

            cells.forEach(function(cell) {
                cell.style.display = isVisible ? 'table-cell' : 'none';
            });
        }
       

    });
    </script>
       <script>
     function toggleReadMore(id) {
            const content = document.getElementById(id);
            const btn = content.nextElementSibling;
            content.classList.toggle('expanded');
            btn.textContent = content.classList.contains('expanded') ? 'Read Less' : 'Read More';
            content.style.maxHeight = content.classList.contains('expanded') ? null : '100px';

        }


    </script>



</body>

</html>
