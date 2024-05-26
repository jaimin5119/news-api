<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>News Articles</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">News Articles</h1>

        <form action="{{ route('news.index') }}" method="GET" class="mb-4">
            <input type="text" name="query" class="border p-2 rounded w-full" placeholder="Search by author name..."
                value="{{ request('query') }}">
            <button type="submit" class="bg-blue-500 text-white p-2 rounded mt-2">Search</button>
        </form>

        @if($articles->isEmpty())
        <p>No articles found.</p>
        @else
        <div class="mb-4">
            <label for="columnSelector" class="block mb-2 font-semibold">Select Columns to Show:</label>
            <div id="columnSelector" class="border p-2 rounded flex flex-wrap space-x-4">
                @foreach ($columns as $column)
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="column_{{ $column }}" class="column-checkbox" value="{{ $column }}"
                        checked>
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
                            <a
                                href="{{ route('news.index', array_merge(request()->query(), ['sort' => $column, 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}">
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
                    <tr>
                        <td class="border border-gray-300 px-4 py-2"></td>
                        @foreach ($columns as $column)
                        <td id="{{ $column }}Cell" class="border border-gray-300 px-4 py-2">
                            @if ($column === 'author')
                            <input type="text" name="author" class="border p-2 rounded w-full"
                                placeholder="Filter by author..." value="{{ request('author') }}">
                            @elseif ($column === 'publishedAt')
                            <input type="date" name="publishedAt" class="border p-2 rounded w-full"
                                value="{{ request('publishedAt') }}">
                            @elseif ($column === 'source')
                            <input type="text" name="source" class="border p-2 rounded w-full"
                                placeholder="Filter by source..." value="{{ request('source') }}">
                            @else
                            <input type="text" name="{{ $column }}" class="border p-2 rounded w-full"
                                placeholder="Filter by {{ $column }}..." value="{{ request($column) }}">
                            @endif
                        </td>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($articles as $index => $article)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $articles->firstItem() + $index }}</td>
                        @foreach ($columns as $column)
                        @if ($column === 'urlToImage')
                        <td id="{{ $column }}Cell" class="border border-gray-300 px-4 py-2">
                            @if(isset($article[$column]))
                            <img src="{{ $article[$column] }}" alt="Article Image" class="w-32 h-auto">
                            @else
                            N/A
                            @endif
                        </td>
                        @else
                        <td id="{{ $column }}Cell" class="border border-gray-300 px-4 py-2">{{ $article[$column] }}</td>
                        @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <nav class="flex justify-center" aria-label="Pagination">
                <ul class="flex">
                    {{-- Previous Page Link --}}
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
                        <span
                            class="relative block px-2 py-1 border border-gray-300 bg-white text-gray-500">Previous</span>
                    </li>
                    @else
                    <li class="mr-2">
                        <a href="{{ $articles->previousPageUrl() }}"
                            class="relative block px-2 py-1 border border-gray-300 bg-white text-blue-500 hover:text-white hover:bg-blue-500">Previous</a>
                    </li>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($articles->hasMorePages())
                    <li>
                        <a href="{{ $articles->nextPageUrl() }}"
                            class="relative block px-2 py-1 border border-gray-300 bg-white text-blue-500 hover:text-white hover:bg-blue-500">Next</a>
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
        const table = document.getElementById('articlesTable');

        columnCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const column = this.value;
                const header = document.getElementById(column + 'Header');
                const cells = document.querySelectorAll('#' + column + 'Cell');

                if (header && cells) {
                    const isChecked = this.checked;

                    header.style.display = isChecked ? 'table-cell' : 'none';
                    cells.forEach(function(cell) {
                        cell.style.display = isChecked ? 'table-cell' : 'none';
                    });
                }
            });
        });

        // Initialize column visibility based on checkboxes
        columnCheckboxes.forEach(function(checkbox) {
            const column = checkbox.value;
            const header = document.getElementById(column + 'Header');
            const cells = document.querySelectorAll('#' + column + 'Cell');

            if (header && cells) {
                const isChecked = checkbox.checked;

                header.style.display = isChecked ? 'table-cell' : 'none';
                cells.forEach(function(cell) {
                    cell.style.display = isChecked ? 'table-cell' : 'none';
                });
            }
        });
    });
    </script>
</body>

</html>