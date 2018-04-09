<?php

namespace App\Http\Controllers;

use Storage;
use Markdown;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function __invoke($name) {
        $file = Storage::disk('dropbox')->get(str_finish($name, '.md'));
        $converted = Markdown::convertToHtml($file);

        return view('guide', ['content' => $converted]);
    }
}
