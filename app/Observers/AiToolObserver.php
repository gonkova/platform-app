<?php

namespace App\Observers;

use App\Models\AiTool;
use Illuminate\Support\Facades\Cache;

class AiToolObserver
{
    public function saved(AiTool $aiTool)
    {
        Cache::forget('categories_with_tools');
    }

    public function deleted(AiTool $aiTool)
    {
        Cache::forget('categories_with_tools');
    }
}
