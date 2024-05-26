<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NewsService;
use Illuminate\Pagination\Paginator;

class NewsController extends Controller
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    
    public function index(Request $request)
{
    try {
        // Define the columns
        $columns = ['title', 'author', 'description', 'urlToImage', 'publishedAt', 'content', 'source'];
    
        // Get sorting parameters
        $sortField = $request->input('sort', 'publishedAt'); // Default sort field
        $sortOrder = $request->input('order', 'desc'); // Default sort order
        
        // Validate the sort field
        if (!in_array($sortField, $columns)) {
            $sortField = 'publishedAt'; // Fallback to default sort field if invalid
        }
    
        // Get the search query
        $query = $request->input('query', '');
    
        // Prepare filter parameters
        $filters = [
            'source' => $request->input('source', ''),
            'publishedAt' => $request->input('publishedAt', ''),
            'author' => $request->input('author', ''),
        ];
    
        // Get articles with sorting, pagination, and filters
        $response = $this->newsService->getArticles([
            'q' => $query,
            'page' => $request->input('page', 1),
            'pageSize' => 10,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'filters' => $filters,
        ]);
    
        // Check if the response contains articles
        $articles = isset($response['articles']) ? $response['articles'] : [];
    
        // Convert the array of articles to a paginator instance
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $articles, // Items
            $response['totalResults'] ?? 0, // Total items
            10, // Items per page
            $request->input('page', 1), // Current page
            ['path' => $request->url(), 'query' => $request->query()] // Additional options
        );
    
        return view('news.index', [
            'articles' => $paginator,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'query' => $query,
            'source' => $filters['source'],
            'publishedAt' => $filters['publishedAt'],
            'author' => $filters['author'],
            'columns' => $columns, // Pass the columns variable to the view
        ]);
    } catch (\Exception $e) {
        // Handle the error
        return back()->withError('Error: ' . $e->getMessage());
    }
}

}
