<?php

namespace App\Http\Controllers;

use Storage;
use Markdown;
use Illuminate\Http\Request;

class MarkdownController extends Controller
{
    public function __invoke($page) {
        $file = Storage::disk('dropbox')->get(str_finish($page, '.md'));
        $converted = Markdown::convertToHtml($file);

        return view('markdown', ['markdown' => $converted]);
    }
}
