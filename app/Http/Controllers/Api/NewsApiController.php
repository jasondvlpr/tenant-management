<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    public function index()
    {
        $news = News::where('is_active', true)
                    ->latest()
                    ->get();
                    
        return response()->json([
            'status' => 'success',
            'data' => $news
        ]);
    }
}
